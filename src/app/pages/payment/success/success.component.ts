import { ChangeDetectionStrategy, Component, inject, OnInit, signal, computed } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { PaymentService } from '../../../core/services/payment.service';

@Component({
  selector: 'app-payment-success',
  imports: [RouterLink],
  template: `
    <section class="payment-result">
      <div class="container">
        <div class="payment-card payment-card--success">
          <div class="payment-card__icon">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
          </div>

          <h1 class="payment-card__title">Zahlung erfolgreich!</h1>

          <p class="payment-card__message">
            Vielen Dank für Ihren Kauf von Mailer Campaigner Pro.
          </p>

          @if (licenseKey()) {
            <div class="license-box">
              <span class="license-box__label">Ihr Lizenzschlüssel:</span>
              <code class="license-box__key">{{ licenseKey() }}</code>
              <button class="license-box__copy" (click)="copyLicense()" type="button">
                @if (copied()) {
                  <i class="fa-solid fa-check" aria-hidden="true"></i>
                  Kopiert
                } @else {
                  <i class="fa-solid fa-copy" aria-hidden="true"></i>
                  Kopieren
                }
              </button>
            </div>
          } @else {
            <div class="license-box license-box--pending">
              <i class="fa-solid fa-envelope" aria-hidden="true"></i>
              <p>
                Ihr Lizenzschlüssel wird in Kürze an <strong>{{ email() || 'Ihre E-Mail-Adresse' }}</strong> gesendet.
              </p>
            </div>
          }

          <div class="payment-card__info">
            <h2>Nächste Schritte</h2>
            <ol>
              <li>
                <i class="fa-solid fa-download" aria-hidden="true"></i>
                <span>Laden Sie die Software herunter</span>
              </li>
              <li>
                <i class="fa-solid fa-key" aria-hidden="true"></i>
                <span>Geben Sie Ihren Lizenzschlüssel ein</span>
              </li>
              <li>
                <i class="fa-solid fa-rocket" aria-hidden="true"></i>
                <span>Starten Sie mit E-Mail-Marketing!</span>
              </li>
            </ol>
          </div>

          <div class="payment-card__actions">
            <a routerLink="/download" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-download" aria-hidden="true"></i>
              Jetzt herunterladen
            </a>
            <a routerLink="/" class="btn btn-outline">
              Zur Startseite
            </a>
          </div>

          <p class="payment-card__support">
            Fragen? Kontaktieren Sie uns unter
            <a href="mailto:support@mailer-campaigner.de">support&#64;mailer-campaigner.de</a>
          </p>
        </div>
      </div>
    </section>
  `,
  styleUrl: './success.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PaymentSuccessComponent implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly paymentService = inject(PaymentService);

  readonly sessionId = signal<string | null>(null);
  readonly licenseKey = signal<string | null>(null);
  readonly email = signal<string | null>(null);
  readonly copied = signal(false);

  ngOnInit(): void {
    // Session ID aus URL-Parameter auslesen
    this.route.queryParams.subscribe((params) => {
      this.sessionId.set(params['session_id'] || null);
      this.email.set(params['email'] || null);

      // Falls Lizenzschlüssel direkt übergeben wurde (optional)
      if (params['license']) {
        this.licenseKey.set(params['license']);
      }
    });
  }

  copyLicense(): void {
    const key = this.licenseKey();
    if (key) {
      navigator.clipboard.writeText(key).then(() => {
        this.copied.set(true);
        setTimeout(() => this.copied.set(false), 2000);
      });
    }
  }
}
