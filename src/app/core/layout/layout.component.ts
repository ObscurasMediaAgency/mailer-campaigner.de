import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent } from '../header/header.component';
import { FooterComponent } from '../footer/footer.component';
import { ScrollUtilitiesComponent } from '../scroll-utilities/scroll-utilities.component';
import { CookieBannerComponent } from '../cookie-banner/cookie-banner.component';
import { SearchSpotlightComponent } from '../search-spotlight/search-spotlight.component';
import { ThemePickerComponent } from '../theme-picker/theme-picker.component';
import { KeyboardShortcutsComponent } from '../keyboard-shortcuts/keyboard-shortcuts.component';

@Component({
  selector: 'app-layout',
  imports: [
    RouterOutlet,
    HeaderComponent,
    FooterComponent,
    ScrollUtilitiesComponent,
    CookieBannerComponent,
    SearchSpotlightComponent,
    ThemePickerComponent,
    KeyboardShortcutsComponent,
  ],
  template: `
    <div class="layout">
      <app-header />
      <app-scroll-utilities />
      <app-search-spotlight />
      <app-keyboard-shortcuts />
      <app-theme-picker />
      <main class="layout__main">
        <router-outlet />
      </main>
      <app-footer />
      <app-cookie-banner />
    </div>
  `,
  styles: `
    .layout {
      display: flex;
      flex-direction: column;
      min-height: 100vh;

      &__main {
        flex: 1;
        padding-top: 72px; // Header height
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LayoutComponent {}
