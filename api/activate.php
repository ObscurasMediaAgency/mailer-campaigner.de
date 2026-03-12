<?php
/**
 * Mailer Campaigner - License Activate Endpoint
 * 
 * POST /api/activate.php
 * Aktiviert eine Lizenz auf einem Gerät
 * 
 * Request:  { "license_key": "XXXX-XXXX-XXXX-XXXX", "machine_id": "...", "machine_name": "PC-Name" }
 * Response: { "success": true, "message": "Lizenz aktiviert", "expires": "2027-03-12" }
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

logLicense(LOG_LEVEL_INFO, '═══ Lizenz-Aktivierung gestartet ═══', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'ip' => getClientIp()
]);

// Nur POST erlauben
requireMethod('POST');

// Rate Limiting
$clientIp = getClientIp();
if (!checkRateLimit("activate_{$clientIp}", 10, 60)) {
    logLicense(LOG_LEVEL_WARN, 'Rate Limit erreicht', ['ip' => $clientIp]);
    errorResponse('Zu viele Anfragen. Bitte versuchen Sie es später erneut.', 429);
}

// ═══════════════════════════════════════════════════════════════════════════
// REQUEST VERARBEITEN
// ═══════════════════════════════════════════════════════════════════════════

$input = getJsonInput();

$licenseKey = $input['license_key'] ?? '';
$machineId = $input['machine_id'] ?? '';
$machineName = sanitizeInput($input['machine_name'] ?? '');

// Validierung
if (empty($licenseKey)) {
    logLicense(LOG_LEVEL_WARN, 'Kein Lizenzschlüssel angegeben');
    errorResponse('Lizenzschlüssel ist erforderlich.');
}

if (empty($machineId)) {
    logLicense(LOG_LEVEL_WARN, 'Keine Machine-ID angegeben');
    errorResponse('Machine-ID ist erforderlich.');
}

$licenseKey = strtoupper(trim($licenseKey));
$keyPrefix = substr($licenseKey, 0, 9) . '...';
$machineIdPrefix = substr($machineId, 0, 16) . '...';

logLicense(LOG_LEVEL_INFO, 'Aktiviere Lizenz', [
    'key_prefix' => $keyPrefix,
    'machine_id_prefix' => $machineIdPrefix,
    'machine_name' => $machineName ?: 'nicht angegeben'
]);

// Format validieren
if (!isValidLicenseKey($licenseKey)) {
    logLicense(LOG_LEVEL_WARN, 'Ungültiges Lizenzformat', ['key_prefix' => $keyPrefix]);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ungültiges Lizenzformat',
        'code' => 'INVALID_FORMAT'
    ]);
    exit;
}

// Machine-ID Länge prüfen (sollte ein Hash sein)
if (strlen($machineId) < 16) {
    logLicense(LOG_LEVEL_WARN, 'Machine-ID zu kurz', ['length' => strlen($machineId)]);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Ungültige Machine-ID',
        'code' => 'INVALID_MACHINE_ID'
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// LIZENZ AKTIVIEREN
// ═══════════════════════════════════════════════════════════════════════════

try {
    $licenseManager = new LicenseManager();
    $result = $licenseManager->activateLicense($licenseKey, $machineId, $machineName);
    
    if ($result['success'] ?? false) {
        logLicense(LOG_LEVEL_SUCCESS, 'Lizenz erfolgreich aktiviert', [
            'key_prefix' => $keyPrefix,
            'machine_name' => $machineName
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'expires' => $result['expires'],
            'remaining_days' => $result['remaining_days'],
        ]);
        
    } else {
        logLicense(LOG_LEVEL_WARN, 'Aktivierung fehlgeschlagen', [
            'key_prefix' => $keyPrefix,
            'reason' => $result['code'] ?? 'unknown'
        ]);
        
        http_response_code(400);
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    logLicense(LOG_LEVEL_ERROR, 'Fehler bei Lizenz-Aktivierung', [
        'error' => $e->getMessage()
    ]);
    errorResponse('Ein interner Fehler ist aufgetreten.', 500);
}
