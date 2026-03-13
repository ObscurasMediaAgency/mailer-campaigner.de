<?php
/**
 * E-Mail Template: Newsletter Abmeldung Bestätigung
 * 
 * @package MailerCampaigner
 */

/**
 * Generiert die HTML-E-Mail für die Abmeldebestätigung
 */
function getNewsletterUnsubscribeEmail(string $email): string {
    $year = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Newsletter-Abmeldung bestätigt</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <!--
    Fonts: Inter wird über den font-family Stack als Fallback geladen.
    @import in E-Mails wird von Spam-Filtern blockiert.
    -->
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
                                    <td style="font-size: 28px; font-weight: 700; background: linear-gradient(135deg, #22c55e, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                        &#9993; Mailer Campaigner
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Main Card -->
                    <tr>
                        <td style="background: linear-gradient(180deg, rgba(24, 24, 27, 0.98) 0%, rgba(15, 15, 20, 0.98) 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 48px 40px;">
                            
                            <!-- Wave Icon -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(113, 113, 122, 0.2), rgba(82, 82, 91, 0.2)); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px;">
                                            👋
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Title -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 8px;">
                                        <h1 style="margin: 0; color: #fafafa; font-size: 28px; font-weight: 700; line-height: 1.3;">
                                            Auf Wiedersehen!
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Subtitle -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; color: #71717a; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                            Newsletter-Abmeldung bestätigt
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Message -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; color: #a1a1aa; font-size: 16px; line-height: 1.7;">
                                            Sie wurden erfolgreich von unserem Newsletter abgemeldet und werden keine weiteren E-Mails von uns erhalten.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Info Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: rgba(113, 113, 122, 0.1); border: 1px solid rgba(113, 113, 122, 0.2); border-radius: 12px; padding: 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center">
                                                    <p style="margin: 0 0 16px 0; color: #a1a1aa; font-size: 15px; line-height: 1.6;">
                                                        <strong style="color: #fafafa;">Wir respektieren Ihre Entscheidung.</strong><br>
                                                        Falls Sie es sich anders überlegen, können Sie sich jederzeit wieder anmelden.
                                                    </p>
                                                    <a href="https://mailer-campaigner.de" 
                                                       target="_blank"
                                                       style="display: inline-block; background: rgba(34, 197, 94, 0.15); color: #22c55e; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid rgba(34, 197, 94, 0.3);">
                                                        Wieder anmelden
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Feedback Request -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 32px;">
                                        <p style="margin: 0; color: #71717a; font-size: 14px; line-height: 1.6;">
                                            Haben Sie Feedback für uns?<br>
                                            <a href="mailto:feedback@mailer-campaigner.de" style="color: #22c55e;">feedback@mailer-campaigner.de</a>
                                        </p>
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
                                            Dies ist die letzte E-Mail, die Sie von uns erhalten werden.
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
