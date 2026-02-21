import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { NgOptimizedImage } from '@angular/common';
import { ThemeService } from '../services/theme.service';

interface NavItem {
  label: string;
  path: string;
  icon: string;
}

@Component({
  selector: 'app-header',
  imports: [RouterLink, RouterLinkActive, NgOptimizedImage],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class HeaderComponent {
  private readonly themeService = inject(ThemeService);
  
  readonly mobileMenuOpen = signal(false);
  readonly theme = this.themeService.theme;
  
  readonly navItems: NavItem[] = [
    { label: 'Startseite', path: '/', icon: 'fa-house' },
    { label: 'Features', path: '/features', icon: 'fa-star' },
    { label: 'Preise', path: '/pricing', icon: 'fa-tag' },
    { label: 'Download', path: '/download', icon: 'fa-download' },
    { label: 'Dokumentation', path: '/docs', icon: 'fa-book' },
  ];

  toggleMobileMenu(): void {
    this.mobileMenuOpen.update(open => !open);
  }

  closeMobileMenu(): void {
    this.mobileMenuOpen.set(false);
  }

  toggleTheme(): void {
    this.themeService.toggleTheme();
  }
}
