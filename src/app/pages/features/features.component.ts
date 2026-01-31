import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-features',
  imports: [RouterLink],
  templateUrl: './features.component.html',
  styleUrl: './features.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FeaturesComponent {
  readonly mainFeatures = [
    {
      icon: 'fa-bullseye',
      title: 'Kampagnen-basierte Verwaltung',
      description: 'Organisiere verschiedene Anschreiben für unterschiedliche Zielgruppen. Jede Kampagne hat eigene Templates, Kontaktlisten und Konfigurationen.',
      highlights: [
        'Separate Kampagnen für verschiedene Branchen',
        'Individuelle Betreffzeilen pro Kampagne',
        'Eigene Kontaktlisten pro Kampagne',
        'Automatische Vorschau-Generierung',
      ],
    },
    {
      icon: 'fa-code',
      title: 'YAML-Konfiguration',
      description: 'Konfiguriere deine Kampagnen ohne Programmierung. Alles wird in übersichtlichen YAML-Dateien definiert.',
      highlights: [
        'Einfache Textdatei-Bearbeitung',
        'Versionskontrolle mit Git möglich',
        'Schnelle Änderungen ohne Neustart',
        'Template-Vorlagen für schnellen Start',
      ],
    },
    {
      icon: 'fa-envelope-open-text',
      title: 'HTML + Plaintext E-Mails',
      description: 'Multipart-E-Mails für maximale Kompatibilität. Deine E-Mails sehen überall gut aus.',
      highlights: [
        'Responsives HTML-Design',
        'Automatische Plaintext-Version',
        'Inline-CSS für E-Mail-Clients',
        'Vorschau vor dem Versand',
      ],
    },
    {
      icon: 'fa-user-tag',
      title: 'Dynamische Personalisierung',
      description: 'Nutze beliebige Platzhalter aus deiner CSV-Datei für maximale Individualisierung.',
      highlights: [
        'Unbegrenzte benutzerdefinierte Felder',
        'Automatische Feldübernahme aus CSV',
        'Globale Platzhalter (Jahr, Firma, etc.)',
        'Fehlerfreie Ersetzung',
      ],
    },
  ];

  readonly technicalFeatures = [
    {
      icon: 'fa-shield-halved',
      title: 'Intelligentes Rate-Limiting',
      description: 'Schütze deine Reputation mit konfigurierbaren Verzögerungen zwischen E-Mails.',
    },
    {
      icon: 'fa-clock',
      title: 'Zeitfenster-Planung',
      description: 'Sende nur zu professionellen Geschäftszeiten (Mo-Fr, 9-17 Uhr) für bessere Öffnungsraten.',
    },
    {
      icon: 'fa-ban',
      title: 'Bounce-Handling',
      description: 'Automatische Erkennung und Protokollierung von Hard-Bounces. Bereinige CSVs mit einem Klick.',
    },
    {
      icon: 'fa-rotate',
      title: 'Auto-Reconnect',
      description: 'Verbindungsabbrüche werden automatisch behandelt. Kampagnen laufen stabil durch.',
    },
    {
      icon: 'fa-clone',
      title: 'Duplikat-Schutz',
      description: 'Keine E-Mail wird doppelt versendet. Intelligente Tracking-Mechanismen.',
    },
    {
      icon: 'fa-database',
      title: 'Lokale Datenspeicherung',
      description: 'Alle Daten bleiben auf deinem System. Keine Cloud, keine Drittanbieter.',
    },
  ];

  readonly guiFeatures = [
    {
      icon: 'fa-gauge-high',
      title: 'Dashboard',
      description: 'Übersicht aller Kampagnen mit Live-Statistiken und letzten Aktivitäten.',
    },
    {
      icon: 'fa-folder-open',
      title: 'Kampagnen-Manager',
      description: 'Erstelle, bearbeite und starte Kampagnen mit der intuitiven Oberfläche.',
    },
    {
      icon: 'fa-users',
      title: 'Kontakt-Manager',
      description: 'Importiere Kontakte aus CSV oder Excel mit automatischem Feld-Mapping.',
    },
    {
      icon: 'fa-palette',
      title: 'Template-Editor',
      description: 'HTML-Editor mit Syntax-Highlighting und Live-Vorschau.',
    },
    {
      icon: 'fa-server',
      title: 'SMTP-Profile',
      description: 'Verwalte mehrere Absender-Konten mit sicherer Passwort-Speicherung.',
    },
    {
      icon: 'fa-user-slash',
      title: 'Blacklist-Verwaltung',
      description: 'DSGVO-konforme Abmelde-Verwaltung und automatische Bounce-Erfassung.',
    },
  ];
}
