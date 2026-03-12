<?php
/**
 * Mailer Campaigner - License Verify Endpoint
 * 
 * POST /api/verify.php
 * Verifiziert einen Lizenzschlüssel
 * 
 * Request:  { "license_key": "XXXX-XXXX-XXXX-XXXX" }
 * Response: { "valid": true, "email": "...", "expires": "2027-03-12", "remaining_days": 365 }
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/LicenseManager.php';

// ═══════════════════════════════════════════════════════════════════════════
// INITIALISIERUNG
// ═══════════════════════════════════════════════════════════════════════════

setCorsHeaders();

logLicense(LOG_LEVEL_INFO, '═══ Lizenz-Verifizierung gestartet ═══', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'ip' => getClientIp()
]);

// Nur POST erlauben
requireMethod('POST');

// Rate Limiting (großzügiger für Verifizierung)
$clientIp = getClientIp();
if (!checkRateLimit("verify_{$clientIp}", 30, 60)) {
    logLicense(LOG_LEVEL_WARN, 'Rate Limit erreicht', ['ip' => $clientIp]);
    errorResponse('Zu viele Anfragen. Bitte versuchen Sie es später erneut.', 429);
}

// ═══════════════════════════════════════════════════════════════════════════
// REQUEST VERARBEITEN
// ═══════════════════════════════════════════════════════════════════════════

$input = getJsonInput();
$licenseKey = $input['license_key'] ?? '';

if (empty($licenseKey)) {
    logLicense(LOG_LEVEL_WARN, 'Kein Lizenzschlüssel angegeben');
    errorResponse('Lizenzschlüssel ist erforderlich.');
}

$licenseKey = strtoupper(trim($licenseKey));
$keyPrefix = substr($licenseKey, 0, 9) . '...';

logLicense(LOG_LEVEL_INFO, 'Verifiziere Lizenz', ['key_prefix' => $keyPrefix]);

// Format validieren
if (!isValidLicenseKey($licenseKey)) {
    logLicense(LOG_LEVEL_WARN, 'Ungültiges Lizenzformat', ['key_prefix' => $keyPrefix]);
    http_response_code(400);
    echo json_encode([
        'valid' => false,
        'error' => 'Ungültiges Lizenzformat. Erwartet: XXXX-XXXX-XXXX-XXXX',
        'code' => 'INVALID_FORMAT'
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// LIZENZ VERIFIZIEREN
// ═══════════════════════════════════════════════════════════════════════════

try {
    $licenseManager = new LicenseManager();
    $result = $licenseManager->verifyLicense($licenseKey);
    
    if ($result['valid']) {
        logLicense(LOG_LEVEL_SUCCESS, 'Lizenz gültig', [
            'key_prefix' => $keyPrefix,
            'remaining_days' => $result['remaining_days']
        ]);
        
        // Sensible Daten für Response entfernen
        $response = [
            'valid' => true,
            'product' => $result['product'],
            'expires' => $result['expires'],
            'remaining_days' => $result['remaining_days'],
            'activated' => $result['activated'],
        ];
        
        // E-Mail nur teilweise anzeigen
        if (isset($result['email'])) {
            $response['email_hint'] = maskEmail($result['email']);
        }
        
        echo json_encode($response);
        
    } else {
        logLicense(LOG_LEVEL_INFO, 'Lizenz ungültig', [
            'key_prefix' => $keyPrefix,
            'reason' => $result['code'] ?? 'unknown'
        ]);
        
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    logLicense(LOG_LEVEL_ERROR, 'Fehler bei Lizenz-Verifizierung', [
        'error' => $e->getMessage()
    ]);
    errorResponse('Ein interner Fehler ist aufgetreten.', 500);
}
