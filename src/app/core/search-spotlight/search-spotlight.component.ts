import {
  ChangeDetectionStrategy,
  Component,
  signal,
  inject,
  PLATFORM_ID,
  OnInit,
  OnDestroy,
  computed,
} from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { Router } from '@angular/router';

interface SearchResult {
  title: string;
  description: string;
  path: string;
  icon: string;
  category: string;
}

@Component({
  selector: 'app-search-spotlight',
  template: `
    @if (isOpen()) {
      <div class="spotlight-backdrop" (click)="close()" (keydown.escape)="close()"></div>
      <div class="spotlight" role="dialog" aria-modal="true" aria-labelledby="spotlight-title">
        <div class="spotlight__header">
          <div class="spotlight__search-icon">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          </div>
          <input
            #searchInput
            type="text"
            id="spotlight-title"
            class="spotlight__input"
            placeholder="Suche nach Seiten, Docs, Features..."
            [value]="query()"
            (input)="onSearch($event)"
            (keydown.arrowdown)="navigateResults(1)"
            (keydown.arrowup)="navigateResults(-1)"
            (keydown.enter)="selectResult()"
            autocomplete="off"
          />
          <kbd class="spotlight__shortcut">ESC</kbd>
        </div>

        <div class="spotlight__results">
          @if (filteredResults().length > 0) {
            @for (result of filteredResults(); track result.path; let i = $index) {
              <button
                type="button"
                class="spotlight__result"
                [class.spotlight__result--active]="selectedIndex() === i"
                (click)="goTo(result.path)"
                (mouseenter)="selectedIndex.set(i)"
              >
                <div class="spotlight__result-icon">
                  <i class="fa-solid {{ result.icon }}" aria-hidden="true"></i>
                </div>
                <div class="spotlight__result-content">
                  <span class="spotlight__result-title">{{ result.title }}</span>
                  <span class="spotlight__result-description">{{ result.description }}</span>
                </div>
                <span class="spotlight__result-category">{{ result.category }}</span>
              </button>
            }
          } @else if (query().length > 0) {
            <div class="spotlight__empty">
              <i class="fa-solid fa-search" aria-hidden="true"></i>
              <p>Keine Ergebnisse für "{{ query() }}"</p>
            </div>
          } @else {
            <div class="spotlight__hints">
              <p class="spotlight__hints-title">Schnellzugriff</p>
              @for (result of searchResults.slice(0, 5); track result.path) {
                <button
                  type="button"
                  class="spotlight__hint"
                  (click)="goTo(result.path)"
                >
                  <i class="fa-solid {{ result.icon }}" aria-hidden="true"></i>
                  {{ result.title }}
                </button>
              }
            </div>
          }
        </div>

        <div class="spotlight__footer">
          <div class="spotlight__footer-item">
            <kbd>↑</kbd><kbd>↓</kbd> Navigation
          </div>
          <div class="spotlight__footer-item">
            <kbd>Enter</kbd> Öffnen
          </div>
          <div class="spotlight__footer-item">
            <kbd>ESC</kbd> Schließen
          </div>
        </div>
      </div>
    }
  `,
  styles: `
    .spotlight-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 999;
      animation: fadeIn 0.15s ease-out;
    }

    .spotlight {
      position: fixed;
      top: 20%;
      left: 50%;
      transform: translateX(-50%);
      width: 100%;
      max-width: 600px;
      background: var(--color-bg-secondary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-xl), 0 0 60px rgba(99, 102, 241, 0.15);
      z-index: 1000;
      overflow: hidden;
      animation: slideIn 0.2s ease-out;

      @media (max-width: 640px) {
        top: 10%;
        left: var(--spacing-md);
        right: var(--spacing-md);
        transform: none;
        max-width: none;
        width: auto;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
    }

    .spotlight__header {
      display: flex;
      align-items: center;
      gap: var(--spacing-md);
      padding: var(--spacing-md) var(--spacing-lg);
      border-bottom: 1px solid var(--color-border);
    }

    .spotlight__search-icon {
      color: var(--color-text-muted);
      font-size: 1.125rem;
    }

    .spotlight__input {
      flex: 1;
      background: transparent;
      border: none;
      color: var(--color-text-primary);
      font-size: 1.125rem;
      outline: none;

      &::placeholder {
        color: var(--color-text-muted);
      }
    }

    .spotlight__shortcut {
      padding: 0.25rem 0.5rem;
      background: var(--color-bg-tertiary);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-sm);
      font-size: 0.75rem;
      color: var(--color-text-muted);
      font-family: var(--font-mono);
    }

    .spotlight__results {
      max-height: 400px;
      overflow-y: auto;
      padding: var(--spacing-sm);
    }

    .spotlight__result {
      display: flex;
      align-items: center;
      gap: var(--spacing-md);
      width: 100%;
      padding: var(--spacing-md);
      background: transparent;
      border: none;
      border-radius: var(--radius-md);
      text-align: left;
      cursor: pointer;
      transition: all var(--transition-fast);

      &:hover,
      &--active {
        background: var(--color-bg-tertiary);
      }

      &--active {
        outline: 2px solid var(--color-accent-primary);
        outline-offset: -2px;
      }
    }

    .spotlight__result-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: var(--gradient-subtle);
      border-radius: var(--radius-md);
      color: var(--color-accent-primary);
      flex-shrink: 0;
    }

    .spotlight__result-content {
      flex: 1;
      min-width: 0;
    }

    .spotlight__result-title {
      display: block;
      font-weight: 500;
      color: var(--color-text-primary);
      margin-bottom: 2px;
    }

    .spotlight__result-description {
      display: block;
      font-size: 0.8125rem;
      color: var(--color-text-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .spotlight__result-category {
      font-size: 0.75rem;
      color: var(--color-text-muted);
      padding: 0.25rem 0.5rem;
      background: var(--color-bg-tertiary);
      border-radius: var(--radius-sm);
      flex-shrink: 0;
    }

    .spotlight__empty {
      text-align: center;
      padding: var(--spacing-2xl);
      color: var(--color-text-muted);

      i {
        font-size: 2rem;
        margin-bottom: var(--spacing-md);
        opacity: 0.5;
      }

      p {
        margin: 0;
      }
    }

    .spotlight__hints {
      padding: var(--spacing-md);
    }

    .spotlight__hints-title {
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--color-text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: var(--spacing-sm);
    }

    .spotlight__hint {
      display: flex;
      align-items: center;
      gap: var(--spacing-sm);
      width: 100%;
      padding: var(--spacing-sm) var(--spacing-md);
      background: transparent;
      border: none;
      border-radius: var(--radius-md);
      color: var(--color-text-secondary);
      font-size: 0.9375rem;
      cursor: pointer;
      transition: all var(--transition-fast);

      &:hover {
        background: var(--color-bg-tertiary);
        color: var(--color-text-primary);
      }

      i {
        color: var(--color-accent-primary);
        width: 20px;
        text-align: center;
      }
    }

    .spotlight__footer {
      display: flex;
      justify-content: center;
      gap: var(--spacing-lg);
      padding: var(--spacing-sm) var(--spacing-lg);
      background: var(--color-bg-tertiary);
      border-top: 1px solid var(--color-border);
    }

    .spotlight__footer-item {
      display: flex;
      align-items: center;
      gap: var(--spacing-xs);
      font-size: 0.75rem;
      color: var(--color-text-muted);

      kbd {
        padding: 0.125rem 0.375rem;
        background: var(--color-bg-secondary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-family: var(--font-mono);
        font-size: 0.6875rem;
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchSpotlightComponent implements OnInit, OnDestroy {
  private readonly platformId = inject(PLATFORM_ID);
  private readonly router = inject(Router);
  private keyListener: ((e: KeyboardEvent) => void) | null = null;

  readonly isOpen = signal(false);
  readonly query = signal('');
  readonly selectedIndex = signal(0);

  readonly searchResults: SearchResult[] = [
    { title: 'Startseite', description: 'Zurück zur Hauptseite', path: '/', icon: 'fa-house', category: 'Seite' },
    { title: 'Features', description: 'Alle Funktionen im Überblick', path: '/features', icon: 'fa-star', category: 'Seite' },
    { title: 'Preise', description: 'Einmalige Jahreslizenz für €129', path: '/pricing', icon: 'fa-tag', category: 'Seite' },
    { title: 'Download', description: 'Software herunterladen', path: '/download', icon: 'fa-download', category: 'Seite' },
    { title: 'Dokumentation', description: 'Anleitungen und Guides', path: '/docs', icon: 'fa-book', category: 'Docs' },
    { title: 'Schnellstart', description: 'In 5 Minuten loslegen', path: '/docs', icon: 'fa-rocket', category: 'Docs' },
    { title: 'CLI-Befehle', description: 'Kommandozeilen-Referenz', path: '/docs', icon: 'fa-terminal', category: 'Docs' },
    { title: 'SMTP einrichten', description: 'E-Mail-Server konfigurieren', path: '/docs', icon: 'fa-server', category: 'Docs' },
    { title: 'Templates', description: 'E-Mail-Vorlagen erstellen', path: '/docs', icon: 'fa-code', category: 'Docs' },
    { title: 'GUI-Oberfläche', description: 'Grafische Benutzeroberfläche', path: '/docs', icon: 'fa-desktop', category: 'Docs' },
  ];

  readonly filteredResults = computed(() => {
    const q = this.query().toLowerCase().trim();
    if (!q) return [];

    return this.searchResults.filter(
      (r) =>
        r.title.toLowerCase().includes(q) ||
        r.description.toLowerCase().includes(q) ||
        r.category.toLowerCase().includes(q)
    );
  });

  ngOnInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      this.keyListener = (e: KeyboardEvent) => {
        // Cmd/Ctrl + K to open
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
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

  toggle(): void {
    this.isOpen.update((o) => !o);
    if (this.isOpen()) {
      this.query.set('');
      this.selectedIndex.set(0);
      // Focus input after render
      setTimeout(() => {
        const input = document.querySelector('.spotlight__input') as HTMLInputElement;
        input?.focus();
      }, 50);
    }
  }

  close(): void {
    this.isOpen.set(false);
  }

  onSearch(event: Event): void {
    const value = (event.target as HTMLInputElement).value;
    this.query.set(value);
    this.selectedIndex.set(0);
  }

  navigateResults(direction: number): void {
    const results = this.filteredResults();
    if (results.length === 0) return;

    this.selectedIndex.update((i) => {
      const newIndex = i + direction;
      if (newIndex < 0) return results.length - 1;
      if (newIndex >= results.length) return 0;
      return newIndex;
    });
  }

  selectResult(): void {
    const results = this.filteredResults();
    const index = this.selectedIndex();
    if (results[index]) {
      this.goTo(results[index].path);
    }
  }

  goTo(path: string): void {
    this.router.navigate([path]);
    this.close();
  }
}
