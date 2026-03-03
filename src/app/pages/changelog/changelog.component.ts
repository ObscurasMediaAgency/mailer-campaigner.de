import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

interface ChangelogEntry {
  version: string;
  date: string;
  type: 'major' | 'minor' | 'patch';
  highlights?: string[];
  changes: {
    category: 'added' | 'changed' | 'fixed' | 'removed' | 'security';
    items: string[];
  }[];
}

@Component({
  selector: 'app-changelog',
  imports: [RouterLink],
  templateUrl: './changelog.component.html',
  styleUrl: './changelog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ChangelogComponent {
  readonly categoryIcons: Record<string, { icon: string; label: string; color: string }> = {
    added: { icon: 'fa-plus', label: 'Hinzugefügt', color: '#10b981' },
    changed: { icon: 'fa-pen', label: 'Geändert', color: '#f59e0b' },
    fixed: { icon: 'fa-wrench', label: 'Behoben', color: '#6366f1' },
    removed: { icon: 'fa-trash', label: 'Entfernt', color: '#ef4444' },
    security: { icon: 'fa-shield-halved', label: 'Sicherheit', color: '#8b5cf6' },
  };

  readonly changelog: ChangelogEntry[] = [
    {
      version: '1.2.0',
      date: '02. März 2026',
      type: 'minor',
      highlights: [
        'Neue GUI-Oberfläche mit modernem Dark Mode',
        'Verbessertes Template-System mit Live-Vorschau',
      ],
      changes: [
        {
          category: 'added',
          items: [
            'GUI-Oberfläche mit Electron-Framework',
            'Template-Editor mit Syntax-Highlighting',
            'Live-Vorschau für HTML-Templates',
            'Import/Export von Kampagnen-Konfigurationen',
            'Mehrsprachige Oberfläche (DE/EN)',
          ],
        },
        {
          category: 'changed',
          items: [
            'Verbesserte Performance beim CSV-Import',
            'Modernisierte CLI-Ausgabe mit Farbunterstützung',
            'Aktualisierte Abhängigkeiten für Python 3.12',
          ],
        },
        {
          category: 'fixed',
          items: [
            'Encoding-Probleme bei Umlauten in CSV-Dateien',
            'Rate-Limiting funktioniert jetzt korrekt bei Reconnects',
            'SMTP-Timeout wird nun korrekt berücksichtigt',
          ],
        },
      ],
    },
    {
      version: '1.1.0',
      date: '15. Januar 2026',
      type: 'minor',
      highlights: [
        'Automatisches Bounce-Handling',
        'Zeitfenster-Planung für Versand',
      ],
      changes: [
        {
          category: 'added',
          items: [
            'Automatische Bounce-Erkennung und -Bereinigung',
            'Zeitfenster-Planung (Schedule) für Kampagnen',
            'Neue Platzhalter: {{YEAR}}, {{SENDER_TITLE}}',
            'CSV-Validierung vor dem Import',
            'Detailliertes Logging mit Log-Rotation',
          ],
        },
        {
          category: 'changed',
          items: [
            'Verbesserte Fehlerbehandlung bei SMTP-Verbindungen',
            'Optimierte Duplikat-Erkennung',
          ],
        },
        {
          category: 'fixed',
          items: [
            'Memory-Leak bei sehr großen Kontaktlisten behoben',
            'Korrektes Parsen von Excel-Dateien mit Formeln',
          ],
        },
      ],
    },
    {
      version: '1.0.1',
      date: '28. November 2025',
      type: 'patch',
      changes: [
        {
          category: 'fixed',
          items: [
            'SSL-Zertifikatsprobleme unter Windows behoben',
            'Korrektur der Lizenzaktivierung bei Proxy-Nutzung',
            'Fehlende Template-Dateien werden jetzt korrekt gemeldet',
          ],
        },
        {
          category: 'security',
          items: [
            'SMTP-Passwörter werden nun verschlüsselt gespeichert',
          ],
        },
      ],
    },
    {
      version: '1.0.0',
      date: '01. Oktober 2025',
      type: 'major',
      highlights: [
        'Erster stabiler Release',
        'CLI und GUI verfügbar',
      ],
      changes: [
        {
          category: 'added',
          items: [
            'Vollständige E-Mail-Kampagnen-Verwaltung',
            'CLI-Tool mit umfangreichen Optionen',
            'HTML und Plaintext E-Mail-Unterstützung',
            'YAML-basierte Kampagnen-Konfiguration',
            'Personalisierung mit Platzhaltern',
            'Intelligentes Rate-Limiting',
            'CSV und Excel Import',
            'Duplikat-Erkennung',
            'Mehrere SMTP-Profile',
            'Detailliertes Logging',
            'Linux, Windows und macOS Unterstützung',
          ],
        },
      ],
    },
  ];

  scrollToVersion(version: string, event: Event): void {
    event.preventDefault();
    const element = document.getElementById('v' + version);
    if (element) {
      const headerOffset = 100;
      const elementPosition = element.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.scrollY - headerOffset;

      window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth',
      });
    }
  }
}
