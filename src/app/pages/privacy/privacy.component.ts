import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'app-privacy',
  templateUrl: './privacy.component.html',
  styleUrl: './privacy.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PrivacyComponent {
  readonly companyInfo = {
    name: 'Obscuras Media Agency',
    owner: 'Sascha Gebel',
    street: 'Amsdorfer Straße 27',
    city: '06317 Seegebiet Mansfelder Land',
    country: 'Deutschland',
    email: 'datenschutz@obscuras-media-agency.de',
  };

  readonly cookies = [
    {
      name: 'theme',
      purpose: 'Speichert die bevorzugte Farbschema-Einstellung (hell/dunkel)',
      duration: 'Dauerhaft (localStorage)',
      type: 'Erforderlich',
    },
    {
      name: 'accent-color',
      purpose: 'Speichert die gewählte Akzentfarbe der Oberfläche',
      duration: 'Dauerhaft (localStorage)',
      type: 'Erforderlich',
    },
    {
      name: 'cookie-consent',
      purpose: 'Speichert die Cookie-Einwilligung des Nutzers',
      duration: 'Dauerhaft (localStorage)',
      type: 'Erforderlich',
    },
    {
      name: 'newsletter-subscribers',
      purpose: 'Speichert Newsletter-Anmeldungen (nur Demo)',
      duration: 'Dauerhaft (localStorage)',
      type: 'Optional',
    },
  ];

  readonly rights = [
    {
      title: 'Auskunft',
      description: 'Sie haben das Recht, Auskunft über Ihre von uns verarbeiteten personenbezogenen Daten zu verlangen.',
    },
    {
      title: 'Berichtigung',
      description: 'Sie haben das Recht, die Berichtigung unrichtiger oder die Vervollständigung Ihrer bei uns gespeicherten Daten zu verlangen.',
    },
    {
      title: 'Löschung',
      description: 'Sie haben das Recht, die Löschung Ihrer bei uns gespeicherten Daten zu verlangen.',
    },
    {
      title: 'Einschränkung',
      description: 'Sie haben das Recht, die Einschränkung der Verarbeitung Ihrer personenbezogenen Daten zu verlangen.',
    },
    {
      title: 'Datenübertragbarkeit',
      description: 'Sie haben das Recht, die Daten, die wir auf Grundlage Ihrer Einwilligung verarbeiten, in einem strukturierten Format zu erhalten.',
    },
    {
      title: 'Widerspruch',
      description: 'Sie haben das Recht, der Verarbeitung Ihrer personenbezogenen Daten zu widersprechen.',
    },
    {
      title: 'Widerruf der Einwilligung',
      description: 'Sie haben das Recht, eine erteilte Einwilligung jederzeit zu widerrufen.',
    },
    {
      title: 'Beschwerde',
      description: 'Sie haben das Recht, sich bei einer Aufsichtsbehörde zu beschweren.',
    },
  ];

  readonly lastUpdated = '02. März 2026';
}
