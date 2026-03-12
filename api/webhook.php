<?php
/**
 * Mailer Campaigner - Stripe Webhook Handler
 * 
 * POST /api/webhook.php
 * Wird von Stripe nach erfolgreicher Zahlung aufgerufen
 * 
 * Events:
 *   - checkout.session.completed: Lizenz erstellen & E-Mail senden
 *   - payment_intent.succeeded: Zusätzliche Bestätigung (optional)
 * 
 * WICHTIG: Diesen Endpoint in Stripe Dashboard registrieren:
 * https://dashboard.stripe.com/webhooks
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/LicenseManager.php';
require_once __DIR__ . '/includes/EmailService.php';
require_once __DIR__ . '/vendor/autoload.php';

// ═══════════════════════════════════════════════════════════════════════════
// INITIALISIERUNG
// ═══════════════════════════════════════════════════════════════════════════

// CORS nicht nötig für Webhooks, aber Content-Type setzen
header('Content-Type: application/json');

logStripe(LOG_LEVEL_INFO, '═══ Webhook-Anfrage empfangen ═══', [
    'ip' => getClientIp(),
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'unknown'
]);

// ═══════════════════════════════════════════════════════════════════════════
// STRIPE INITIALISIEREN
// ═══════════════════════════════════════════════════════════════════════════

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Raw POST body für Signatur-Verifizierung
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

logStripe(LOG_LEVEL_DEBUG, 'Webhook-Daten empfangen', [
    'payload_length' => strlen($payload),
    'has_signature' => !empty($sigHeader)
]);

// ═══════════════════════════════════════════════════════════════════════════
// SIGNATUR VERIFIZIEREN
// ═══════════════════════════════════════════════════════════════════════════

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, 
        $sigHeader, 
        STRIPE_WEBHOOK_SECRET
    );
    
    logStripe(LOG_LEVEL_INFO, 'Webhook-Signatur verifiziert', [
        'event_type' => $event->type,
        'event_id' => $event->id
    ]);
    
} catch (\UnexpectedValueException $e) {
    logStripe(LOG_LEVEL_ERROR, 'Ungültiger Webhook-Payload', [
        'error' => $e->getMessage()
    ]);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
    
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    logStripe(LOG_LEVEL_ERROR, 'Ungültige Webhook-Signatur', [
        'error' => $e->getMessage()
    ]);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// EVENT VERARBEITEN
// ═══════════════════════════════════════════════════════════════════════════

switch ($event->type) {
    case 'checkout.session.completed':
        handleCheckoutCompleted($event->data->object);
        break;
        
    case 'payment_intent.succeeded':
        handlePaymentSucceeded($event->data->object);
        break;
        
    case 'payment_intent.payment_failed':
        handlePaymentFailed($event->data->object);
        break;
        
    default:
        logStripe(LOG_LEVEL_DEBUG, 'Unbehandelter Event-Typ', ['type' => $event->type]);
}

// Erfolgreiche Verarbeitung bestätigen
http_response_code(200);
echo json_encode(['received' => true, 'event_id' => $event->id]);

// ═══════════════════════════════════════════════════════════════════════════
// EVENT HANDLER
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Verarbeitet abgeschlossene Checkout-Sessions
 */
