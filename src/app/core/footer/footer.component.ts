import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-footer',
  imports: [RouterLink],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FooterComponent {
  readonly currentYear = new Date().getFullYear();

  readonly footerLinks = {
    product: [
      { label: 'Features', path: '/features' },
      { label: 'Preise', path: '/pricing' },
      { label: 'Download', path: '/download' },
      { label: 'Changelog', path: '/docs/changelog' },
    ],
    resources: [
      { label: 'Dokumentation', path: '/docs' },
      { label: 'Schnellstart', path: '/docs/quickstart' },
      { label: 'Installation', path: '/download' },
      { label: 'FAQ', path: '/docs/faq' },
    ],
    legal: [
      { label: 'Impressum', path: '/impressum' },
      { label: 'Datenschutz', path: '/datenschutz' },
      { label: 'AGB', path: '/agb' },
    ],
  };

  readonly socialLinks = [
    { icon: 'fa-github', url: 'https://github.com/obscuras-media-agency', label: 'GitHub' },
    { icon : 'fa-telegram', url: 'https://t.me/obscuras_media_agency/8', label: 'Telegram' },
    { icon: 'fa-discord', url: 'https://discord.gg/obscuras-media-agency', label: 'Discord' },];
}
