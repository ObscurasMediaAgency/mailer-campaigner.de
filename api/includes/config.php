<?php
/**
 * Mailer Campaigner - API Konfiguration
 * 
 * WICHTIG: Diese Datei enthält sensible Daten!
 * - Niemals in Git committen
 * - In Produktion: chmod 600
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

// ═══════════════════════════════════════════════════════════════════════════
// UMGEBUNG
// ═══════════════════════════════════════════════════════════════════════════
define('APP_ENV', 'production');  // 'development' oder 'production'
define('APP_DEBUG', false);
define('APP_URL', 'https://mailer-campaigner.de');
define('API_URL', APP_URL . '/api');

// ═══════════════════════════════════════════════════════════════════════════
// SMTP KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('SMTP_HOST', 'mail.dein-server.de');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'noreply@mailer-campaigner.de');
define('SMTP_PASSWORD', 'DEIN_SMTP_PASSWORT');
define('SMTP_ENCRYPTION', 'ssl');  // 'ssl' oder 'tls'

define('EMAIL_FROM', 'noreply@mailer-campaigner.de');
define('EMAIL_FROM_NAME', 'Mailer Campaigner');

// ═══════════════════════════════════════════════════════════════════════════
// STRIPE KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('STRIPE_SECRET_KEY', 'sk_test_DEIN_SECRET_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_DEIN_PUBLISHABLE_KEY');
define('STRIPE_WEBHOOK_SECRET', 'whsec_DEIN_WEBHOOK_SECRET');
define('STRIPE_PRICE_ID', '');  // Optional: Feste Price-ID aus Stripe Dashboard

// ═══════════════════════════════════════════════════════════════════════════
// PRODUKT KONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════
define('PRODUCT_NAME', 'Mailer Campaigner Pro');
define('PRODUCT_PRICE_EUR', 129.00);
define('LICENSE_VALIDITY_DAYS', 365);

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
define('RATE_LIMIT_REQUESTS', 10);
define('RATE_LIMIT_WINDOW', 60);  // 60 Sekunden
define('RATE_LIMIT_STORAGE', __DIR__ . '/../data/rate_limits.json');

define('ALLOWED_ORIGINS', [
    'https://mailer-campaigner.de',
    'https://www.mailer-campaigner.de',
]);

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
