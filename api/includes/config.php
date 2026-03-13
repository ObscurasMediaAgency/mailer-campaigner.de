<?php
/**
 * Mailer Campaigner - API Konfiguration
 * 
 * Lädt Umgebungsvariablen aus .env und definiert Konstanten.
 * Sensible Daten werden NICHT im Code gespeichert!
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

// ═══════════════════════════════════════════════════════════════════════════
// COMPOSER AUTOLOADER
// ═══════════════════════════════════════════════════════════════════════════
$vendorPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($vendorPath)) {
    http_response_code(500);
    header('Content-Type: application/json');
    $errorMsg = 'Composer dependencies not installed. Run: cd api && composer install';
    error_log('[CONFIG] FATAL: ' . $errorMsg);
    echo json_encode([
        'success' => false,
        'error' => $errorMsg,
        'hint' => 'SSH zum Server verbinden und im api/ Ordner "composer install" ausführen'
    ]);
    exit(1);
}

require_once $vendorPath;

// ═══════════════════════════════════════════════════════════════════════════
// DOTENV LADEN
// ═══════════════════════════════════════════════════════════════════════════
use Symfony\Component\Dotenv\Dotenv;

// .env Datei laden
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $dotenv = new Dotenv();
    $dotenv->load($envPath);
} else {
    // Fallback: Prüfen ob Umgebungsvariablen bereits gesetzt sind (z.B. via Server)
    if (empty($_ENV['APP_URL']) && empty(getenv('APP_URL'))) {
        error_log('[CONFIG] WARNUNG: .env Datei nicht gefunden: ' . $envPath);
    }
}

/**
 * Hilfsfunktion zum Laden von Umgebungsvariablen mit Fallback
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false || $value === '') {
        return $default;
    }
    
    // Boolean-Werte umwandeln
    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => $value,
    };
}

// ═══════════════════════════════════════════════════════════════════════════
// UMGEBUNG
// ═══════════════════════════════════════════════════════════════════════════
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', false));
define('APP_URL', env('APP_URL', 'https://mailer-campaigner.de'));
define('API_URL', APP_URL . '/api');

// ═══════════════════════════════════════════════════════════════════════════
// SMTP KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('SMTP_HOST', env('SMTP_HOST', 'localhost'));
define('SMTP_PORT', (int) env('SMTP_PORT', 465));
define('SMTP_USERNAME', env('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env('SMTP_PASSWORD', ''));
define('SMTP_ENCRYPTION', env('SMTP_ENCRYPTION', 'ssl'));

define('EMAIL_FROM', env('EMAIL_FROM', 'noreply@mailer-campaigner.de'));
define('EMAIL_FROM_NAME', env('EMAIL_FROM_NAME', 'Mailer Campaigner'));

// ═══════════════════════════════════════════════════════════════════════════
// STRIPE KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));
define('STRIPE_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', ''));
define('STRIPE_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET', ''));
define('STRIPE_PRICE_ID', env('STRIPE_PRICE_ID', ''));

// ═══════════════════════════════════════════════════════════════════════════
// PRODUKT KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('PRODUCT_NAME', env('PRODUCT_NAME', 'Mailer Campaigner Pro'));
define('PRODUCT_PRICE_EUR', (float) env('PRODUCT_PRICE_EUR', 129.00));
define('LICENSE_VALIDITY_DAYS', (int) env('LICENSE_VALIDITY_DAYS', 365));

// ═══════════════════════════════════════════════════════════════════════════
// NEWSLETTER KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('NEWSLETTER_STORAGE', __DIR__ . '/../data/newsletter_subscribers.json');
define('NEWSLETTER_TOKEN_EXPIRY', 48 * 60 * 60);  // 48 Stunden

// ═══════════════════════════════════════════════════════════════════════════
// DATENBANK (SQLite für Lizenzen)
// ═══════════════════════════════════════════════════════════════════════════
define('DATABASE_PATH', __DIR__ . '/../data/licenses.db');

// ═══════════════════════════════════════════════════════════════════════════
// LOGGING
// ═══════════════════════════════════════════════════════════════════════════
define('LOG_DIR', __DIR__ . '/../logs');
define('LOG_NEWSLETTER', LOG_DIR . '/newsletter.log');
define('LOG_STRIPE', LOG_DIR . '/stripe.log');
define('LOG_LICENSE', LOG_DIR . '/license.log');
define('LOG_ERROR', LOG_DIR . '/error.log');
define('LOG_MAX_SIZE', 10 * 1024 * 1024);  // 10 MB

// ═══════════════════════════════════════════════════════════════════════════
// SICHERHEIT
// ═══════════════════════════════════════════════════════════════════════════
define('RATE_LIMIT_REQUESTS', (int) env('RATE_LIMIT_REQUESTS', 10));
define('RATE_LIMIT_WINDOW', (int) env('RATE_LIMIT_WINDOW', 60));
define('RATE_LIMIT_STORAGE', __DIR__ . '/../data/rate_limits.json');

// Allowed Origins aus Komma-separiertem String parsen
$originsString = env('ALLOWED_ORIGINS', 'https://mailer-campaigner.de,https://www.mailer-campaigner.de');
define('ALLOWED_ORIGINS', array_map('trim', explode(',', $originsString)));

// In Development auch localhost erlauben
if (APP_ENV === 'development') {
    define('ALLOWED_ORIGINS_DEV', [
        'http://localhost:4200',
        'http://127.0.0.1:4200',
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════
// URLS
// ═══════════════════════════════════════════════════════════════════════════
define('URL_PAYMENT_SUCCESS', APP_URL . '/payment/success');
define('URL_PAYMENT_CANCEL', APP_URL . '/payment/cancel');
