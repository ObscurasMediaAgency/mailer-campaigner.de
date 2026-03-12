import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-payment-cancel',
  imports: [RouterLink],
  template: `
    <section class="payment-result">
      <div class="container">
        <div class="payment-card payment-card--cancel">
          <div class="payment-card__icon">
            <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
          </div>

          <h1 class="payment-card__title">Zahlung abgebrochen</h1>

          <p class="payment-card__message">
            Ihr Kaufvorgang wurde abgebrochen. Es wurden keine Zahlungen vorgenommen.
          </p>

          <div class="payment-card__info">
            <h2>Was möchten Sie tun?</h2>
            <ul class="options-list">
              <li>
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                <div>
                  <strong>Erneut versuchen</strong>
                  <p>Kehren Sie zur Preisseite zurück und schließen Sie den Kauf ab.</p>
                </div>
              </li>
              <li>
                <i class="fa-solid fa-download" aria-hidden="true"></i>
                <div>
                  <strong>Kostenlos testen</strong>
                  <p>Testen Sie Mailer Campaigner 14 Tage lang unverbindlich.</p>
                </div>
              </li>
              <li>
                <i class="fa-solid fa-question-circle" aria-hidden="true"></i>
                <div>
                  <strong>Fragen?</strong>
                  <p>Kontaktieren Sie unseren Support für Hilfe.</p>
                </div>
              </li>
            </ul>
          </div>

          <div class="payment-card__actions">
            <a routerLink="/pricing" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
              Zur Preisseite
            </a>
            <a routerLink="/download" class="btn btn-outline">
              <i class="fa-solid fa-download" aria-hidden="true"></i>
              Kostenlos testen
            </a>
          </div>

          <p class="payment-card__support">
            Probleme bei der Zahlung?
            <a href="mailto:support@mailer-campaigner.de">Kontaktieren Sie uns</a>
          </p>
        </div>
      </div>
    </section>
  `,
  styleUrl: './cancel.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PaymentCancelComponent {}