function handleCheckoutCompleted($session): void {
    $sessionId = $session->id;
    $paymentIntent = $session->payment_intent ?? '';
    $customerId = $session->customer ?? null;
    
    // E-Mail aus verschiedenen Quellen extrahieren
    $customerEmail = $session->customer_details->email 
        ?? $session->customer_email 
        ?? null;
    
    $maskedEmail = $customerEmail ? maskEmail($customerEmail) : 'unknown';
    
    logStripe(LOG_LEVEL_INFO, 'Checkout abgeschlossen', [
        'session_id' => $sessionId,
        'email' => $maskedEmail,
        'amount' => ($session->amount_total ?? 0) / 100 . ' EUR',
        'payment_status' => $session->payment_status
    ]);
    
    // Zahlung muss erfolgreich sein
    if ($session->payment_status !== 'paid') {
        logStripe(LOG_LEVEL_WARN, 'Checkout nicht bezahlt', [
            'session_id' => $sessionId,
            'status' => $session->payment_status
        ]);
        return;
    }
    
    // E-Mail erforderlich
    if (empty($customerEmail)) {
        logStripe(LOG_LEVEL_ERROR, 'Keine E-Mail in Checkout-Session', [
            'session_id' => $sessionId
        ]);
        return;
    }
    
    // License Manager initialisieren
    $licenseManager = new LicenseManager();
    
    // Duplikat-Schutz: Prüfen ob für diese Session bereits eine Lizenz existiert
    if ($licenseManager->licenseExistsForSession($sessionId)) {
        logStripe(LOG_LEVEL_INFO, 'Lizenz für Session existiert bereits (Duplikat-Webhook)', [
            'session_id' => $sessionId
        ]);
        return;
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // LIZENZ ERSTELLEN
    // ═══════════════════════════════════════════════════════════════════════
    
    try {
        $licenseData = $licenseManager->createLicense(
            $customerEmail,
            $sessionId,
            $paymentIntent,
            $customerId
        );
        
        logStripe(LOG_LEVEL_SUCCESS, 'Lizenz erstellt', [
            'email' => $maskedEmail,
            'key_prefix' => substr($licenseData['license_key'], 0, 9) . '...',
            'expires_at' => $licenseData['expires_at']
        ]);
        
    } catch (Exception $e) {
        logStripe(LOG_LEVEL_ERROR, 'Fehler beim Erstellen der Lizenz', [
            'error' => $e->getMessage(),
            'session_id' => $sessionId
        ]);
        // Nicht abbrechen - versuchen E-Mail zu senden falls möglich
        return;
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // LIZENZ-E-MAIL SENDEN
    // ═══════════════════════════════════════════════════════════════════════
    
    $emailService = new EmailService();
    $emailSent = $emailService->sendLicenseEmail($customerEmail, $licenseData);
    
    if ($emailSent) {
        logStripe(LOG_LEVEL_SUCCESS, '═══ Checkout vollständig verarbeitet ═══', [
            'session_id' => $sessionId,
            'email' => $maskedEmail
        ]);
    } else {
        logStripe(LOG_LEVEL_ERROR, 'Lizenz-E-Mail konnte nicht gesendet werden', [
            'session_id' => $sessionId,
            'email' => $maskedEmail,
            'license_key' => $licenseData['license_key'] // Für manuellen Versand
        ]);
        // TODO: Admin-Benachrichtigung oder Retry-Queue implementieren
    }
}

/**
 * Verarbeitet erfolgreiche Zahlungen (zusätzliche Bestätigung)
 */
function handlePaymentSucceeded($paymentIntent): void {
    logStripe(LOG_LEVEL_INFO, 'Payment Intent erfolgreich', [
        'payment_intent_id' => $paymentIntent->id,
        'amount' => ($paymentIntent->amount ?? 0) / 100 . ' EUR'
    ]);
    // Hauptverarbeitung erfolgt in handleCheckoutCompleted
}

/**
 * Verarbeitet fehlgeschlagene Zahlungen
 */
function handlePaymentFailed($paymentIntent): void {
    $lastError = $paymentIntent->last_payment_error;
    
    logStripe(LOG_LEVEL_WARN, 'Zahlung fehlgeschlagen', [
        'payment_intent_id' => $paymentIntent->id,
        'error_type' => $lastError->type ?? 'unknown',
        'error_code' => $lastError->code ?? 'unknown',
        'error_message' => $lastError->message ?? 'Keine Details'
    ]);
    
    // Optional: Benachrichtigung an Admin oder Retry-Logik
}
