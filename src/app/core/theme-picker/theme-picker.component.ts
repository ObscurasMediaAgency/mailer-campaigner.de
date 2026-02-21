import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { ThemeService, AccentColor } from '../services/theme.service';

interface ColorOption {
  id: AccentColor;
  name: string;
  primary: string;
  secondary: string;
}

@Component({
  selector: 'app-theme-picker',
  template: `
    <div class="theme-picker" [class.theme-picker--open]="isOpen()">
      <button
        type="button"
        class="theme-picker__toggle"
        aria-label="Farbschema anpassen"
        (click)="toggle()"
      >
        <i class="fa-solid fa-palette" aria-hidden="true"></i>
      </button>

      @if (isOpen()) {
        <div class="theme-picker__panel">
          <div class="theme-picker__header">
            <span class="theme-picker__title">Akzentfarbe</span>
          </div>
          <div class="theme-picker__colors">
            @for (color of colors; track color.id) {
              <button
                type="button"
                class="theme-picker__color"
                [class.theme-picker__color--active]="themeService.accent() === color.id"
                [style.background]="'linear-gradient(135deg, ' + color.primary + ', ' + color.secondary + ')'"
                [attr.aria-label]="color.name"
                [attr.title]="color.name"
                (click)="setAccent(color.id)"
              >
                @if (themeService.accent() === color.id) {
                  <i class="fa-solid fa-check" aria-hidden="true"></i>
                }
              </button>
            }
          </div>
        </div>
      }
    </div>
  `,
  styles: `
    .theme-picker {
      position: fixed;
      bottom: 6rem;
      right: 2rem;
      z-index: 89;

      @media (max-width: 576px) {
        bottom: 5rem;
        right: 1rem;
      }

      &--open {
        .theme-picker__toggle {
          background: var(--color-accent-primary);
          border-color: var(--color-accent-primary);
          color: white;
        }
      }
    }

    .theme-picker__toggle {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      background: var(--color-bg-secondary);
      border: 1px solid var(--color-border);
      border-radius: 50%;
      color: var(--color-text-secondary);
      cursor: pointer;
      transition: all var(--transition-base);
      box-shadow: var(--shadow-md);

      &:hover {
        background: var(--color-bg-tertiary);
        border-color: var(--color-accent-primary);
        color: var(--color-accent-primary);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
      }

      i {
        font-size: 1rem;
      }
    }

    .theme-picker__panel {
      position: absolute;
      bottom: calc(100% + 0.75rem);
      right: 0;
      background: var(--color-bg-secondary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-lg);
      padding: var(--spacing-md);
      box-shadow: var(--shadow-xl);
      animation: slideUp 0.2s ease-out;
      min-width: 180px;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .theme-picker__header {
      margin-bottom: var(--spacing-sm);
    }

    .theme-picker__title {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .theme-picker__colors {
      display: flex;
      gap: var(--spacing-sm);
      flex-wrap: wrap;
    }

    .theme-picker__color {
      width: 32px;
      height: 32px;
      border: 2px solid transparent;
      border-radius: 50%;
      cursor: pointer;
      transition: all var(--transition-fast);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 0.75rem;

      &:hover {
        transform: scale(1.15);
      }

      &--active {
        border-color: var(--color-text-primary);
        box-shadow: 0 0 0 2px var(--color-bg-secondary);
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ThemePickerComponent {
  readonly themeService = inject(ThemeService);
  readonly isOpen = signal(false);

  readonly colors: ColorOption[] = [
    { id: 'indigo', name: 'Indigo', primary: '#6366f1', secondary: '#a855f7' },
    { id: 'emerald', name: 'Smaragd', primary: '#10b981', secondary: '#06b6d4' },
    { id: 'rose', name: 'Rose', primary: '#f43f5e', secondary: '#ec4899' },
    { id: 'amber', name: 'Bernstein', primary: '#f59e0b', secondary: '#ef4444' },
    { id: 'cyan', name: 'Cyan', primary: '#06b6d4', secondary: '#3b82f6' },
  ];

  toggle(): void {
    this.isOpen.update((o) => !o);
  }

  setAccent(accent: AccentColor): void {
    this.themeService.setAccent(accent);
  }
}
