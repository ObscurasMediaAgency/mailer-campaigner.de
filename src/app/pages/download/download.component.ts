import { ChangeDetectionStrategy, Component, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { KeyValuePipe } from '@angular/common';

type PlatformKey = 'linux' | 'windows' | 'macos';

interface PlatformInfo {
  name: string;
  icon: string;
  pythonCommand: string;
  activateCommand: string;
  requirements: string[];
}

interface InstallerCommand {
  command: string;
  description: string;
  icon: string;
}

interface Feature {
  icon: string;
  title: string;
  description: string;
}

@Component({
  selector: 'app-download',
  imports: [RouterLink, KeyValuePipe],
  templateUrl: './download.component.html',
  styleUrl: './download.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DownloadComponent {
  readonly version = '1.2.0';
  readonly downloadUrl = 'https://github.com/obscuras-media-agency/mailer-campaigner/releases/latest';
  readonly selectedPlatform = signal<PlatformKey>('linux');
  readonly copiedCommand = signal<string | null>(null);

  readonly platforms: Record<PlatformKey, PlatformInfo> = {
    linux: {
      name: 'Linux',
      icon: 'fa-brands fa-linux',
      pythonCommand: 'python3',
      activateCommand: './start.sh',
      requirements: [
        'Python 3.10 oder höher',
        'pip (Python Package Manager)',
        '500 MB freier Speicherplatz',
        'Internetverbindung',
      ],
    },
    windows: {
      name: 'Windows',
      icon: 'fa-brands fa-windows',
      pythonCommand: 'python',
      activateCommand: 'start.bat',
      requirements: [
        'Windows 10/11',
        'Python 3.10 oder höher',
        '500 MB freier Speicherplatz',
        'Internetverbindung',
      ],
    },
    macos: {
      name: 'macOS',
      icon: 'fa-brands fa-apple',
      pythonCommand: 'python3',
      activateCommand: './start.sh',
      requirements: [
        'macOS 11+ (Big Sur)',
        'Python 3.10 oder höher',
        '500 MB freier Speicherplatz',
        'Internetverbindung',
      ],
    },
  };

  readonly installerCommands: InstallerCommand[] = [
    {
      command: 'python install.py',
      description: 'Vollständige Installation mit Desktop-Integration',
      icon: 'fa-download',
    },
    {
      command: 'python install.py --update',
      description: 'Update mit automatischem Backup',
      icon: 'fa-arrows-rotate',
    },
    {
      command: 'python install.py --repair',
      description: 'Reparatur-Installation (venv neu erstellen)',
      icon: 'fa-screwdriver-wrench',
    },
    {
      command: 'python install.py --uninstall',
      description: 'Saubere Deinstallation',
      icon: 'fa-trash',
    },
    {
      command: 'python install.py --check',
      description: 'Nur Systemvoraussetzungen prüfen',
      icon: 'fa-clipboard-check',
    },
    {
      command: 'python install.py --dev',
      description: 'Mit Entwickler-Tools (pytest, black, mypy)',
      icon: 'fa-code',
    },
  ];

  readonly features: Feature[] = [
    {
      icon: 'fa-wand-magic-sparkles',
      title: 'Ein-Klick-Installation',
      description: 'Automatische venv-Erstellung, Dependency-Installation und Konfiguration',
    },
    {
      icon: 'fa-desktop',
      title: 'Desktop-Integration',
      description: 'Launcher-Skripte und Anwendungsmenü-Einträge für alle Plattformen',
    },
    {
      icon: 'fa-shield-halved',
      title: 'Sicheres Update',
      description: 'Automatisches Backup vor Updates, einfache Wiederherstellung',
    },
    {
      icon: 'fa-rotate-left',
      title: 'Reparatur-Modus',
      description: 'Probleme? Ein Befehl stellt alles wieder her',
    },
  ];

  readonly installSteps = [
    {
      number: 1,
      title: 'Download',
      description: 'Lade die neueste Version herunter und entpacke das Archiv.',
      icon: 'fa-download',
    },
    {
      number: 2,
      title: 'Installer starten',
      description: 'Führe den Universal-Installer aus.',
      icon: 'fa-terminal',
    },
    {
      number: 3,
      title: 'Fertig!',
      description: 'Starte die Anwendung über das Startmenü oder den Launcher.',
      icon: 'fa-rocket',
    },
  ];

  get currentPlatform(): PlatformInfo {
    return this.platforms[this.selectedPlatform()];
  }

  get installCommand(): string {
    return `${this.currentPlatform.pythonCommand} install.py`;
  }

  selectPlatform(platform: PlatformKey): void {
    this.selectedPlatform.set(platform);
  }

  copyToClipboard(text: string): void {
    navigator.clipboard.writeText(text);
    this.copiedCommand.set(text);
    setTimeout(() => this.copiedCommand.set(null), 2000);
  }
}
