<?php
/**
 * Mailer Campaigner - Helper Funktionen
 * 
 * Enthält gemeinsam genutzte Utility-Funktionen für alle API-Endpoints:
 * - Logging
 * - CORS
 * - Rate Limiting
 * - Input-Validierung
 * - Response-Handler
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/config.php';

// ═══════════════════════════════════════════════════════════════════════════
// LOGGING SYSTEM
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Log-Level Konstanten
 */
define('LOG_LEVEL_DEBUG', 'DEBUG');
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_SUCCESS', 'SUCCESS');
define('LOG_LEVEL_WARN', 'WARN');
define('LOG_LEVEL_ERROR', 'ERROR');

/**
 * Schreibt einen Log-Eintrag in die angegebene Datei
 * 
 * @param string $logFile Pfad zur Log-Datei
 * @param string $level Log-Level (DEBUG, INFO, SUCCESS, WARN, ERROR)
 * @param string $message Nachricht
 * @param array $context Zusätzliche Kontextdaten
 */
function writeLog(string $logFile, string $level, string $message, array $context = []): void {
    // Log-Verzeichnis erstellen falls nicht vorhanden
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Log-Rotation bei Überschreitung der maximalen Größe
    if (file_exists($logFile) && filesize($logFile) > LOG_MAX_SIZE) {
        $archiveName = $logFile . '.' . date('Y-m-d_H-i-s') . '.bak';
        rename($logFile, $archiveName);
    }
    
    // Log-Eintrag formatieren
    $timestamp = date('Y-m-d H:i:s');
    $requestId = getRequestId();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    
    $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    $logEntry = "[{$timestamp}] [{$requestId}] [{$ip}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
    
    // In Datei schreiben (thread-safe)
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Bei Fehlern zusätzlich in error.log schreiben
    if ($level === LOG_LEVEL_ERROR && $logFile !== LOG_ERROR) {
        file_put_contents(LOG_ERROR, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Generiert eine eindeutige Request-ID für Log-Korrelation
 */
function getRequestId(): string {
    static $requestId = null;
    if ($requestId === null) {
        $requestId = substr(bin2hex(random_bytes(4)), 0, 8);
    }
    return $requestId;
}

/**
 * Shortcut-Funktionen für verschiedene Log-Typen
 */
function logNewsletter(string $level, string $message, array $context = []): void {
    writeLog(LOG_NEWSLETTER, $level, $message, $context);
}

function logStripe(string $level, string $message, array $context = []): void {
    writeLog(LOG_STRIPE, $level, $message, $context);
}

function logLicense(string $level, string $message, array $context = []): void {
    writeLog(LOG_LICENSE, $level, $message, $context);
}

function logError(string $message, array $context = []): void {
    writeLog(LOG_ERROR, LOG_LEVEL_ERROR, $message, $context);
}

// ═══════════════════════════════════════════════════════════════════════════
// CORS HANDLING
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Setzt CORS-Header basierend auf der Konfiguration
 */
function setCorsHeaders(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Erlaubte Origins prüfen
    $allowedOrigins = ALLOWED_ORIGINS;
    if (APP_ENV === 'development' && defined('ALLOWED_ORIGINS_DEV')) {
        $allowedOrigins = array_merge($allowedOrigins, ALLOWED_ORIGINS_DEV);
    }
    
    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: {$origin}");
    } elseif (empty($origin)) {
        // Für Server-zu-Server Requests (z.B. Stripe Webhooks)
        header("Access-Control-Allow-Origin: *");
    }
    
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    header('Access-Control-Max-Age: 86400');
    header('Content-Type: application/json; charset=UTF-8');
    
    // Preflight-Request behandeln
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RATE LIMITING
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Prüft ob das Rate Limit erreicht wurde
 * 
 * @param string $identifier Eindeutiger Identifier (z.B. IP oder API-Key)
 * @param int $maxRequests Maximale Anzahl Requests (default aus Config)
 * @param int $windowSeconds Zeitfenster in Sekunden (default aus Config)
 * @return bool True wenn noch erlaubt, False wenn Limit erreicht
 */
function checkRateLimit(string $identifier, int $maxRequests = null, int $windowSeconds = null): bool {
    $maxRequests = $maxRequests ?? RATE_LIMIT_REQUESTS;
    $windowSeconds = $windowSeconds ?? RATE_LIMIT_WINDOW;
    
    $storageDir = dirname(RATE_LIMIT_STORAGE);
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }
    
    $limits = [];
    if (file_exists(RATE_LIMIT_STORAGE)) {
        $limits = json_decode(file_get_contents(RATE_LIMIT_STORAGE), true) ?? [];
    }
    
    $now = time();
    $key = md5($identifier);
    
    // Alte Einträge bereinigen
    if (isset($limits[$key])) {
        $limits[$key] = array_filter($limits[$key], fn($timestamp) => $timestamp > ($now - $windowSeconds));
    } else {
        $limits[$key] = [];
    }
    
    // Prüfen ob Limit erreicht
    if (count($limits[$key]) >= $maxRequests) {
        return false;
    }
    
    // Neuen Request eintragen
    $limits[$key][] = $now;
    
    // Speichern
    file_put_contents(RATE_LIMIT_STORAGE, json_encode($limits), LOCK_EX);
    
    return true;
}

// ═══════════════════════════════════════════════════════════════════════════
// INPUT VALIDIERUNG
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Validiert E-Mail-Adressen
 */
function isValidEmail(string $email): bool {
    if (empty($email)) {
        return false;
    }
    
    // Basis-Validierung
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Zusätzliche Prüfungen
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    
    // Domain muss mindestens einen Punkt haben
    if (strpos($parts[1], '.') === false) {
        return false;
    }
    
    // Keine temporären E-Mail-Dienste (optional)
    $blockedDomains = ['tempmail.com', '10minutemail.com', 'guerrillamail.com'];
    if (in_array(strtolower($parts[1]), $blockedDomains, true)) {
        return false;
    }
    
    return true;
}

/**
 * Bereinigt User-Input
 */
function sanitizeInput(string $input): string {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Validiert Lizenzschlüssel-Format (XXXX-XXXX-XXXX-XXXX)
 */
function isValidLicenseKey(string $key): bool {
    return (bool) preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', strtoupper($key));
}

// ═══════════════════════════════════════════════════════════════════════════
// TOKEN GENERIERUNG
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Generiert einen sicheren zufälligen Token
 */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Generiert einen Lizenzschlüssel im Format XXXX-XXXX-XXXX-XXXX
 */
function generateLicenseKey(): string {
    $segments = [];
    for ($i = 0; $i < 4; $i++) {
        $segments[] = strtoupper(bin2hex(random_bytes(2)));
    }
    return implode('-', $segments);
}

// ═══════════════════════════════════════════════════════════════════════════
// RESPONSE HANDLER
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Sendet eine erfolgreiche JSON-Response
 */
function successResponse(string $message, array $data = [], int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c'),
        'requestId' => getRequestId(),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Sendet eine Fehler-Response
 */
function errorResponse(string $message, int $httpCode = 400, array $details = []): void {
    http_response_code($httpCode);
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('c'),
        'requestId' => getRequestId(),
    ];
    if (!empty($details) && APP_DEBUG) {
        $response['details'] = $details;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// REQUEST HELPERS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Liest und dekodiert JSON-Input
 */
function getJsonInput(): array {
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        return [];
    }
    
    $decoded = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        errorResponse('Ungültiges JSON-Format: ' . json_last_error_msg(), 400);
    }
    
    return $decoded ?? [];
}

/**
 * Prüft ob die HTTP-Methode erlaubt ist
 */
function requireMethod(string|array $methods): void {
    if (is_string($methods)) {
        $methods = [$methods];
    }
    
    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        errorResponse('Methode nicht erlaubt. Erlaubt: ' . implode(', ', $methods), 405);
    }
}

/**
 * Holt einen erforderlichen Parameter oder gibt Fehler zurück
 */
function requireParam(array $input, string $key, string $errorMessage = null): mixed {
    if (!isset($input[$key]) || (is_string($input[$key]) && trim($input[$key]) === '')) {
        $errorMessage = $errorMessage ?? "Parameter '{$key}' ist erforderlich.";
        errorResponse($errorMessage, 400);
    }
    return $input[$key];
}

// ═══════════════════════════════════════════════════════════════════════════
// UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Maskiert E-Mail für Logs (max@example.com -> m**@e******.com)
 */
function maskEmail(string $email): string {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return '***';
    }
    
    $local = $parts[0];
    $domain = $parts[1];
    
    $maskedLocal = strlen($local) > 1 
        ? $local[0] . str_repeat('*', strlen($local) - 1)
        : '*';
    
    $domainParts = explode('.', $domain);
    $maskedDomain = strlen($domainParts[0]) > 1
        ? $domainParts[0][0] . str_repeat('*', strlen($domainParts[0]) - 1)
        : '*';
    
    array_shift($domainParts);
    $maskedDomain .= '.' . implode('.', $domainParts);
    
    return $maskedLocal . '@' . $maskedDomain;
}

/**
 * Formatiert Bytes in lesbare Größe
 */
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Gibt Client-IP zurück (berücksichtigt Proxies)
 */
function getClientIp(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return 'unknown';
}
