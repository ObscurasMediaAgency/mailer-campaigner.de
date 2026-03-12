<?php
/**
 * E-Mail Template: Newsletter Double-Opt-In Bestätigung
 * 
 * @package MailerCampaigner
 */

/**
 * Generiert die HTML-E-Mail für die Double-Opt-In Bestätigung
 */
function getNewsletterDoubleOptInEmail(string $email, string $confirmUrl): string {
    $year = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Newsletter-Anmeldung bestätigen</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
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
                            
                            <!-- Icon -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(6, 182, 212, 0.2)); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 36px;">
                                            ✉️
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Title -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <h1 style="margin: 0; color: #fafafa; font-size: 24px; font-weight: 700; line-height: 1.3;">
                                            Fast geschafft!
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Description -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; color: #a1a1aa; font-size: 16px; line-height: 1.6;">
                                            Bitte bestätigen Sie Ihre Newsletter-Anmeldung,<br>
                                            indem Sie auf den Button klicken:
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <a href="{$confirmUrl}" 
                                           target="_blank"
                                           style="display: inline-block; background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 10px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 14px rgba(34, 197, 94, 0.4);">
                                            ✓ Anmeldung bestätigen
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Info Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 8px; padding: 16px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="color: #a1a1aa; font-size: 14px; line-height: 1.6;">
                                                    <strong style="color: #22c55e;">⏰ Hinweis:</strong> Dieser Link ist <strong style="color: #fafafa;">48 Stunden</strong> gültig.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Fallback Link -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 24px;">
                                        <p style="margin: 0; color: #71717a; font-size: 13px; line-height: 1.6;">
                                            Falls der Button nicht funktioniert, kopieren Sie diesen Link:<br>
                                            <a href="{$confirmUrl}" style="color: #22c55e; word-break: break-all;">{$confirmUrl}</a>
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
                                        <p style="margin: 0 0 8px 0; color: #52525b; font-size: 13px;">
                                            Falls Sie sich nicht angemeldet haben, können Sie diese E-Mail ignorieren.
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
