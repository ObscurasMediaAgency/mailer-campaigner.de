<?php
/**
 * Mailer Campaigner - Email Service
 * 
 * Service für den Versand von Lizenz-bezogenen E-Mails:
 * - Lizenzschlüssel nach Kauf
 * - Zahlungsbestätigungen
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/helpers.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    /**
     * Sendet die Lizenz per E-Mail an den Käufer
     */
    public function sendLicenseEmail(string $toEmail, array $licenseData): bool {
        $maskedEmail = maskEmail($toEmail);
        logStripe(LOG_LEVEL_INFO, 'Sende Lizenz-E-Mail', ['to' => $maskedEmail]);
        
        $subject = "🎉 Dein Lizenzschlüssel für " . PRODUCT_NAME;
        
        $htmlBody = $this->buildLicenseEmailHtml($licenseData);
        $textBody = $this->buildLicenseEmailText($licenseData);
        
        return $this->sendMail($toEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Sendet eine E-Mail via PHPMailer
     */
    private function sendMail(string $to, string $subject, string $htmlBody, string $textBody): bool {
        $maskedEmail = maskEmail($to);
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
            
            // SSL-Optionen (für shared Hosting)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Absender und Empfänger
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($to);
            
            // Inhalt
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;
            
            // Headers für bessere Deliverability
            $mail->addCustomHeader('X-Mailer', 'MailerCampaigner/1.0');
            $mail->addCustomHeader('X-Priority', '1');
            
            $mail->send();
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            logStripe(LOG_LEVEL_SUCCESS, 'E-Mail erfolgreich gesendet', [
                'to' => $maskedEmail,
                'duration_ms' => $duration
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            logStripe(LOG_LEVEL_ERROR, 'E-Mail-Versand fehlgeschlagen', [
                'to' => $maskedEmail,
                'error' => $e->getMessage(),
                'mailer_error' => isset($mail) ? $mail->ErrorInfo : 'N/A',
                'duration_ms' => $duration
            ]);
            return false;
        }
    }
    
    /**
     * Erstellt die HTML-Version der Lizenz-E-Mail
     */
    private function buildLicenseEmailHtml(array $licenseData): string {
        $licenseKey = htmlspecialchars($licenseData['license_key']);
        $expiresAt = date('d.m.Y', strtotime($licenseData['expires_at']));
        $year = date('Y');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dein Lizenzschlüssel</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap');
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0a0a0f; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #0a0a0f;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="font-size: 28px; font-weight: 700; color: #22c55e;">
                                        &#9993; Mailer Campaigner
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Main Card -->
                    <tr>
                        <td style="background: linear-gradient(180deg, #18181b 0%, #0f0f14 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 48px 40px;">
                            
                            <!-- Success Icon -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <div style="width: 80px; height: 80px; background: rgba(34, 197, 94, 0.2); border-radius: 50%; text-align: center; line-height: 80px; font-size: 40px;">
                                            ✓
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Title -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <h1 style="margin: 0; color: #fafafa; font-size: 26px; font-weight: 700;">
                                            Vielen Dank für deinen Kauf!
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Description -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; color: #a1a1aa; font-size: 16px; line-height: 1.6;">
                                            Deine Lizenz für <strong style="color: #fafafa;">Mailer Campaigner Pro</strong> ist bereit.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- License Key Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: #09090b; border: 2px dashed #22c55e; border-radius: 12px; padding: 24px; text-align: center;">
                                        <p style="margin: 0 0 8px 0; color: #71717a; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;">
                                            Dein Lizenzschlüssel
                                        </p>
                                        <p style="margin: 0; font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: 24px; color: #22c55e; letter-spacing: 3px; font-weight: 600;">
                                            {$licenseKey}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- License Info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-top: 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #27272a; border-radius: 10px;">
                                            <tr>
                                                <td style="padding: 16px 20px; border-bottom: 1px solid #3f3f46;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="color: #71717a; font-size: 14px;">Produkt</td>
                                                            <td align="right" style="color: #fafafa; font-size: 14px; font-weight: 500;">Mailer Campaigner Pro</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 16px 20px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="color: #71717a; font-size: 14px;">Gültig bis</td>
                                                            <td align="right" style="color: #fafafa; font-size: 14px; font-weight: 500;">{$expiresAt}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Activation Steps -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-top: 32px;">
                                        <h2 style="margin: 0 0 16px 0; color: #fafafa; font-size: 18px; font-weight: 600;">
                                            So aktivierst du deine Lizenz:
                                        </h2>
                                        <ol style="margin: 0; padding-left: 20px; color: #a1a1aa; font-size: 15px; line-height: 2;">
                                            <li>Öffne den Mailer Campaigner</li>
                                            <li>Gehe zu <strong style="color: #fafafa;">Einstellungen → Lizenz</strong></li>
                                            <li>Gib deinen Lizenzschlüssel ein</li>
                                            <li>Klicke auf <strong style="color: #fafafa;">Aktivieren</strong></li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Download Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 32px;">
                                        <a href="https://mailer-campaigner.de/download" 
                                           target="_blank"
                                           style="display: inline-block; background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 10px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);">
                                            📥 Software herunterladen
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding-top: 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0 0 16px 0; color: #52525b; font-size: 13px;">
                                            Bei Fragen kontaktiere uns unter<br>
                                            <a href="mailto:support@mailer-campaigner.de" style="color: #22c55e;">support@mailer-campaigner.de</a>
                                        </p>
                                        <p style="margin: 0; color: #3f3f46; font-size: 12px;">
                                            © {$year} Mailer Campaigner · 
                                            <a href="https://mailer-campaigner.de/privacy" style="color: #3f3f46;">Datenschutz</a> · 
                                            <a href="https://mailer-campaigner.de/imprint" style="color: #3f3f46;">Impressum</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
    
    /**
     * Erstellt die Text-Version der Lizenz-E-Mail
     */
    private function buildLicenseEmailText(array $licenseData): string {
        $licenseKey = $licenseData['license_key'];
        $expiresAt = date('d.m.Y', strtotime($licenseData['expires_at']));
        
        return <<<TEXT
VIELEN DANK FÜR DEINEN KAUF!
=============================

Deine Lizenz für Mailer Campaigner Pro ist bereit.

DEIN LIZENZSCHLÜSSEL:
{$licenseKey}

LIZENZ-DETAILS:
- Produkt: Mailer Campaigner Pro
- Gültig bis: {$expiresAt}

SO AKTIVIERST DU DEINE LIZENZ:
1. Öffne den Mailer Campaigner
2. Gehe zu Einstellungen → Lizenz
3. Gib deinen Lizenzschlüssel ein
4. Klicke auf Aktivieren

Download: https://mailer-campaigner.de/download

Bei Fragen: support@mailer-campaigner.de

© 2026 Mailer Campaigner
TEXT;
    }
}
