import { Routes } from '@angular/router';
import { LayoutComponent } from './core';

export const routes: Routes = [
  {
    path: '',
    component: LayoutComponent,
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./pages/home/home.component').then((m) => m.HomeComponent),
        title: 'Mailer Campaigner - E-Mail Kampagnen ohne Kompromisse',
      },
      {
        path: 'features',
        loadComponent: () =>
          import('./pages/features/features.component').then((m) => m.FeaturesComponent),
        title: 'Features - Mailer Campaigner',
      },
      {
        path: 'pricing',
        loadComponent: () =>
          import('./pages/pricing/pricing.component').then((m) => m.PricingComponent),
        title: 'Preise - Mailer Campaigner',
      },
      {
        path: 'download',
        loadComponent: () =>
          import('./pages/download/download.component').then((m) => m.DownloadComponent),
        title: 'Download & Installation - Mailer Campaigner',
      },
      {
        path: 'docs',
        loadComponent: () =>
          import('./pages/docs/docs.component').then((m) => m.DocsComponent),
        title: 'Dokumentation - Mailer Campaigner',
      },
      {
        path: 'impressum',
        loadComponent: () =>
          import('./pages/imprint/imprint.component').then((m) => m.ImprintComponent),
        title: 'Impressum - Mailer Campaigner',
      },
      {
        path: 'datenschutz',
        loadComponent: () =>
          import('./pages/privacy/privacy.component').then((m) => m.PrivacyComponent),
        title: 'Datenschutzerklärung - Mailer Campaigner',
      },
      {
        path: 'agb',
        loadComponent: () =>
          import('./pages/terms/terms.component').then((m) => m.TermsComponent),
        title: 'AGB - Mailer Campaigner',
      },
      {
        path: 'changelog',
        loadComponent: () =>
          import('./pages/changelog/changelog.component').then((m) => m.ChangelogComponent),
        title: 'Changelog - Mailer Campaigner',
      },
      {
        path: 'payment/success',
        loadComponent: () =>
          import('./pages/payment/success/success.component').then((m) => m.PaymentSuccessComponent),
        title: 'Zahlung erfolgreich - Mailer Campaigner',
      },
      {
        path: 'payment/cancel',
        loadComponent: () =>
          import('./pages/payment/cancel/cancel.component').then((m) => m.PaymentCancelComponent),
        title: 'Zahlung abgebrochen - Mailer Campaigner',
      },
      {
        path: '**',
        redirectTo: '',
      },
    ],
  },
];

