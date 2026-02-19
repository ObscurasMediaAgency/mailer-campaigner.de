import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-home',
  imports: [RouterLink],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HomeComponent {
  readonly features = [
    {
      icon: 'fa-bullseye',
      title: 'Kampagnen-basiert',
      description: 'Verwalte verschiedene Anschreiben für unterschiedliche Zielgruppen mit eigenen Templates und Kontaktlisten.',
    },
    {
      icon: 'fa-code',
      title: 'YAML-Konfiguration',
      description: 'Einfach anpassbare Kampagnen ohne Code-Änderungen. Definiere Betreff, Inhalt und Platzhalter in einer Datei.',
    },
    {
      icon: 'fa-envelope-open-text',
      title: 'HTML + Plaintext',
      description: 'Multipart-E-Mails für maximale Kompatibilität. Automatische Plaintext-Version für alle Empfänger.',
    },
    {
      icon: 'fa-user-tag',
      title: 'Personalisierung',
      description: 'Beliebige Platzhalter aus deiner CSV-Datei. Individualisiere jede E-Mail für maximale Wirkung.',
    },
    {
      icon: 'fa-shield-halved',
      title: 'Rate-Limiting',
      description: 'Intelligenter Schutz vor Spam-Klassifizierung. Konfigurierbare Verzögerungen zwischen E-Mails.',
    },
    {
      icon: 'fa-clock',
      title: 'Zeitfenster',
      description: 'Sende nur zu professionellen Geschäftszeiten (Mo-Fr, 9-17 Uhr) für bessere Öffnungsraten.',
    },
    {
      icon: 'fa-ban',
      title: 'Bounce-Handling',
      description: 'Automatische Erkennung und Protokollierung von Hard-Bounces. CSV-Bereinigung mit einem Klick.',
    },
    {
      icon: 'fa-database',
      title: 'Volle Datenkontrolle',
      description: 'Alle Daten bleiben lokal auf deinem System. Keine Cloud, keine Drittanbieter, volle DSGVO-Konformität.',
    },
  ];

  readonly stats = [
    { value: '10.000+', label: 'E-Mails versendet' },
    { value: '99.2%', label: 'Zustellrate' },
    { value: '45/h', label: 'Mails pro Stunde' },
    { value: '0€', label: 'Externe Kosten' },
  ];

  readonly testimonials = [
    {
      quote: 'Endlich ein Tool, das mir die volle Kontrolle über meine Geschäfts-E-Mails gibt. Die YAML-Konfiguration ist genial!',
      author: 'Max M.',
      role: 'Freelance Entwickler',
    },
    {
      quote: 'Das Bounce-Handling spart mir Stunden an manueller Arbeit. Absolut empfehlenswert für B2B-Kommunikation.',
      author: 'Sarah K.',
      role: 'Marketing Manager',
    },
    {
      quote: 'Keine monatlichen Kosten, keine Datenweitergabe an Dritte. Genau das, was ich gesucht habe.',
      author: 'Thomas L.',
      role: 'Geschäftsführer',
    },
  ];
}
