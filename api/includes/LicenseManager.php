<?php
/**
 * Mailer Campaigner - License Manager
 * 
 * Verwaltet Lizenzen in einer SQLite-Datenbank:
 * - Generierung von Lizenzschlüsseln
 * - Speicherung nach Stripe-Zahlung
 * - Verifizierung und Aktivierung
 * 
 * @package MailerCampaigner
 * @version 1.0.0
 */

require_once __DIR__ . '/helpers.php';

class LicenseManager {
    private PDO $db;
    
    /**
     * Initialisiert den License Manager und die Datenbank
     */
    public function __construct() {
        $this->initDatabase();
    }
    
    /**
     * Initialisiert die SQLite-Datenbank
     */
    private function initDatabase(): void {
        $dbDir = dirname(DATABASE_PATH);
        
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0755, true)) {
                logLicense(LOG_LEVEL_ERROR, 'Konnte Datenbankverzeichnis nicht erstellen', ['dir' => $dbDir]);
                throw new Exception('Database directory could not be created');
            }
        }
        
        try {
            $this->db = new PDO("sqlite:" . DATABASE_PATH);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Tabelle erstellen falls nicht vorhanden
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS licenses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    license_key TEXT UNIQUE NOT NULL,
                    email TEXT NOT NULL,
                    stripe_session_id TEXT,
                    stripe_payment_intent TEXT,
                    stripe_customer_id TEXT,
                    product TEXT DEFAULT 'pro',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME NOT NULL,
                    activated_at DATETIME,
                    machine_id TEXT,
                    machine_name TEXT,
                    is_active INTEGER DEFAULT 1,
                    notes TEXT
                )
            ");
            
            // Indices für schnelle Lookups
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_license_key ON licenses(license_key)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_email ON licenses(email)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_stripe_session ON licenses(stripe_session_id)");
            
            logLicense(LOG_LEVEL_DEBUG, 'Datenbank initialisiert');
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Datenbankfehler bei Initialisierung', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generiert einen neuen Lizenzschlüssel (XXXX-XXXX-XXXX-XXXX)
     */
    public function generateLicenseKey(): string {
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(bin2hex(random_bytes(2)));
        }
        return implode('-', $segments);
    }
    
    /**
     * Erstellt eine neue Lizenz nach erfolgreicher Zahlung
     */
    public function createLicense(
        string $email, 
        string $sessionId, 
        string $paymentIntent,
        ?string $customerId = null
    ): array {
        $maskedEmail = maskEmail($email);
        logLicense(LOG_LEVEL_INFO, 'Erstelle neue Lizenz', [
            'email' => $maskedEmail,
            'sessionId' => substr($sessionId, 0, 20) . '...'
        ]);
        
        // Eindeutigen Lizenzschlüssel generieren
        $maxAttempts = 10;
        $licenseKey = null;
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $candidate = $this->generateLicenseKey();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM licenses WHERE license_key = ?");
            $stmt->execute([$candidate]);
            
            if ($stmt->fetchColumn() == 0) {
                $licenseKey = $candidate;
                break;
            }
            
            logLicense(LOG_LEVEL_WARN, 'Lizenzschlüssel-Kollision, generiere neu', ['attempt' => $i + 1]);
        }
        
        if (!$licenseKey) {
            logLicense(LOG_LEVEL_ERROR, 'Konnte keinen eindeutigen Lizenzschlüssel generieren');
            throw new Exception('Could not generate unique license key');
        }
        
        // Ablaufdatum berechnen
        $expiresAt = date('Y-m-d H:i:s', strtotime("+" . LICENSE_VALIDITY_DAYS . " days"));
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO licenses 
                (license_key, email, stripe_session_id, stripe_payment_intent, stripe_customer_id, expires_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $licenseKey, 
                $email, 
                $sessionId, 
                $paymentIntent, 
                $customerId, 
                $expiresAt
            ]);
            
            $licenseData = [
                'license_key' => $licenseKey,
                'email' => $email,
                'expires_at' => $expiresAt,
                'product' => 'pro',
                'created_at' => date('c'),
            ];
            
            logLicense(LOG_LEVEL_SUCCESS, 'Lizenz erfolgreich erstellt', [
                'email' => $maskedEmail,
                'key_prefix' => substr($licenseKey, 0, 9) . '...',
                'expires_at' => $expiresAt
            ]);
            
            return $licenseData;
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Fehler beim Erstellen der Lizenz', [
                'error' => $e->getMessage(),
                'email' => $maskedEmail
            ]);
            throw $e;
        }
    }
    
    /**
     * Verifiziert einen Lizenzschlüssel
     */
    public function verifyLicense(string $licenseKey): array {
        $keyPrefix = substr($licenseKey, 0, 9) . '...';
        logLicense(LOG_LEVEL_INFO, 'Verifiziere Lizenz', ['key_prefix' => $keyPrefix]);
        
        $licenseKey = strtoupper(trim($licenseKey));
        
        // Format validieren
        if (!isValidLicenseKey($licenseKey)) {
            logLicense(LOG_LEVEL_WARN, 'Ungültiges Lizenzformat', ['key_prefix' => $keyPrefix]);
            return [
                'valid' => false, 
                'error' => 'Ungültiges Lizenzformat',
                'code' => 'INVALID_FORMAT'
            ];
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM licenses 
                WHERE license_key = ? AND is_active = 1
            ");
            $stmt->execute([$licenseKey]);
            $license = $stmt->fetch();
            
            if (!$license) {
                logLicense(LOG_LEVEL_WARN, 'Lizenz nicht gefunden', ['key_prefix' => $keyPrefix]);
                return [
                    'valid' => false, 
                    'error' => 'Lizenzschlüssel nicht gefunden',
                    'code' => 'NOT_FOUND'
                ];
            }
            
            // Ablaufdatum prüfen
            $now = new DateTime();
            $expires = new DateTime($license['expires_at']);
            
            if ($now > $expires) {
                logLicense(LOG_LEVEL_INFO, 'Lizenz abgelaufen', [
                    'key_prefix' => $keyPrefix,
                    'expired_at' => $license['expires_at']
                ]);
                return [
                    'valid' => false, 
                    'error' => 'Lizenz ist abgelaufen',
                    'code' => 'EXPIRED',
                    'expired_at' => $license['expires_at']
                ];
            }
            
            // Verbleibende Tage berechnen
            $remainingDays = $now->diff($expires)->days;
            
            logLicense(LOG_LEVEL_SUCCESS, 'Lizenz gültig', [
                'key_prefix' => $keyPrefix,
                'remaining_days' => $remainingDays
            ]);
            
            return [
                'valid' => true,
                'email' => $license['email'],
                'product' => $license['product'],
                'expires' => $license['expires_at'],
                'remaining_days' => $remainingDays,
                'activated' => !empty($license['activated_at']),
                'activated_at' => $license['activated_at'],
            ];
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Datenbankfehler bei Verifizierung', [
                'error' => $e->getMessage()
            ]);
            return [
                'valid' => false, 
                'error' => 'Interner Fehler bei der Verifizierung',
                'code' => 'DATABASE_ERROR'
            ];
        }
    }
    
    /**
     * Aktiviert eine Lizenz mit Machine-ID
     */
    public function activateLicense(string $licenseKey, string $machineId, ?string $machineName = null): array {
        $keyPrefix = substr($licenseKey, 0, 9) . '...';
        logLicense(LOG_LEVEL_INFO, 'Aktiviere Lizenz', [
            'key_prefix' => $keyPrefix,
            'machine_id' => substr($machineId, 0, 16) . '...'
        ]);
        
        // Zuerst verifizieren
        $verify = $this->verifyLicense($licenseKey);
        if (!$verify['valid']) {
            return $verify;
        }
        
        // Prüfen ob bereits auf anderer Maschine aktiviert
        try {
            $stmt = $this->db->prepare("
                SELECT machine_id, activated_at FROM licenses WHERE license_key = ?
            ");
            $stmt->execute([strtoupper($licenseKey)]);
            $current = $stmt->fetch();
            
            if ($current['machine_id'] && $current['machine_id'] !== $machineId) {
                logLicense(LOG_LEVEL_WARN, 'Lizenz bereits auf anderer Maschine aktiviert', [
                    'key_prefix' => $keyPrefix
                ]);
                return [
                    'success' => false,
                    'error' => 'Diese Lizenz ist bereits auf einem anderen Gerät aktiviert',
                    'code' => 'ALREADY_ACTIVATED_ELSEWHERE'
                ];
            }
            
            // Aktivierung speichern
            $stmt = $this->db->prepare("
                UPDATE licenses 
                SET activated_at = CURRENT_TIMESTAMP, 
                    machine_id = ?,
                    machine_name = ?
                WHERE license_key = ?
            ");
            $stmt->execute([$machineId, $machineName, strtoupper($licenseKey)]);
            
            logLicense(LOG_LEVEL_SUCCESS, 'Lizenz erfolgreich aktiviert', [
                'key_prefix' => $keyPrefix
            ]);
            
            return [
                'success' => true,
                'message' => 'Lizenz erfolgreich aktiviert',
                'expires' => $verify['expires'],
                'remaining_days' => $verify['remaining_days'],
            ];
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Datenbankfehler bei Aktivierung', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Interner Fehler bei der Aktivierung',
                'code' => 'DATABASE_ERROR'
            ];
        }
    }
    
    /**
     * Deaktiviert eine Lizenz
     */
    public function deactivateLicense(string $licenseKey): array {
        $keyPrefix = substr($licenseKey, 0, 9) . '...';
        logLicense(LOG_LEVEL_INFO, 'Deaktiviere Lizenz', ['key_prefix' => $keyPrefix]);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE licenses 
                SET machine_id = NULL, 
                    machine_name = NULL,
                    activated_at = NULL
                WHERE license_key = ?
            ");
            $stmt->execute([strtoupper($licenseKey)]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => 'Lizenz nicht gefunden',
                    'code' => 'NOT_FOUND'
                ];
            }
            
            logLicense(LOG_LEVEL_SUCCESS, 'Lizenz deaktiviert', ['key_prefix' => $keyPrefix]);
            
            return [
                'success' => true,
                'message' => 'Lizenz wurde deaktiviert und kann auf einem neuen Gerät aktiviert werden'
            ];
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Fehler bei Deaktivierung', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Interner Fehler',
                'code' => 'DATABASE_ERROR'
            ];
        }
    }
    
    /**
     * Prüft ob für eine Stripe-Session bereits eine Lizenz existiert
     */
    public function licenseExistsForSession(string $sessionId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM licenses WHERE stripe_session_id = ?
            ");
            $stmt->execute([$sessionId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Fehler bei Session-Prüfung', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Holt alle Lizenzen für eine E-Mail-Adresse
     */
    public function getLicensesByEmail(string $email): array {
        try {
            $stmt = $this->db->prepare("
                SELECT license_key, product, created_at, expires_at, activated_at, is_active
                FROM licenses 
                WHERE email = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([strtolower($email)]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Fehler beim Abrufen der Lizenzen', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Statistiken für Admin-Dashboard
     */
    public function getStatistics(): array {
        try {
            $stats = [];
            
            // Gesamtzahl Lizenzen
            $stats['total'] = $this->db->query("SELECT COUNT(*) FROM licenses")->fetchColumn();
            
            // Aktive Lizenzen
            $stats['active'] = $this->db->query("
                SELECT COUNT(*) FROM licenses 
                WHERE is_active = 1 AND expires_at > datetime('now')
            ")->fetchColumn();
            
            // Abgelaufene Lizenzen
            $stats['expired'] = $this->db->query("
                SELECT COUNT(*) FROM licenses 
                WHERE expires_at <= datetime('now')
            ")->fetchColumn();
            
            // Aktivierte Lizenzen
            $stats['activated'] = $this->db->query("
                SELECT COUNT(*) FROM licenses WHERE activated_at IS NOT NULL
            ")->fetchColumn();
            
            // Lizenzen letzter 30 Tage
            $stats['last_30_days'] = $this->db->query("
                SELECT COUNT(*) FROM licenses 
                WHERE created_at >= datetime('now', '-30 days')
            ")->fetchColumn();
            
            return $stats;
            
        } catch (PDOException $e) {
            logLicense(LOG_LEVEL_ERROR, 'Fehler bei Statistikabfrage', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
