import { Injectable, inject, signal } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Observable, catchError, map, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface NewsletterResponse {
  success: boolean;
  message: string;
  data?: {
    alreadySubscribed?: boolean;
    pendingConfirmation?: boolean;
  };
  error?: string;
  requestId?: string;
}

export interface NewsletterSubscribeRequest {
  email: string;
  source?: string;
}

export interface NewsletterUnsubscribeRequest {
  email: string;
}

export type SubscriptionStatus = 'idle' | 'loading' | 'success' | 'error';

@Injectable({ providedIn: 'root' })
export class NewsletterService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl + '/newsletter.php';

  readonly status = signal<SubscriptionStatus>('idle');
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  /**
   * Meldet eine E-Mail-Adresse für den Newsletter an (Double-Opt-In).
   * Sendet eine Bestätigungs-E-Mail an den Nutzer.
   */
  subscribe(email: string, source = 'website'): Observable<NewsletterResponse> {
    this.status.set('loading');
    this.errorMessage.set(null);
    this.successMessage.set(null);

    const payload: NewsletterSubscribeRequest = { email, source };

    return this.http
      .post<NewsletterResponse>(`${this.apiUrl}?action=subscribe`, payload)
      .pipe(
        map((response) => {
          if (response.success) {
            this.status.set('success');
            this.successMessage.set(response.message);
          } else {
            this.status.set('error');
            this.errorMessage.set(response.error ?? 'Ein Fehler ist aufgetreten.');
          }
          return response;
        }),
        catchError((error: HttpErrorResponse) => this.handleError(error))
      );
  }

  /**
   * Meldet eine E-Mail-Adresse vom Newsletter ab.
   */
  unsubscribe(email: string): Observable<NewsletterResponse> {
    this.status.set('loading');
    this.errorMessage.set(null);

    const payload: NewsletterUnsubscribeRequest = { email };

    return this.http
      .post<NewsletterResponse>(`${this.apiUrl}?action=unsubscribe`, payload)
      .pipe(
        map((response) => {
          if (response.success) {
            this.status.set('success');
            this.successMessage.set(response.message);
          } else {
            this.status.set('error');
            this.errorMessage.set(response.error ?? 'Ein Fehler ist aufgetreten.');
          }
          return response;
        }),
        catchError((error: HttpErrorResponse) => this.handleError(error))
      );
  }

  /**
   * Setzt den Service-Status zurück.
   */
  reset(): void {
    this.status.set('idle');
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  /**
   * Behandelt HTTP-Fehler und gibt eine benutzerfreundliche Fehlermeldung zurück.
   */
  private handleError(error: HttpErrorResponse): Observable<never> {
    let message: string;

    if (error.status === 0) {
      message = 'Keine Verbindung zum Server. Bitte prüfen Sie Ihre Internetverbindung.';
    } else if (error.status === 429) {
      message = 'Zu viele Anfragen. Bitte versuchen Sie es später erneut.';
    } else if (error.error?.message) {
      message = error.error.message;
    } else if (error.error?.error) {
      message = error.error.error;
    } else {
      message = 'Ein unerwarteter Fehler ist aufgetreten.';
    }

    this.status.set('error');
    this.errorMessage.set(message);

    console.error('[NewsletterService] Error:', {
      status: error.status,
      message,
      error: error.error,
    });

    return throwError(() => new Error(message));
  }
}
