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
        path: '**',
        redirectTo: '',
      },
    ],
  },
];

