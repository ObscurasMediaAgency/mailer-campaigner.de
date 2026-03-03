import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-terms',
  imports: [RouterLink],
  templateUrl: './terms.component.html',
  styleUrl: './terms.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TermsComponent {
  readonly companyInfo = {
    name: 'Obscuras Media Agency',
    owner: 'Sascha Gebel',
    email: 'info@obscuras-media-agency.de',
    supportEmail: 'support@obscuras-media-agency.de',
  };

  readonly productInfo = {
    name: 'Mailer Campaigner',
    price: '129,00 €',
    period: 'pro Jahr',
    vat: 'inkl. 19% MwSt.',
  };

  readonly sections = [
    {
      id: 'scope',
      title: 'Geltungsbereich',
      icon: 'fa-file-contract',
      content: [
        'Diese Allgemeinen Geschäftsbedingungen (AGB) gelten für alle Verträge zwischen der Obscuras Media Agency (nachfolgend \"Anbieter\") und dem Kunden über die Nutzung der Software \"Mailer Campaigner\".',
        'Abweichende Bedingungen des Kunden werden nicht anerkannt, es sei denn, der Anbieter stimmt ihrer Geltung ausdrücklich schriftlich zu.',
        'Die AGB gelten sowohl für Verbraucher als auch für Unternehmer, wobei für Verbraucher zusätzlich die gesetzlichen Verbraucherschutzvorschriften gelten.',
      ],
    },
    {
      id: 'contract',
      title: 'Vertragsschluss',
      icon: 'fa-handshake',
      content: [
        'Die Darstellung der Software auf der Website stellt kein rechtlich bindendes Angebot, sondern eine unverbindliche Aufforderung zur Bestellung dar.',
        'Durch das Absenden einer Bestellung gibt der Kunde ein verbindliches Angebot zum Kauf ab.',
        'Der Vertrag kommt zustande, wenn der Anbieter das Angebot durch eine Auftragsbestätigung per E-Mail oder durch Bereitstellung des Downloads annimmt.',
      ],
    },
    {
      id: 'license',
      title: 'Lizenz & Nutzungsrechte',
      icon: 'fa-key',
      content: [
        'Der Anbieter räumt dem Kunden mit Zahlung des Lizenzpreises ein nicht-exklusives, nicht übertragbares Recht zur Nutzung der Software für die Dauer des Lizenzzeitraums ein.',
        'Die Lizenz ist personengebunden und gilt für eine natürliche Person. Der Kunde darf die Software auf beliebig vielen eigenen Geräten installieren.',
        'Die Lizenz beinhaltet das Recht auf Updates und Support für den gebuchten Lizenzzeitraum (1 Jahr).',
        'Nach Ablauf der Lizenz kann die Software weiterhin genutzt werden, jedoch ohne Anspruch auf Updates oder Support. Eine Verlängerung ist durch Erwerb einer neuen Lizenz möglich.',
      ],
    },
    {
      id: 'restrictions',
      title: 'Nutzungsbeschränkungen',
      icon: 'fa-ban',
      content: [
        'Die Weitergabe, der Weiterverkauf oder die Unterlizenzierung der Software oder des Lizenzschlüssels ist untersagt.',
        'Eine Dekompilierung, Reverse Engineering oder Modifikation der Software ist nur im Rahmen der gesetzlich zulässigen Grenzen gestattet.',
        'Der Kunde ist für die rechtskonforme Nutzung der Software selbst verantwortlich, insbesondere hinsichtlich der Einhaltung des Datenschutzrechts (DSGVO) und des Wettbewerbsrechts (UWG) beim E-Mail-Versand.',
      ],
    },
    {
      id: 'prices',
      title: 'Preise & Zahlung',
      icon: 'fa-credit-card',
      content: [
        'Der aktuelle Lizenzpreis beträgt 129,00 € pro Jahr inklusive 19% Mehrwertsteuer.',
        'Die Zahlung erfolgt im Voraus für den gesamten Lizenzzeitraum.',
        'Akzeptierte Zahlungsmethoden sind Banküberweisung, PayPal und Kreditkarte.',
        'Bei Zahlungsverzug ist der Anbieter berechtigt, den Zugang zur Software zu sperren, bis die ausstehende Zahlung eingeht.',
      ],
    },
    {
      id: 'delivery',
      title: 'Bereitstellung & Download',
      icon: 'fa-download',
      content: [
        'Nach erfolgreicher Zahlung erhält der Kunde per E-Mail einen Download-Link sowie seinen persönlichen Lizenzschlüssel.',
        'Der Download ist ab Erhalt für mindestens 12 Monate verfügbar.',
        'Die Software wird \"wie besehen\" (as-is) bereitgestellt. Der Anbieter empfiehlt, vor der Installation die Systemanforderungen zu prüfen.',
      ],
    },
    {
      id: 'warranty',
      title: 'Gewährleistung',
      icon: 'fa-shield-halved',
      content: [
        'Der Anbieter gewährleistet, dass die Software im Wesentlichen den in der Dokumentation beschriebenen Funktionen entspricht.',
        'Bei Mängeln hat der Kunde zunächst Anspruch auf Nachbesserung. Schlägt diese fehl, kann der Kunde vom Vertrag zurücktreten oder eine Minderung verlangen.',
        'Die Gewährleistungsfrist beträgt für Verbraucher 2 Jahre, für Unternehmer 1 Jahr ab Bereitstellung.',
        'Keine Gewährleistung besteht für Fehler, die durch unsachgemäße Nutzung, Modifikation der Software oder inkompatible Systemumgebungen verursacht wurden.',
      ],
    },
    {
      id: 'liability',
      title: 'Haftung',
      icon: 'fa-gavel',
      content: [
        'Der Anbieter haftet unbeschränkt für Vorsatz und grobe Fahrlässigkeit sowie für Schäden aus der Verletzung von Leben, Körper oder Gesundheit.',
        'Bei einfacher Fahrlässigkeit haftet der Anbieter nur bei Verletzung wesentlicher Vertragspflichten (Kardinalpflichten), begrenzt auf den vertragstypischen, vorhersehbaren Schaden.',
        'Die Haftung für mittelbare Schäden, entgangenen Gewinn oder Datenverlust ist ausgeschlossen, soweit gesetzlich zulässig.',
        'Der Kunde ist für die ordnungsgemäße Datensicherung selbst verantwortlich.',
      ],
    },
    {
      id: 'withdrawal',
      title: 'Widerrufsrecht (Verbraucher)',
      icon: 'fa-rotate-left',
      content: [
        'Verbraucher haben das Recht, binnen 14 Tagen ohne Angabe von Gründen diesen Vertrag zu widerrufen.',
        'Das Widerrufsrecht erlischt bei digitalen Inhalten, wenn der Anbieter mit der Ausführung des Vertrags (Bereitstellung des Downloads) begonnen hat, nachdem der Verbraucher ausdrücklich zugestimmt und seine Kenntnis vom Verlust des Widerrufsrechts bestätigt hat.',
        'Um Ihr Widerrufsrecht auszuüben, müssen Sie uns mittels einer eindeutigen Erklärung (z.B. E-Mail) über Ihren Entschluss informieren.',
        'Im Falle eines wirksamen Widerrufs erstatten wir alle erhaltenen Zahlungen unverzüglich, spätestens binnen 14 Tagen.',
      ],
    },
    {
      id: 'termination',
      title: 'Laufzeit & Kündigung',
      icon: 'fa-calendar-xmark',
      content: [
        'Die Lizenz gilt für den gebuchten Zeitraum (standardmäßig 1 Jahr) und verlängert sich nicht automatisch.',
        'Eine ordentliche Kündigung während der Laufzeit ist nicht möglich.',
        'Das Recht zur außerordentlichen Kündigung aus wichtigem Grund bleibt unberührt.',
        'Bei schwerwiegenden Verstößen gegen diese AGB ist der Anbieter berechtigt, die Lizenz zu widerrufen.',
      ],
    },
    {
      id: 'final',
      title: 'Schlussbestimmungen',
      icon: 'fa-file-signature',
      content: [
        'Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.',
        'Für Unternehmer ist Gerichtsstand der Sitz des Anbieters.',
        'Sollten einzelne Bestimmungen dieser AGB unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt.',
        'Änderungen und Ergänzungen bedürfen der Schriftform. Dies gilt auch für die Aufhebung dieses Schriftformerfordernisses.',
      ],
    },
  ];

  readonly lastUpdated = '02. März 2026';

  scrollToSection(sectionId: string, event: Event): void {
    event.preventDefault();
    const element = document.getElementById(sectionId);
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
