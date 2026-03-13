<?php
/**
 * E-Mail Template: Newsletter Willkommen
 * 
 * @package MailerCampaigner
 */

/**
 * Generiert die HTML-E-Mail für die Willkommensnachricht nach Bestätigung
 */
function getNewsletterWelcomeEmail(string $email, string $unsubscribeUrl): string {
    $year = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Willkommen beim Newsletter</title>
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
                            
                            <!-- Confetti Icon -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(6, 182, 212, 0.2)); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 40px;">
                                            🎉
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Title -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 8px;">
                                        <h1 style="margin: 0; color: #fafafa; font-size: 28px; font-weight: 700; line-height: 1.3;">
                                            Willkommen an Bord!
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Subtitle -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; color: #22c55e; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                            Newsletter-Anmeldung bestätigt
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Welcome Message -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <p style="margin: 0; color: #a1a1aa; font-size: 16px; line-height: 1.7;">
                                            Vielen Dank für Ihre Anmeldung! Ab sofort erhalten Sie exklusive Updates zu:
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Features List -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <!-- Feature 1 -->
                                            <tr>
                                                <td style="padding: 12px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="48" valign="top">
                                                                <div style="width: 40px; height: 40px; background: rgba(34, 197, 94, 0.15); border-radius: 10px; text-align: center; line-height: 40px; font-size: 18px;">
                                                                    🚀
                                                                </div>
                                                            </td>
                                                            <td style="padding-left: 12px;">
                                                                <p style="margin: 0 0 4px 0; color: #fafafa; font-size: 15px; font-weight: 600;">Neue Features & Updates</p>
                                                                <p style="margin: 0; color: #71717a; font-size: 13px;">Erfahren Sie als Erster von neuen Funktionen</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- Feature 2 -->
                                            <tr>
                                                <td style="padding: 12px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="48" valign="top">
                                                                <div style="width: 40px; height: 40px; background: rgba(6, 182, 212, 0.15); border-radius: 10px; text-align: center; line-height: 40px; font-size: 18px;">
                                                                    💡
                                                                </div>
                                                            </td>
                                                            <td style="padding-left: 12px;">
                                                                <p style="margin: 0 0 4px 0; color: #fafafa; font-size: 15px; font-weight: 600;">Tipps & Best Practices</p>
                                                                <p style="margin: 0; color: #71717a; font-size: 13px;">Wertvolle Insights für erfolgreiches E-Mail-Marketing</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- Feature 3 -->
                                            <tr>
                                                <td style="padding: 12px 0;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="48" valign="top">
                                                                <div style="width: 40px; height: 40px; background: rgba(249, 115, 22, 0.15); border-radius: 10px; text-align: center; line-height: 40px; font-size: 18px;">
                                                                    🎁
                                                                </div>
                                                            </td>
                                                            <td style="padding-left: 12px;">
                                                                <p style="margin: 0 0 4px 0; color: #fafafa; font-size: 15px; font-weight: 600;">Exklusive Angebote</p>
                                                                <p style="margin: 0; color: #71717a; font-size: 13px;">Spezielle Rabatte nur für Newsletter-Abonnenten</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <a href="https://mailer-campaigner.de" 
                                           target="_blank"
                                           style="display: inline-block; background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);">
                                            Zur Website →
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
                                            Sie erhalten diese E-Mail, weil Sie sich für unseren Newsletter angemeldet haben.
                                        </p>
                                        <p style="margin: 0 0 16px 0;">
                                            <a href="{$unsubscribeUrl}" style="color: #71717a; font-size: 13px; text-decoration: underline;">Newsletter abbestellen</a>
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
