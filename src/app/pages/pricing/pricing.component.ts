import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-pricing',
  imports: [RouterLink],
  templateUrl: './pricing.component.html',
  styleUrl: './pricing.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PricingComponent {
  readonly price = {
    amount: 129,
    currency: '€',
    period: 'Jahr',
    vat: 'inkl. MwSt.',
  };

  readonly features = [
    { text: 'Unbegrenzte Kampagnen', included: true },
    { text: 'Unbegrenzte Kontakte', included: true },
    { text: 'Unbegrenzte E-Mails', included: true },
    { text: 'GUI & CLI Oberfläche', included: true },
    { text: 'HTML + Plaintext E-Mails', included: true },
    { text: 'YAML-basierte Konfiguration', included: true },
    { text: 'Personalisierung mit Platzhaltern', included: true },
    { text: 'Intelligentes Rate-Limiting', included: true },
    { text: 'Zeitfenster-Planung (Schedule)', included: true },
    { text: 'Automatisches Bounce-Handling', included: true },
    { text: 'CSV/Excel Import & Export', included: true },
    { text: 'Duplikat-Erkennung', included: true },
    { text: 'Template-Editor mit Vorschau', included: true },
    { text: 'Mehrere SMTP-Profile', included: true },
    { text: 'Detailliertes Logging', included: true },
    { text: '1 Jahr Updates inklusive', included: true },
    { text: 'E-Mail Support', included: true },
  ];

  readonly faqs = [
    {
      question: 'Was passiert nach Ablauf des Jahres?',
      answer: 'Nach Ablauf der Lizenz kannst du die Software weiterhin nutzen, erhältst jedoch keine Updates mehr. Du kannst jederzeit eine neue Jahreslizenz erwerben, um wieder Updates zu bekommen.',
    },
    {
      question: 'Gibt es versteckte Kosten?',
      answer: 'Nein. Der Preis von 129 € pro Jahr ist alles, was du zahlst. Es gibt keine zusätzlichen Gebühren pro E-Mail, pro Kampagne oder für Premium-Features.',
    },
    {
      question: 'Auf wie vielen Geräten kann ich die Software nutzen?',
      answer: 'Die Lizenz gilt für einen Nutzer. Du kannst die Software auf beliebig vielen deiner eigenen Geräte installieren (z.B. Arbeits-PC und Laptop).',
    },
    {
      question: 'Kann ich vor dem Kauf testen?',
      answer: 'Ja! Du kannst die Software 14 Tage lang kostenlos testen. Danach wird eine gültige Lizenz benötigt.',
    },
    {
      question: 'Wie erhalte ich Support?',
      answer: 'Support erfolgt per E-Mail. Wir antworten in der Regel innerhalb von 24-48 Stunden an Werktagen.',
    },
    {
      question: 'Welche Zahlungsmethoden werden akzeptiert?',
      answer: 'Wir akzeptieren Kreditkarte, PayPal und Überweisung. Nach der Zahlung erhältst du sofort deinen Lizenzschlüssel per E-Mail.',
    },
  ];

  readonly comparisonItems = [
    { feature: 'Monatliche Kosten', competitor: '50-200€/Monat', us: '10,75€/Monat' },
    { feature: 'Datenspeicherung', competitor: 'Cloud (Drittanbieter)', us: 'Lokal (dein System)' },
    { feature: 'E-Mail-Limit', competitor: 'Je nach Plan', us: 'Unbegrenzt' },
    { feature: 'Kontakt-Limit', competitor: 'Je nach Plan', us: 'Unbegrenzt' },
    { feature: 'DSGVO-Konformität', competitor: 'Komplex', us: 'Automatisch (lokale Daten)' },
    { feature: 'Kontrolle über Daten', competitor: 'Eingeschränkt', us: 'Vollständig' },
  ];
}
