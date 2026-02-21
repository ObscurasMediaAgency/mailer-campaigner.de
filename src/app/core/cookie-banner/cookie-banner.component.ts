import { ChangeDetectionStrategy, Component, signal, inject, PLATFORM_ID, OnInit } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

interface CookiePreferences {
  necessary: boolean;
  analytics: boolean;
  marketing: boolean;
}

@Component({
  selector: 'app-cookie-banner',
  template: `
    @if (showBanner()) {
      <div class="cookie-banner" role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-desc">
        <div class="cookie-banner__content">
          <div class="cookie-banner__text">
            <h3 id="cookie-title" class="cookie-banner__title">
              <i class="fa-solid fa-cookie-bite" aria-hidden="true"></i>
              Cookie-Einstellungen
            </h3>
            <p id="cookie-desc" class="cookie-banner__description">
              Wir nutzen Cookies, um dein Erlebnis zu verbessern. Du kannst wählen, welche 
              Cookies du akzeptieren möchtest.
            </p>
          </div>

          @if (showSettings()) {
            <div class="cookie-banner__settings">
              <label class="cookie-option">
                <input type="checkbox" checked disabled />
                <span class="cookie-option__info">
                  <strong>Notwendig</strong>
                  <small>Für die Grundfunktionen der Website erforderlich</small>
                </span>
              </label>

              <label class="cookie-option">
                <input 
                  type="checkbox" 
                  [checked]="preferences().analytics"
                  (change)="updatePreference('analytics', $event)"
                />
                <span class="cookie-option__info">
                  <strong>Analyse</strong>
                  <small>Helfen uns, die Nutzung zu verstehen</small>
                </span>
              </label>

              <label class="cookie-option">
                <input 
                  type="checkbox" 
                  [checked]="preferences().marketing"
                  (change)="updatePreference('marketing', $event)"
                />
                <span class="cookie-option__info">
                  <strong>Marketing</strong>
                  <small>Für personalisierte Inhalte</small>
                </span>
              </label>
            </div>
          }

          <div class="cookie-banner__actions">
            @if (!showSettings()) {
              <button type="button" class="btn btn-secondary btn-sm" (click)="toggleSettings()">
                Einstellungen
              </button>
            }
            <button type="button" class="btn btn-secondary btn-sm" (click)="acceptSelected()">
              @if (showSettings()) {
                Auswahl speichern
              } @else {
                Nur Notwendige
              }
            </button>
            <button type="button" class="btn btn-primary btn-sm" (click)="acceptAll()">
              Alle akzeptieren
            </button>
          </div>
        </div>
      </div>
    }
  `,
  styles: `
    .cookie-banner {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: var(--color-bg-secondary);
      border-top: 1px solid var(--color-border);
      padding: var(--spacing-lg);
      z-index: 1000;
      box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.3s ease-out;

      @keyframes slideUp {
        from {
          transform: translateY(100%);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      &__content {
        max-width: var(--container-max-width);
        margin: 0 auto;
      }

      &__title {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-size: 1.125rem;
        margin-bottom: var(--spacing-sm);

        i {
          color: var(--color-accent-primary);
        }
      }

      &__description {
        color: var(--color-text-secondary);
        font-size: 0.9375rem;
        margin-bottom: var(--spacing-md);
        max-width: 600px;
      }

      &__settings {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-lg);
        padding: var(--spacing-md);
        background: var(--color-bg-tertiary);
        border-radius: var(--radius-md);
      }

      &__actions {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-sm);
      }
    }

    .cookie-option {
      display: flex;
      align-items: flex-start;
      gap: var(--spacing-sm);
      cursor: pointer;
      min-width: 180px;

      input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--color-accent-primary);
        margin-top: 2px;
        cursor: pointer;

        &:disabled {
          opacity: 0.7;
          cursor: not-allowed;
        }
      }

      &__info {
        display: flex;
        flex-direction: column;
        gap: 2px;

        strong {
          font-size: 0.9375rem;
          color: var(--color-text-primary);
        }

        small {
          font-size: 0.8125rem;
          color: var(--color-text-muted);
        }
      }
    }

    @media (max-width: 576px) {
      .cookie-banner {
        &__actions {
          flex-direction: column;

          button {
            width: 100%;
          }
        }
      }

      .cookie-option {
        width: 100%;
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CookieBannerComponent implements OnInit {
  private readonly platformId = inject(PLATFORM_ID);
  private readonly STORAGE_KEY = 'mailer-campaigner-cookies';

  readonly showBanner = signal(false);
  readonly showSettings = signal(false);
  readonly preferences = signal<CookiePreferences>({
    necessary: true,
    analytics: false,
    marketing: false,
  });

  ngOnInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      const stored = localStorage.getItem(this.STORAGE_KEY);
      if (!stored) {
        // Show banner after short delay for better UX
        setTimeout(() => this.showBanner.set(true), 1000);
      }
    }
  }

  toggleSettings(): void {
    this.showSettings.update((s) => !s);
  }

  updatePreference(key: 'analytics' | 'marketing', event: Event): void {
    const checked = (event.target as HTMLInputElement).checked;
    this.preferences.update((p) => ({ ...p, [key]: checked }));
  }

  acceptAll(): void {
    this.preferences.set({
      necessary: true,
      analytics: true,
      marketing: true,
    });
    this.saveAndClose();
  }

  acceptSelected(): void {
    this.saveAndClose();
  }

  private saveAndClose(): void {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem(this.STORAGE_KEY, JSON.stringify(this.preferences()));
    }
    this.showBanner.set(false);
  }
}
