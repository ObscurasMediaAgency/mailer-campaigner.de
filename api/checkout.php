<?php
/**
 * Mailer Campaigner - Stripe Checkout Endpoint
 * 
 * POST /api/checkout.php
 * Erstellt eine Stripe Checkout Session
 * 
 * Request:  { "email": "kunde@example.com" }
 * Response: { "success": true, "checkout_url": "https://checkout.stripe.com/..." }
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/vendor/autoload.php';

// ═══════════════════════════════════════════════════════════════════════════
// INITIALISIERUNG
// ═══════════════════════════════════════════════════════════════════════════

setCorsHeaders();

logStripe(LOG_LEVEL_INFO, '═══ Checkout-Anfrage gestartet ═══', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'ip' => getClientIp()
]);

// Nur POST erlauben
requireMethod('POST');

// Rate Limiting
$clientIp = getClientIp();
if (!checkRateLimit("checkout_{$clientIp}", 5, 60)) {
    logStripe(LOG_LEVEL_WARN, 'Rate Limit erreicht', ['ip' => $clientIp]);
    errorResponse('Zu viele Anfragen. Bitte versuchen Sie es später erneut.', 429);
}

// ═══════════════════════════════════════════════════════════════════════════
// STRIPE INITIALISIEREN
// ═══════════════════════════════════════════════════════════════════════════

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// ═══════════════════════════════════════════════════════════════════════════
// REQUEST VERARBEITEN
// ═══════════════════════════════════════════════════════════════════════════

$input = getJsonInput();
$email = isset($input['email']) ? strtolower(trim($input['email'])) : null;

if ($email && !isValidEmail($email)) {
    logStripe(LOG_LEVEL_WARN, 'Ungültige E-Mail angegeben', ['email' => maskEmail($email)]);
    errorResponse('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
}

$maskedEmail = $email ? maskEmail($email) : 'nicht angegeben';
logStripe(LOG_LEVEL_INFO, 'Erstelle Checkout-Session', ['email' => $maskedEmail]);

try {
    // ═══════════════════════════════════════════════════════════════════════
    // CHECKOUT SESSION PARAMETER
    // ═══════════════════════════════════════════════════════════════════════
    
    $params = [
        'payment_method_types' => ['card'],
        'mode' => 'payment',
        'success_url' => URL_PAYMENT_SUCCESS . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => URL_PAYMENT_CANCEL,
        'locale' => 'de',
        'metadata' => [
            'product' => 'mailer_campaigner_pro',
            'license_days' => (string) LICENSE_VALIDITY_DAYS,
            'source' => 'website',
        ],
        // Steuerberechnung und Rechnungsadresse
        'billing_address_collection' => 'required',
        'tax_id_collection' => ['enabled' => true],
    ];
    
    // E-Mail vorausfüllen falls angegeben
    if ($email) {
        $params['customer_email'] = $email;
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // PRODUKT KONFIGURATION
    // ═══════════════════════════════════════════════════════════════════════
    
    // Feste Price ID verwenden falls konfiguriert
    if (!empty(STRIPE_PRICE_ID)) {
        $params['line_items'] = [[
            'price' => STRIPE_PRICE_ID,
            'quantity' => 1,
        ]];
        logStripe(LOG_LEVEL_DEBUG, 'Verwende Price ID', ['price_id' => STRIPE_PRICE_ID]);
    } else {
        // Fallback: Preis dynamisch erstellen
        $params['line_items'] = [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => PRODUCT_NAME,
                    'description' => "Jahreslizenz - " . LICENSE_VALIDITY_DAYS . " Tage gültig",
                    'images' => ['https://mailer-campaigner.de/assets/product-image.png'],
                ],
                'unit_amount' => (int) (PRODUCT_PRICE_EUR * 100), // Cent-Betrag
            ],
            'quantity' => 1,
        ]];
        logStripe(LOG_LEVEL_DEBUG, 'Verwende dynamischen Preis', [
            'price_eur' => PRODUCT_PRICE_EUR
        ]);
    }
    
    // ═══════════════════════════════════════════════════════════════════════
    // SESSION ERSTELLEN
    // ═══════════════════════════════════════════════════════════════════════
    
    $session = \Stripe\Checkout\Session::create($params);
    
    logStripe(LOG_LEVEL_SUCCESS, 'Checkout-Session erstellt', [
        'session_id' => $session->id,
        'email' => $maskedEmail,
        'amount' => $session->amount_total / 100 . ' EUR'
    ]);
    
    successResponse('Checkout-Session erstellt', [
        'checkout_url' => $session->url,
        'session_id' => $session->id,
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    logStripe(LOG_LEVEL_ERROR, 'Stripe API-Fehler', [
        'error' => $e->getMessage(),
        'type' => get_class($e),
        'code' => $e->getStripeCode() ?? 'unknown'
    ]);
    errorResponse('Zahlungsdienst vorübergehend nicht verfügbar. Bitte versuchen Sie es später erneut.', 503);
    
} catch (Exception $e) {
    logStripe(LOG_LEVEL_ERROR, 'Unerwarteter Fehler', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    errorResponse('Ein interner Fehler ist aufgetreten.', 500);
}
