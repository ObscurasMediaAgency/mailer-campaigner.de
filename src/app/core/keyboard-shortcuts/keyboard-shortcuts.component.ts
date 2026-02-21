import {
  ChangeDetectionStrategy,
  Component,
  signal,
  inject,
  PLATFORM_ID,
  OnInit,
  OnDestroy,
} from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

interface ShortcutGroup {
  title: string;
  shortcuts: { keys: string[]; description: string }[];
}

@Component({
  selector: 'app-keyboard-shortcuts',
  template: `
    @if (isOpen()) {
      <div class="shortcuts-backdrop" (click)="close()"></div>
      <div class="shortcuts-dialog" role="dialog" aria-modal="true" aria-labelledby="shortcuts-title">
        <div class="shortcuts-header">
          <h2 id="shortcuts-title" class="shortcuts-title">
            <i class="fa-solid fa-keyboard" aria-hidden="true"></i>
            Tastenkürzel
          </h2>
          <button type="button" class="shortcuts-close" aria-label="Schließen" (click)="close()">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
        </div>

        <div class="shortcuts-content">
          @for (group of shortcutGroups; track group.title) {
            <div class="shortcuts-group">
              <h3 class="shortcuts-group__title">{{ group.title }}</h3>
              <div class="shortcuts-list">
                @for (shortcut of group.shortcuts; track shortcut.description) {
                  <div class="shortcuts-item">
                    <div class="shortcuts-item__keys">
                      @for (key of shortcut.keys; track key; let last = $last) {
                        <kbd>{{ key }}</kbd>
                        @if (!last) {
                          <span>+</span>
                        }
                      }
                    </div>
                    <span class="shortcuts-item__description">{{ shortcut.description }}</span>
                  </div>
                }
              </div>
            </div>
          }
        </div>

        <div class="shortcuts-footer">
          <span>Drücke <kbd>?</kbd> um dieses Menü zu öffnen</span>
        </div>
      </div>
    }
  `,
  styles: `
    .shortcuts-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 998;
      animation: fadeIn 0.15s ease-out;
    }

    .shortcuts-dialog {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100%;
      max-width: 500px;
      max-height: 80vh;
      background: var(--color-bg-secondary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-xl);
      z-index: 999;
      overflow: hidden;
      animation: scaleIn 0.2s ease-out;

      @media (max-width: 540px) {
        left: var(--spacing-md);
        right: var(--spacing-md);
        transform: translateY(-50%);
        max-width: none;
        width: auto;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes scaleIn {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    .shortcuts-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--spacing-lg);
      border-bottom: 1px solid var(--color-border);
    }

    .shortcuts-title {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      font-size: 1.25rem;
      margin: 0;

      i {
        color: var(--color-accent-primary);
      }
    }

    .shortcuts-close {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      background: transparent;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      color: var(--color-text-secondary);
      cursor: pointer;
      transition: all var(--transition-fast);

      &:hover {
        background: var(--color-bg-tertiary);
        color: var(--color-text-primary);
      }
    }

    .shortcuts-content {
      padding: var(--spacing-lg);
      max-height: 50vh;
      overflow-y: auto;
    }

    .shortcuts-group {
      &:not(:last-child) {
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--color-border);
      }

      &__title {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--color-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: var(--spacing-md);
      }
    }

    .shortcuts-list {
      display: flex;
      flex-direction: column;
      gap: var(--spacing-sm);
    }

    .shortcuts-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--spacing-md);
      padding: var(--spacing-sm) 0;

      &__keys {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);

        kbd {
          display: inline-block;
          padding: 0.25rem 0.5rem;
          background: var(--color-bg-tertiary);
          border: 1px solid var(--color-border);
          border-radius: var(--radius-sm);
          font-family: var(--font-mono);
          font-size: 0.8125rem;
          color: var(--color-text-primary);
          min-width: 28px;
          text-align: center;
        }

        span {
          color: var(--color-text-muted);
          font-size: 0.75rem;
        }
      }

      &__description {
        color: var(--color-text-secondary);
        font-size: 0.9375rem;
      }
    }

    .shortcuts-footer {
      padding: var(--spacing-md) var(--spacing-lg);
      background: var(--color-bg-tertiary);
      border-top: 1px solid var(--color-border);
      text-align: center;
      font-size: 0.8125rem;
      color: var(--color-text-muted);

      kbd {
        padding: 0.125rem 0.375rem;
        background: var(--color-bg-secondary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-family: var(--font-mono);
        font-size: 0.75rem;
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class KeyboardShortcutsComponent implements OnInit, OnDestroy {
  private readonly platformId = inject(PLATFORM_ID);
  private keyListener: ((e: KeyboardEvent) => void) | null = null;

  readonly isOpen = signal(false);

  readonly shortcutGroups: ShortcutGroup[] = [
    {
      title: 'Navigation',
      shortcuts: [
        { keys: ['Ctrl/⌘', 'K'], description: 'Suche öffnen' },
        { keys: ['?'], description: 'Tastenkürzel anzeigen' },
        { keys: ['ESC'], description: 'Dialog schließen' },
      ],
    },
    {
      title: 'Allgemein',
      shortcuts: [
        { keys: ['↑', '↓'], description: 'In Ergebnissen navigieren' },
        { keys: ['Enter'], description: 'Auswahl bestätigen' },
        { keys: ['Tab'], description: 'Zum nächsten Element' },
      ],
    },
  ];

  ngOnInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      this.keyListener = (e: KeyboardEvent) => {
        // ? to open (without modifier keys, not in input)
        if (
          e.key === '?' &&
          !e.ctrlKey &&
          !e.metaKey &&
          !this.isInputFocused()
        ) {
          e.preventDefault();
          this.toggle();
        }
        // Escape to close
        if (e.key === 'Escape' && this.isOpen()) {
          this.close();
        }
      };
      document.addEventListener('keydown', this.keyListener);
    }
  }

  ngOnDestroy(): void {
    if (this.keyListener && isPlatformBrowser(this.platformId)) {
      document.removeEventListener('keydown', this.keyListener);
    }
  }

  private isInputFocused(): boolean {
    const activeElement = document.activeElement;
    return (
      activeElement instanceof HTMLInputElement ||
      activeElement instanceof HTMLTextAreaElement ||
      activeElement?.getAttribute('contenteditable') === 'true'
    );
  }

  toggle(): void {
    this.isOpen.update((o) => !o);
  }

  close(): void {
    this.isOpen.set(false);
  }
}
