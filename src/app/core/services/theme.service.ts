import { Injectable, signal, effect } from '@angular/core';

export type Theme = 'dark' | 'light';
export type AccentColor = 'indigo' | 'emerald' | 'rose' | 'amber' | 'cyan';

interface ThemeConfig {
  theme: Theme;
  accent: AccentColor;
}

const ACCENT_COLORS: Record<AccentColor, { primary: string; secondary: string }> = {
  indigo: { primary: '#6366f1', secondary: '#a855f7' },
  emerald: { primary: '#10b981', secondary: '#06b6d4' },
  rose: { primary: '#f43f5e', secondary: '#ec4899' },
  amber: { primary: '#f59e0b', secondary: '#ef4444' },
  cyan: { primary: '#06b6d4', secondary: '#3b82f6' },
};

@Injectable({ providedIn: 'root' })
export class ThemeService {
  private readonly STORAGE_KEY = 'mailer-campaigner-theme';

  readonly theme = signal<Theme>('dark');
  readonly accent = signal<AccentColor>('indigo');

  constructor() {
    this.loadFromStorage();

    effect(() => {
      this.applyTheme(this.theme(), this.accent());
      this.saveToStorage();
    });
  }

  toggleTheme(): void {
    this.theme.update((t) => (t === 'dark' ? 'light' : 'dark'));
  }

  setTheme(theme: Theme): void {
    this.theme.set(theme);
  }

  setAccent(accent: AccentColor): void {
    this.accent.set(accent);
  }

  private loadFromStorage(): void {
    try {
      const stored = localStorage.getItem(this.STORAGE_KEY);
      if (stored) {
        const config: ThemeConfig = JSON.parse(stored);
        this.theme.set(config.theme);
        this.accent.set(config.accent);
      } else {
        // Check system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        this.theme.set(prefersDark ? 'dark' : 'light');
      }
    } catch {
      // Use defaults
    }
    // Apply immediately
    this.applyTheme(this.theme(), this.accent());
  }

  private saveToStorage(): void {
    const config: ThemeConfig = {
      theme: this.theme(),
      accent: this.accent(),
    };
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(config));
  }

  private applyTheme(theme: Theme, accent: AccentColor): void {
    const root = document.documentElement;
    const accentColors = ACCENT_COLORS[accent];

    // Set theme attribute
    root.setAttribute('data-theme', theme);

    // Set accent colors
    root.style.setProperty('--color-accent-primary', accentColors.primary);
    root.style.setProperty('--color-accent-secondary', accentColors.secondary);

    // Update gradient
    root.style.setProperty(
      '--gradient-primary',
      `linear-gradient(135deg, ${accentColors.primary}, ${accentColors.secondary})`
    );
    root.style.setProperty(
      '--gradient-glow',
      `radial-gradient(circle, ${accentColors.primary}20 0%, transparent 70%)`
    );
  }
}
