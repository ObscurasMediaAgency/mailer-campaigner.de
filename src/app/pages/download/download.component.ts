import { ChangeDetectionStrategy, Component, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

interface InstallStep {
  command: string;
  description: string;
}

interface DownloadOption {
  platform: string;
  icon: string;
  available: boolean;
  version: string;
  requirements: string[];
  installSteps: InstallStep[];
  downloadUrl?: string;
  fileName?: string;
}

@Component({
  selector: 'app-download',
  imports: [RouterLink],
  templateUrl: './download.component.html',
  styleUrl: './download.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DownloadComponent {
  readonly selectedPlatform = signal<'linux' | 'windows' | 'macos'>('linux');

  readonly downloadOptions: Record<string, DownloadOption> = {
    linux: {
      platform: 'Linux',
      icon: 'fa-brands fa-linux',
      available: true,
      version: '1.0.0',
      requirements: [
        'Python 3.8 oder höher',
        'pip (Python Package Manager)',
        'PyQt6 für die GUI',
        'Internetverbindung für SMTP',
      ],
      installSteps: [
        {
          command: 'python -m venv venv && source venv/bin/activate',
          description: 'Virtuelle Umgebung erstellen und aktivieren',
        },
        {
          command: 'pip install -r requirements.txt',
          description: 'Abhängigkeiten installieren',
        },
        {
          command: 'cp .env.example .env && nano .env',
          description: 'Konfigurationsdatei erstellen und SMTP-Daten eintragen',
        },
        {
          command: 'python main.py',
          description: 'GUI starten',
        },
      ],
      downloadUrl: 'https://github.com/obscuras-media-agency/mailer-campaigner/releases/latest',
      fileName: 'mailer-campaigner-1.0.0-linux.tar.gz',
    },
    windows: {
      platform: 'Windows',
      icon: 'fa-brands fa-windows',
      available: false,
      version: 'Coming Soon',
      requirements: [
        'Windows 10/11',
        'Python 3.8 oder höher',
        'pip (Python Package Manager)',
      ],
      installSteps: [
        {
          command: 'python -m venv venv',
          description: 'Virtuelle Umgebung erstellen',
        },
        {
          command: 'venv\\Scripts\\activate',
          description: 'Virtuelle Umgebung aktivieren',
        },
        {
          command: 'pip install -r requirements.txt',
          description: 'Abhängigkeiten installieren',
        },
        {
          command: 'python main.py',
          description: 'GUI starten',
        },
      ],
    },
    macos: {
      platform: 'macOS',
      icon: 'fa-brands fa-apple',
      available: false,
      version: 'Coming Soon',
      requirements: [
        'macOS 11+ (Big Sur)',
        'Python 3.8 oder höher',
        'pip (Python Package Manager)',
      ],
      installSteps: [
        {
          command: 'python3 -m venv venv && source venv/bin/activate',
          description: 'Virtuelle Umgebung erstellen und aktivieren',
        },
        {
          command: 'pip install -r requirements.txt',
          description: 'Abhängigkeiten installieren',
        },
        {
          command: 'cp .env.example .env && nano .env',
          description: 'Konfigurationsdatei erstellen und SMTP-Daten eintragen',
        },
        {
          command: 'python main.py',
          description: 'GUI starten',
        },
      ],
    },
  };

  readonly cliQuickstart = [
    {
      title: 'Kampagnen anzeigen',
      command: 'python send_campaign.py --list',
      description: 'Zeigt alle verfügbaren Kampagnen an',
    },
    {
      title: 'Vorschau erstellen',
      command: 'python send_campaign.py meine_kampagne --preview',
      description: 'Generiert eine HTML-Vorschau der E-Mail',
    },
    {
      title: 'Test-Mail senden',
      command: 'python send_campaign.py meine_kampagne --test deine@email.de',
      description: 'Sendet eine Test-Mail an dich selbst',
    },
    {
      title: 'Kampagne starten',
      command: 'python send_campaign.py meine_kampagne --schedule',
      description: 'Startet den Versand mit Zeitfenster (Mo-Fr 9-17 Uhr)',
    },
  ];

  selectPlatform(platform: 'linux' | 'windows' | 'macos'): void {
    this.selectedPlatform.set(platform);
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
  }

  get currentPlatform(): DownloadOption {
    return this.downloadOptions[this.selectedPlatform()];
  }
}
