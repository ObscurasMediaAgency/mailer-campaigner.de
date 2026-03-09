import { ChangeDetectionStrategy, Component, ElementRef, inject, OnInit, signal, viewChild } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

interface DocSection {
  id: string;
  title: string;
  icon: string;
}

interface DocArticle {
  title: string;
  description: string;
  icon: string;
  link: string;
  fragment?: string;
}

@Component({
  selector: 'app-docs',
  imports: [RouterLink],
  templateUrl: './docs.component.html',
  styleUrl: './docs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DocsComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  readonly activeSection = signal('quickstart');
  readonly docsContent = viewChild<ElementRef<HTMLElement>>('docsContent');

  ngOnInit(): void {
    this.route.fragment.subscribe((fragment) => {
      if (fragment && this.sections.some((s) => s.id === fragment)) {
        this.activeSection.set(fragment);
      }
    });
  }

  readonly sections: DocSection[] = [
    { id: 'quickstart', title: 'Schnellstart', icon: 'fa-rocket' },
    { id: 'campaigns', title: 'Kampagnen', icon: 'fa-bullseye' },
    { id: 'templates', title: 'Templates', icon: 'fa-code' },
    { id: 'cli', title: 'CLI-Befehle', icon: 'fa-terminal' },
    { id: 'gui', title: 'GUI-Oberfläche', icon: 'fa-desktop' },
    { id: 'smtp', title: 'SMTP-Konfiguration', icon: 'fa-server' },
  ];

  readonly quickGuides: DocArticle[] = [
    {
      title: 'Installation',
      description: 'Mailer Campaigner auf deinem System einrichten',
      icon: 'fa-download',
      link: '/download',
    },
    {
      title: 'Erste Kampagne',
      description: 'Erstelle deine erste E-Mail-Kampagne',
      icon: 'fa-paper-plane',
      link: '/docs',
      fragment: 'campaigns',
    },
    {
      title: 'SMTP einrichten',
      description: 'Konfiguriere deinen E-Mail-Server',
      icon: 'fa-envelope',
      link: '/docs',
      fragment: 'smtp',
    },
    {
      title: 'Kontakte importieren',
      description: 'Importiere Kontakte aus CSV oder Excel',
      icon: 'fa-users',
      link: '/docs',
      fragment: 'campaigns',
    },
  ];

  readonly placeholders = [
    { name: '{{YEAR}}', description: 'Aktuelles Jahr', example: '2026' },
    { name: '{{SENDER_NAME}}', description: 'Name des Absenders', example: 'Max Mustermann' },
    { name: '{{SENDER_TITLE}}', description: 'Titel des Absenders', example: 'Gründer & Entwickler' },
    { name: '{{COMPANY_NAME}}', description: 'Firmenname', example: 'Meine Firma GmbH' },
    { name: '{{COMPANY_URL}}', description: 'Website-URL', example: 'https://meine-firma.de' },
    { name: '{{FIRMA}}', description: 'Empfänger-Firma (aus CSV)', example: 'Musterfirma AG' },
    { name: '{{DOMAIN}}', description: 'Empfänger-Domain (aus CSV)', example: 'https://musterfirma.de' },
    { name: '{{EMAIL}}', description: 'Empfänger-E-Mail (aus CSV)', example: 'kontakt@musterfirma.de' },
  ];

  readonly cliCommands = [
    {
      command: 'python send_campaign.py --list',
      description: 'Alle verfügbaren Kampagnen anzeigen',
    },
    {
      command: 'python send_campaign.py <kampagne> --preview',
      description: 'HTML-Vorschau einer Kampagne erstellen',
    },
    {
      command: 'python send_campaign.py <kampagne> --dry-run',
      description: 'Testlauf ohne echten Versand',
    },
    {
      command: 'python send_campaign.py <kampagne> --test email@example.com',
      description: 'Test-Mail an eine Adresse senden',
    },
    {
      command: 'python send_campaign.py <kampagne> --schedule',
      description: 'Kampagne mit Zeitfenster (Mo-Fr 9-17) starten',
    },
    {
      command: 'python send_campaign.py <kampagne> --limit 10',
      description: 'Nur die ersten 10 E-Mails versenden',
    },
    {
      command: 'python send_campaign.py --bounces',
      description: 'Alle Bounce-Fehler anzeigen',
    },
    {
      command: 'python send_campaign.py --clean-bounces',
      description: 'CSVs von Bounces bereinigen',
    },
  ];

  readonly shortcuts = [
    { keys: 'Ctrl + N', action: 'Neue Kampagne' },
    { keys: 'Ctrl + I', action: 'Kontakte importieren' },
    { keys: 'Ctrl + 1-4', action: 'Zwischen Seiten wechseln' },
    { keys: 'F5', action: 'Kampagne starten' },
    { keys: 'F6', action: 'Kampagne pausieren' },
    { keys: 'Ctrl + P', action: 'Vorschau' },
    { keys: 'Ctrl + T', action: 'Test-E-Mail senden' },
    { keys: 'Ctrl + ,', action: 'Einstellungen' },
  ];

  setActiveSection(sectionId: string): void {
    this.activeSection.set(sectionId);
    const contentEl = this.docsContent()?.nativeElement;
    if (contentEl) {
      const headerOffset = 100;
      const elementPosition = contentEl.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.scrollY - headerOffset;
      window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
    }
  }

  navigateToGuide(guide: DocArticle, event: Event): void {
    // If it's an internal docs link with a fragment, handle it manually
    if (guide.link === '/docs' && guide.fragment) {
      event.preventDefault();
      this.setActiveSection(guide.fragment);
    } else if (guide.link !== '/docs') {
      // External link - let routerLink handle it
      // No need to prevent default
    }
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
  }
}
