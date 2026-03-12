<?php
/**
 * Mailer Campaigner - Newsletter API mit Double-Opt-In
 * 
 * Endpoints:
 *   POST /api/newsletter.php?action=subscribe    - Anmeldung (sendet Bestätigungs-E-Mail)
 *   GET  /api/newsletter.php?action=confirm      - Bestätigung per Token
 *   POST /api/newsletter.php?action=unsubscribe  - Abmeldung
 *   GET  /api/newsletter.php?action=unsubscribe  - Abmeldung per Link
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/PHPMailer/vendor/autoload.php';
require_once __DIR__ . '/templates/newsletter-double-optin.php';
require_once __DIR__ . '/templates/newsletter-welcome.php';
require_once __DIR__ . '/templates/newsletter-unsubscribe.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ═══════════════════════════════════════════════════════════════════════════
// INITIALISIERUNG
// ═══════════════════════════════════════════════════════════════════════════

setCorsHeaders();

$action = $_GET['action'] ?? '';

logNewsletter(LOG_LEVEL_INFO, '═══ Newsletter-Anfrage gestartet ═══', [
    'action' => $action,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'ip' => getClientIp(),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);

// ═══════════════════════════════════════════════════════════════════════════
// ROUTING
// ═══════════════════════════════════════════════════════════════════════════

switch ($action) {
    case 'subscribe':
        handleSubscribe();
        break;
    case 'confirm':
        handleConfirm();
        break;
    case 'unsubscribe':
        handleUnsubscribe();
        break;
    default:
        logNewsletter(LOG_LEVEL_ERROR, 'Ungültige Aktion', ['action' => $action]);
        errorResponse('Ungültige Aktion.', 400);
}

// ═══════════════════════════════════════════════════════════════════════════
// SUBSCRIBER STORAGE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Lädt alle Subscriber aus der JSON-Datei
 */
function loadSubscribers(): array {
    if (!file_exists(NEWSLETTER_STORAGE)) {
        logNewsletter(LOG_LEVEL_DEBUG, 'Subscriber-Datei existiert nicht, erstelle leeres Array');
        return [];
    }
    
    $content = file_get_contents(NEWSLETTER_STORAGE);
    $subscribers = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logNewsletter(LOG_LEVEL_ERROR, 'JSON-Parse-Fehler beim Laden der Subscriber', [
            'error' => json_last_error_msg()
        ]);
        return [];
    }
    
    return $subscribers ?? [];
}

/**
 * Speichert alle Subscriber in der JSON-Datei
 */
function saveSubscribers(array $subscribers): bool {
    $dir = dirname(NEWSLETTER_STORAGE);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            logNewsletter(LOG_LEVEL_ERROR, 'Konnte Verzeichnis nicht erstellen', ['dir' => $dir]);
            return false;
        }
    }
    
    $json = json_encode($subscribers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $result = file_put_contents(NEWSLETTER_STORAGE, $json, LOCK_EX);
    
    if ($result === false) {
        logNewsletter(LOG_LEVEL_ERROR, 'Konnte Subscriber nicht speichern');
        return false;
    }
    
    logNewsletter(LOG_LEVEL_DEBUG, 'Subscriber gespeichert', ['count' => count($subscribers)]);
    return true;
}

// ═══════════════════════════════════════════════════════════════════════════
// E-MAIL VERSAND
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Sendet eine E-Mail via PHPMailer
 */
