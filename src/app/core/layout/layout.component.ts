import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent } from '../header/header.component';
import { FooterComponent } from '../footer/footer.component';

@Component({
  selector: 'app-layout',
  imports: [RouterOutlet, HeaderComponent, FooterComponent],
  template: `
    <div class="layout">
      <app-header />
      <main class="layout__main">
        <router-outlet />
      </main>
      <app-footer />
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
