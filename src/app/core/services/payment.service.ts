import { Injectable, inject, signal } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Observable, catchError, map, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

// ═══════════════════════════════════════════════════════════════════════════════
// INTERFACES
// ═══════════════════════════════════════════════════════════════════════════════

export interface CheckoutResponse {
  success: boolean;
  message?: string;
  data?: {
    checkout_url: string;
    session_id: string;
  };
  error?: string;
}

export interface VerifyLicenseResponse {
  valid: boolean;
  product?: string;
  expires?: string;
  remaining_days?: number;
  activated?: boolean;
  email_hint?: string;
  error?: string;
  code?: string;
}

export interface ActivateLicenseResponse {
  success: boolean;
  message?: string;
  expires?: string;
  remaining_days?: number;
  error?: string;
  code?: string;
}

export interface LicenseInfo {
  valid: boolean;
  product: string;
  expires: string;
  remainingDays: number;
  activated: boolean;
}

export type PaymentStatus = 'idle' | 'loading' | 'redirecting' | 'success' | 'error';
export type LicenseStatus = 'idle' | 'loading' | 'valid' | 'invalid' | 'expired' | 'error';

// ═══════════════════════════════════════════════════════════════════════════════
// SERVICE
// ═══════════════════════════════════════════════════════════════════════════════

@Injectable({ providedIn: 'root' })
export class PaymentService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  // Checkout Status
  readonly paymentStatus = signal<PaymentStatus>('idle');
  readonly paymentError = signal<string | null>(null);

  // License Status
  readonly licenseStatus = signal<LicenseStatus>('idle');
  readonly licenseInfo = signal<LicenseInfo | null>(null);
  readonly licenseError = signal<string | null>(null);

  // ═══════════════════════════════════════════════════════════════════════════
  // CHECKOUT
  // ═══════════════════════════════════════════════════════════════════════════

  /**
   * Erstellt eine Stripe Checkout Session und leitet zur Zahlungsseite weiter.
   */
  createCheckout(email?: string): Observable<CheckoutResponse> {
    this.paymentStatus.set('loading');
    this.paymentError.set(null);

    const payload = email ? { email } : {};

    return this.http.post<CheckoutResponse>(`${this.apiUrl}/checkout.php`, payload).pipe(
      map((response) => {
        if (response.success && response.data?.checkout_url) {
          this.paymentStatus.set('redirecting');
          // Zur Stripe Checkout Seite weiterleiten
          window.location.href = response.data.checkout_url;
        } else {
          this.paymentStatus.set('error');
          this.paymentError.set(response.error ?? 'Checkout konnte nicht erstellt werden.');
        }
        return response;
      }),
      catchError((error: HttpErrorResponse) => this.handleCheckoutError(error))
    );
  }

  /**
   * Initiiert den Kaufprozess - Convenience-Methode mit direkter Weiterleitung.
   */
  startPurchase(email?: string): void {
    this.createCheckout(email).subscribe({
      error: (err) => console.error('[PaymentService] Purchase failed:', err),
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LICENSE VERIFICATION
  // ═══════════════════════════════════════════════════════════════════════════

  /**
   * Verifiziert einen Lizenzschlüssel.
   */
  verifyLicense(licenseKey: string): Observable<VerifyLicenseResponse> {
    this.licenseStatus.set('loading');
    this.licenseError.set(null);
    this.licenseInfo.set(null);

    return this.http
      .post<VerifyLicenseResponse>(`${this.apiUrl}/verify.php`, {
        license_key: licenseKey.toUpperCase().trim(),
      })
      .pipe(
        map((response) => {
          if (response.valid) {
            this.licenseStatus.set('valid');
            this.licenseInfo.set({
              valid: true,
              product: response.product ?? 'pro',
              expires: response.expires ?? '',
              remainingDays: response.remaining_days ?? 0,
              activated: response.activated ?? false,
            });
          } else {
            const status: LicenseStatus = response.code === 'EXPIRED' ? 'expired' : 'invalid';
            this.licenseStatus.set(status);
            this.licenseError.set(response.error ?? 'Ungültige Lizenz');
          }
          return response;
        }),
        catchError((error: HttpErrorResponse) => this.handleLicenseError(error))
      );
  }

  /**
   * Aktiviert eine Lizenz auf einem Gerät.
   */
  activateLicense(
    licenseKey: string,
    machineId: string,
    machineName?: string
  ): Observable<ActivateLicenseResponse> {
    this.licenseStatus.set('loading');
    this.licenseError.set(null);

    return this.http
      .post<ActivateLicenseResponse>(`${this.apiUrl}/activate.php`, {
        license_key: licenseKey.toUpperCase().trim(),
        machine_id: machineId,
        machine_name: machineName,
      })
      .pipe(
        map((response) => {
          if (response.success) {
            this.licenseStatus.set('valid');
            if (this.licenseInfo()) {
              this.licenseInfo.update((info) =>
                info ? { ...info, activated: true, expires: response.expires ?? info.expires } : null
              );
            }
          } else {
            this.licenseStatus.set('error');
            this.licenseError.set(response.error ?? 'Aktivierung fehlgeschlagen');
          }
          return response;
        }),
        catchError((error: HttpErrorResponse) => this.handleLicenseError(error))
      );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  /**
   * Validiert das Format eines Lizenzschlüssels.
   */
  isValidLicenseFormat(key: string): boolean {
    return /^[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}$/.test(key.trim());
  }

  /**
   * Formatiert einen Lizenzschlüssel (fügt Bindestriche ein).
   */
  formatLicenseKey(input: string): string {
    const cleaned = input.toUpperCase().replace(/[^A-Z0-9]/g, '');
    const chunks = cleaned.match(/.{1,4}/g) || [];
    return chunks.slice(0, 4).join('-');
  }

  /**
   * Setzt alle Status zurück.
   */
  reset(): void {
    this.paymentStatus.set('idle');
    this.paymentError.set(null);
    this.licenseStatus.set('idle');
    this.licenseInfo.set(null);
    this.licenseError.set(null);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ERROR HANDLING
  // ═══════════════════════════════════════════════════════════════════════════

  private handleCheckoutError(error: HttpErrorResponse): Observable<never> {
    let message: string;

    if (error.status === 0) {
      message = 'Keine Verbindung zum Server.';
    } else if (error.status === 429) {
      message = 'Zu viele Anfragen. Bitte warten Sie einen Moment.';
    } else if (error.status === 503) {
      message = 'Zahlungsdienst vorübergehend nicht verfügbar.';
    } else {
      message = error.error?.error ?? 'Checkout fehlgeschlagen.';
    }

    this.paymentStatus.set('error');
    this.paymentError.set(message);

    console.error('[PaymentService] Checkout error:', { status: error.status, message });

    return throwError(() => new Error(message));
  }

  private handleLicenseError(error: HttpErrorResponse): Observable<never> {
    let message: string;

    if (error.status === 0) {
      message = 'Keine Verbindung zum Server.';
    } else if (error.status === 429) {
      message = 'Zu viele Anfragen.';
    } else {
      message = error.error?.error ?? 'Lizenzprüfung fehlgeschlagen.';
    }

    this.licenseStatus.set('error');
    this.licenseError.set(message);

    console.error('[PaymentService] License error:', { status: error.status, message });

    return throwError(() => new Error(message));
  }
}