function sendMail(string $to, string $subject, string $htmlBody, string $textBody): bool {
    $maskedEmail = maskEmail($to);
    logNewsletter(LOG_LEVEL_INFO, 'Sende E-Mail...', [
        'to' => $maskedEmail,
        'subject' => $subject
    ]);
    
    $startTime = microtime(true);
    
    try {
        $mail = new PHPMailer(true);
        
        // Server-Einstellungen
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        
        // SSL-Optionen (für shared Hosting mit self-signed Zertifikaten)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Debug-Mode für Entwicklung
        if (APP_DEBUG && APP_ENV === 'development') {
            $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
            $mail->Debugoutput = function($str, $level) {
                logNewsletter(LOG_LEVEL_DEBUG, "SMTP: $str", ['level' => $level]);
            };
        }
        
        // Absender und Empfänger
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Inhalt
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        
        // List-Unsubscribe Header für bessere Deliverability
        $mail->addCustomHeader('List-Unsubscribe', '<' . API_URL . '/newsletter.php?action=unsubscribe>');
        $mail->addCustomHeader('X-Mailer', 'MailerCampaigner/1.0');
        
        $mail->send();
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        logNewsletter(LOG_LEVEL_SUCCESS, 'E-Mail erfolgreich gesendet', [
            'to' => $maskedEmail,
            'duration_ms' => $duration
        ]);
        
        return true;
        
    } catch (Exception $e) {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        logNewsletter(LOG_LEVEL_ERROR, 'E-Mail-Versand fehlgeschlagen', [
            'to' => $maskedEmail,
            'error' => $e->getMessage(),
            'mailer_error' => isset($mail) ? $mail->ErrorInfo : 'N/A',
            'duration_ms' => $duration
        ]);
        return false;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// ACTION HANDLERS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Newsletter-Anmeldung (Double-Opt-In Schritt 1)
 */
function handleSubscribe(): void {
    logNewsletter(LOG_LEVEL_INFO, 'handleSubscribe gestartet');
    
    // Nur POST erlauben
    requireMethod('POST');
    
    // Rate Limiting
    $clientIp = getClientIp();
    if (!checkRateLimit("newsletter_subscribe_{$clientIp}", 5, 300)) {
        logNewsletter(LOG_LEVEL_WARN, 'Rate Limit erreicht', ['ip' => $clientIp]);
        errorResponse('Zu viele Anfragen. Bitte versuchen Sie es in 5 Minuten erneut.', 429);
    }
    
    // Input lesen
    $input = getJsonInput();
    logNewsletter(LOG_LEVEL_DEBUG, 'Input empfangen', [
        'has_email' => isset($input['email']),
        'has_source' => isset($input['source'])
    ]);
    
    $email = strtolower(trim($input['email'] ?? ''));
    $source = sanitizeInput($input['source'] ?? 'website');
    
    // E-Mail validieren
    if (empty($email)) {
        logNewsletter(LOG_LEVEL_WARN, 'Keine E-Mail angegeben');
        errorResponse('E-Mail-Adresse ist erforderlich.');
    }
    
    if (!isValidEmail($email)) {
        logNewsletter(LOG_LEVEL_WARN, 'Ungültige E-Mail', ['email' => maskEmail($email)]);
        errorResponse('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
    }
    
    $maskedEmail = maskEmail($email);
    logNewsletter(LOG_LEVEL_INFO, 'Newsletter-Anmeldung', [
        'email' => $maskedEmail,
        'source' => $source
    ]);
    
    // Subscriber laden
    $subscribers = loadSubscribers();
    
    // Prüfen ob bereits bestätigt
    if (isset($subscribers[$email]) && $subscribers[$email]['confirmed']) {
        logNewsletter(LOG_LEVEL_INFO, 'E-Mail bereits registriert', ['email' => $maskedEmail]);
        successResponse('Diese E-Mail-Adresse ist bereits für den Newsletter registriert.', [
            'alreadySubscribed' => true
        ]);
    }
    
    // Token generieren
    $token = generateToken();
    $tokenHash = hash('sha256', $token);
    
    // Subscriber speichern (pending)
    $subscribers[$email] = [
        'email' => $email,
        'source' => $source,
        'confirmed' => false,
        'token_hash' => $tokenHash,
        'token_expires' => time() + NEWSLETTER_TOKEN_EXPIRY,
        'created_at' => date('c'),
        'confirmed_at' => null,
        'ip' => $clientIp,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
    
    if (!saveSubscribers($subscribers)) {
        logNewsletter(LOG_LEVEL_ERROR, 'Speichern fehlgeschlagen');
        errorResponse('Ein interner Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 500);
    }
    
    logNewsletter(LOG_LEVEL_INFO, 'Subscriber gespeichert (pending)', ['email' => $maskedEmail]);
    
    // Bestätigungs-URL erstellen
    $confirmUrl = API_URL . "/newsletter.php?action=confirm&email=" . urlencode($email) . "&token=" . $token;
    logNewsletter(LOG_LEVEL_DEBUG, 'Bestätigungs-URL erstellt');
    
    // Double-Opt-In E-Mail senden
    $htmlBody = getNewsletterDoubleOptInEmail($email, $confirmUrl);
    $textBody = <<<TEXT
Bitte bestätigen Sie Ihre Newsletter-Anmeldung bei Mailer Campaigner.

Klicken Sie auf folgenden Link um Ihre E-Mail-Adresse zu bestätigen:
{$confirmUrl}

Der Link ist 48 Stunden gültig.

Falls Sie sich nicht angemeldet haben, können Sie diese E-Mail ignorieren.

--
Mailer Campaigner
https://mailer-campaigner.de
TEXT;
    
    if (!sendMail($email, "Bestätigen Sie Ihre Newsletter-Anmeldung", $htmlBody, $textBody)) {
        logNewsletter(LOG_LEVEL_ERROR, 'Bestätigungs-E-Mail konnte nicht gesendet werden');
        errorResponse('Die Bestätigungs-E-Mail konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.', 500);
    }
    
    logNewsletter(LOG_LEVEL_SUCCESS, '═══ Newsletter-Anmeldung erfolgreich ═══', ['email' => $maskedEmail]);
    successResponse('Bitte bestätigen Sie Ihre Anmeldung über den Link in der E-Mail, die wir Ihnen gesendet haben.', [
        'pendingConfirmation' => true
    ]);
}

/**
 * Newsletter-Bestätigung (Double-Opt-In Schritt 2)
 */
function handleConfirm(): void {
    $email = strtolower(trim($_GET['email'] ?? ''));
    $token = $_GET['token'] ?? '';
    $maskedEmail = !empty($email) ? maskEmail($email) : 'unknown';
    
    logNewsletter(LOG_LEVEL_INFO, 'handleConfirm gestartet', ['email' => $maskedEmail]);
    
    if (empty($email) || empty($token)) {
        logNewsletter(LOG_LEVEL_WARN, 'Ungültige Parameter', ['email' => $maskedEmail]);
        showResultPage(false, 'Ungültiger Bestätigungslink.');
        return;
    }
    
    $subscribers = loadSubscribers();
    
    if (!isset($subscribers[$email])) {
        logNewsletter(LOG_LEVEL_WARN, 'E-Mail nicht gefunden', ['email' => $maskedEmail]);
        showResultPage(false, 'Diese E-Mail-Adresse wurde nicht gefunden.');
        return;
    }
    
    $subscriber = $subscribers[$email];
    
    // Bereits bestätigt?
    if ($subscriber['confirmed']) {
        logNewsletter(LOG_LEVEL_INFO, 'Bereits bestätigt', ['email' => $maskedEmail]);
        showResultPage(true, 'Ihre E-Mail-Adresse wurde bereits bestätigt!');
        return;
    }
    
    // Token abgelaufen?
    if (time() > $subscriber['token_expires']) {
        logNewsletter(LOG_LEVEL_WARN, 'Token abgelaufen', [
            'email' => $maskedEmail,
            'expired_at' => date('c', $subscriber['token_expires'])
        ]);
        unset($subscribers[$email]);
        saveSubscribers($subscribers);
        showResultPage(false, 'Der Bestätigungslink ist abgelaufen. Bitte melden Sie sich erneut an.');
        return;
    }
    
    // Token prüfen
    $tokenHash = hash('sha256', $token);
    if (!hash_equals($subscriber['token_hash'], $tokenHash)) {
        logNewsletter(LOG_LEVEL_WARN, 'Ungültiger Token', ['email' => $maskedEmail]);
        showResultPage(false, 'Ungültiger Bestätigungslink.');
        return;
    }
    
    // Bestätigung speichern
    $subscribers[$email]['confirmed'] = true;
    $subscribers[$email]['confirmed_at'] = date('c');
    $subscribers[$email]['token_hash'] = null;
    $subscribers[$email]['token_expires'] = null;
    $subscribers[$email]['confirm_ip'] = getClientIp();
    
    // Unsubscribe-Token generieren
    $unsubscribeToken = generateToken();
    $subscribers[$email]['unsubscribe_token'] = hash('sha256', $unsubscribeToken);
    
    if (!saveSubscribers($subscribers)) {
        logNewsletter(LOG_LEVEL_ERROR, 'Speichern der Bestätigung fehlgeschlagen');
        showResultPage(false, 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
        return;
    }
    
    logNewsletter(LOG_LEVEL_SUCCESS, 'Newsletter-Anmeldung bestätigt', ['email' => $maskedEmail]);
    
    // Unsubscribe-URL für Willkommens-E-Mail
    $unsubscribeUrl = API_URL . "/newsletter.php?action=unsubscribe&email=" . urlencode($email) . "&token=" . $unsubscribeToken;
    
    // Willkommens-E-Mail senden
    $htmlBody = getNewsletterWelcomeEmail($email, $unsubscribeUrl);
    $textBody = <<<TEXT
Willkommen beim Mailer Campaigner Newsletter!

Ihre Anmeldung wurde erfolgreich bestätigt. Sie erhalten ab sofort unsere Updates zu:
- Neuen Funktionen und Updates
- Tipps & Tricks für erfolgreiches E-Mail-Marketing
- Exklusive Angebote für Newsletter-Abonnenten

Abmelden: {$unsubscribeUrl}

--
Mailer Campaigner
https://mailer-campaigner.de
TEXT;
    
    sendMail($email, "Willkommen beim Mailer Campaigner Newsletter!", $htmlBody, $textBody);
    
    logNewsletter(LOG_LEVEL_SUCCESS, '═══ Bestätigung abgeschlossen ═══', ['email' => $maskedEmail]);
    showResultPage(true, 'Ihre Anmeldung wurde erfolgreich bestätigt! Sie erhalten ab sofort unseren Newsletter.');
}

/**
 * Newsletter-Abmeldung
 */
function handleUnsubscribe(): void {
    $email = strtolower(trim($_GET['email'] ?? ''));
    $token = $_GET['token'] ?? '';
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    
    // Bei POST: E-Mail aus Body lesen
    if ($isPost) {
        $input = getJsonInput();
        $email = strtolower(trim($input['email'] ?? ''));
    }
    
    $maskedEmail = !empty($email) ? maskEmail($email) : 'unknown';
    logNewsletter(LOG_LEVEL_INFO, 'handleUnsubscribe gestartet', [
        'email' => $maskedEmail,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    
    if (empty($email)) {
        if ($isPost) {
            errorResponse('E-Mail-Adresse ist erforderlich.');
        } else {
            showResultPage(false, 'Ungültiger Abmelde-Link.', 'unsubscribe');
        }
        return;
    }
    
    $subscribers = loadSubscribers();
    
    if (!isset($subscribers[$email])) {
        logNewsletter(LOG_LEVEL_WARN, 'E-Mail nicht registriert', ['email' => $maskedEmail]);
        if ($isPost) {
            errorResponse('Diese E-Mail-Adresse ist nicht für den Newsletter registriert.');
        } else {
            showResultPage(false, 'Diese E-Mail-Adresse ist nicht für den Newsletter registriert.', 'unsubscribe');
        }
        return;
    }
    
    // Bei GET: Token prüfen (für Links aus E-Mails)
    if (!$isPost && !empty($token)) {
        $tokenHash = hash('sha256', $token);
        if (!isset($subscribers[$email]['unsubscribe_token']) || 
            !hash_equals($subscribers[$email]['unsubscribe_token'], $tokenHash)) {
            logNewsletter(LOG_LEVEL_WARN, 'Ungültiger Unsubscribe-Token', ['email' => $maskedEmail]);
            showResultPage(false, 'Ungültiger Abmelde-Link.', 'unsubscribe');
            return;
        }
    }
    
    // Subscriber entfernen
    $removedEmail = $email;
    unset($subscribers[$email]);
    
    if (!saveSubscribers($subscribers)) {
        logNewsletter(LOG_LEVEL_ERROR, 'Abmeldung speichern fehlgeschlagen');
        if ($isPost) {
            errorResponse('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 500);
        } else {
            showResultPage(false, 'Ein Fehler ist aufgetreten.', 'unsubscribe');
        }
        return;
    }
    
    logNewsletter(LOG_LEVEL_SUCCESS, 'Subscriber abgemeldet', ['email' => $maskedEmail]);
    
    // Abmeldebestätigung senden
    $htmlBody = getNewsletterUnsubscribeEmail($removedEmail);
    $textBody = <<<TEXT
Newsletter-Abmeldung bestätigt

Sie wurden erfolgreich von unserem Newsletter abgemeldet.

Wir respektieren Ihre Entscheidung. Falls Sie es sich anders überlegen, können Sie sich jederzeit wieder anmelden:
https://mailer-campaigner.de

--
Mailer Campaigner
https://mailer-campaigner.de
TEXT;
    
    sendMail($removedEmail, "Newsletter-Abmeldung bestätigt", $htmlBody, $textBody);
    
    logNewsletter(LOG_LEVEL_SUCCESS, '═══ Abmeldung abgeschlossen ═══', ['email' => $maskedEmail]);
    
    if ($isPost) {
        successResponse('Sie wurden erfolgreich vom Newsletter abgemeldet.');
    } else {
        showResultPage(true, 'Sie wurden erfolgreich vom Newsletter abgemeldet.', 'unsubscribe');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// RESULT PAGE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Zeigt eine HTML-Ergebnisseite (für Confirm/Unsubscribe Links)
 */
function showResultPage(bool $success, string $message, string $type = 'confirm'): void {
    $config = [
        'confirm' => [
            'successIcon' => '🎉',
            'successTitle' => 'Willkommen an Bord!',
            'successSubtitle' => 'Newsletter-Anmeldung bestätigt',
            'errorIcon' => '😕',
            'errorTitle' => 'Etwas ist schiefgelaufen',
            'errorSubtitle' => 'Bestätigung fehlgeschlagen'
        ],
        'unsubscribe' => [
            'successIcon' => '👋',
            'successTitle' => 'Auf Wiedersehen!',
            'successSubtitle' => 'Newsletter-Abmeldung bestätigt',
            'errorIcon' => '😕',
            'errorTitle' => 'Etwas ist schiefgelaufen',
            'errorSubtitle' => 'Abmeldung fehlgeschlagen'
        ]
    ];
    
    $cfg = $config[$type] ?? $config['confirm'];
    $icon = $success ? $cfg['successIcon'] : $cfg['errorIcon'];
    $title = $success ? $cfg['successTitle'] : $cfg['errorTitle'];
    $subtitle = $success ? $cfg['successSubtitle'] : $cfg['errorSubtitle'];
    $gradientStart = $success ? '#22c55e' : '#ef4444';
    $gradientEnd = $success ? '#06b6d4' : '#f97316';
    $accentColor = $success ? '#22c55e' : '#ef4444';
    
    header('Content-Type: text/html; charset=UTF-8');
    
    echo <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - Mailer Campaigner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }
        
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(34, 197, 94, 0.15), transparent),
                radial-gradient(ellipse 60% 40% at 100% 100%, rgba(6, 182, 212, 0.1), transparent),
                radial-gradient(ellipse 40% 30% at 0% 100%, rgba(34, 197, 94, 0.1), transparent);
            pointer-events: none;
        }
        
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: linear-gradient(135deg, {$gradientStart}, {$gradientEnd});
            border-radius: 50%;
            opacity: 0;
            animation: float-up 4s ease-in-out infinite;
        }
        
        @keyframes float-up {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }
        
        .container {
            position: relative;
            z-index: 10;
            background: linear-gradient(180deg, rgba(18, 18, 26, 0.95) 0%, rgba(10, 10, 15, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 60px 50px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            backdrop-filter: blur(20px);
            box-shadow: 
                0 0 0 1px rgba(255, 255, 255, 0.05),
                0 20px 50px -10px rgba(0, 0, 0, 0.5),
                0 0 100px -20px rgba(34, 197, 94, 0.3);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .icon-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }
        
        .icon {
            font-size: 72px;
            display: block;
            animation: bounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) 0.3s both;
        }
        
        @keyframes bounce {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        
        .icon-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, {$gradientStart}, {$gradientEnd});
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.2;
            filter: blur(30px);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.3; }
        }
        
        .subtitle {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: {$accentColor};
            margin-bottom: 12px;
            animation: fadeIn 0.5s ease 0.4s both;
        }
        
        h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #f8fafc, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            animation: fadeIn 0.5s ease 0.5s both;
        }
        
        .message {
            color: #94a3b8;
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 40px;
            animation: fadeIn 0.5s ease 0.6s both;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, {$gradientStart}, {$gradientEnd});
            color: white;
            text-decoration: none;
            padding: 16px 36px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeIn 0.5s ease 0.7s both;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px -10px rgba(34, 197, 94, 0.5);
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s;
        }
        
        .btn:hover svg {
            transform: translateX(4px);
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.5s ease 0.8s both;
        }
        
        .logo {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #22c55e, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .logo-sub {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #64748b;
        }
        
        @media (max-width: 520px) {
            .container { padding: 40px 30px; }
            h1 { font-size: 26px; }
            .icon { font-size: 56px; }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="icon-wrapper">
            <div class="icon-glow"></div>
            <span class="icon">{$icon}</span>
        </div>
        
        <p class="subtitle">{$subtitle}</p>
        <h1>{$title}</h1>
        <p class="message">{$message}</p>
        
        <a href="https://mailer-campaigner.de" class="btn">
            Zur Website
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
        
        <div class="footer">
            <div class="logo">Mailer Campaigner</div>
            <div class="logo-sub">Professional E-Mail Marketing</div>
        </div>
    </div>
    
    <script>
        const container = document.getElementById('particles');
        const particleCount = 20;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 4 + 's';
            particle.style.animationDuration = (3 + Math.random() * 3) + 's';
            particle.style.width = (2 + Math.random() * 4) + 'px';
            particle.style.height = particle.style.width;
            container.appendChild(particle);
        }
    </script>
</body>
</html>
HTML;
    exit;
}
