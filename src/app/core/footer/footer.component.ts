import { ChangeDetectionStrategy, Component, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

type NewsletterStatus = 'idle' | 'loading' | 'success' | 'error';

@Component({
  selector: 'app-footer',
  imports: [RouterLink],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FooterComponent {
  readonly currentYear = new Date().getFullYear();
  readonly newsletterEmail = signal('');
  readonly newsletterStatus = signal<NewsletterStatus>('idle');

  readonly footerLinks = {
    product: [
      { label: 'Features', path: '/features' },
      { label: 'Preise', path: '/pricing' },
      { label: 'Download', path: '/download' },
      { label: 'Changelog', path: '/changelog' },
    ],
    resources: [
      { label: 'Dokumentation', path: '/docs' },
      { label: 'Schnellstart', path: '/docs', fragment: 'quickstart' },
      { label: 'Installation', path: '/download' },
      { label: 'FAQ', path: '/pricing', fragment: 'faq' },
    ],
    legal: [
      { label: 'Impressum', path: '/impressum' },
      { label: 'Datenschutz', path: '/datenschutz' },
      { label: 'AGB', path: '/agb' },
    ],
  };

  readonly socialLinks = [
    { icon: 'fa-github', url: 'https://github.com/ObscurasMediaAgency', label: 'GitHub' },
    { icon : 'fa-telegram', url: 'https://t.me/obscuras_media_agency/8', label: 'Telegram' },
    // { icon: 'fa-discord', url: 'https://discord.gg/ObscurasMediaAgency', label: 'Discord' },
  ];

  updateEmail(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.newsletterEmail.set(input.value);
  }

  subscribeNewsletter(event: Event): void {
    event.preventDefault();
    const email = this.newsletterEmail();

    if (!email || !this.isValidEmail(email)) {
      this.newsletterStatus.set('error');
      return;
    }

    this.newsletterStatus.set('loading');

    // Simulate API call
    setTimeout(() => {
      // Store in localStorage for demo
      const subscribers = JSON.parse(localStorage.getItem('newsletter-subscribers') || '[]');
      subscribers.push({ email, date: new Date().toISOString() });
      localStorage.setItem('newsletter-subscribers', JSON.stringify(subscribers));

      this.newsletterStatus.set('success');
      this.newsletterEmail.set('');

      // Reset status after 3 seconds
      setTimeout(() => this.newsletterStatus.set('idle'), 3000);
    }, 800);
  }

  private isValidEmail(email: string): boolean {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
}
