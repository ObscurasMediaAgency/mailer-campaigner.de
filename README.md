# 📧 Mailer Campaigner

## Professionelle E-Mail-Kampagnen-Management Website

[![Angular](https://img.shields.io/badge/Angular-21-DD0031?style=for-the-badge&logo=angular&logoColor=white)](https://angular.dev/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.7-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![SCSS](https://img.shields.io/badge/SCSS-Styling-CC6699?style=for-the-badge&logo=sass&logoColor=white)](https://sass-lang.com/)
[![License](https://img.shields.io/badge/License-Proprietary-orange?style=for-the-badge)](LICENSE)

[Demo ansehen](https://mailer-campaigner.de) · [Features](#-features) · [Installation](#-installation) · [Struktur](#-projektstruktur)

![Mailer Campaigner Logo](https://github.com/ObscurasMediaAgency/mailer-campaigner.de/blob/main/public/assets/logo.png)

---

## 🎯 Über das Projekt

Marketing-Website für **Mailer Campaigner** – ein lokales E-Mail-Kampagnen-Tool für professionelle B2B-Kommunikation. Die Website präsentiert Features, Preise und Dokumentation der Software.

### ✨ Highlights

- 🌓 **Light/Dark Mode** mit persistenter Theme-Einstellung
- 🎨 **5 Akzentfarben** wählbar (Indigo, Emerald, Rose, Amber, Cyan)
- 🔍 **Spotlight-Suche** (Cmd/Ctrl + K)
- ⌨️ **Keyboard Shortcuts** (? für Übersicht)
- 📜 **Smooth Page Transitions** (View Transitions API)
- 🍪 **DSGVO-konformer Cookie-Banner**
- 📱 **Vollständig responsive** für alle Geräte

---

## 🚀 Features

| Seite | Route | Beschreibung |
| ----- | ----- | ------------ |
| **Home** | `/` | Landing Page mit Hero, Features & CTA |
| **Features** | `/features` | Detaillierte Funktionsübersicht |
| **Preise** | `/pricing` | Lizenzmodell (€129/Jahr) |
| **Download** | `/download` | Installation & Systemanforderungen |
| **Dokumentation** | `/docs` | Quickstart, CLI, Templates |
| **Changelog** | `/changelog` | Versionshistorie & Updates |
| **Impressum** | `/impressum` | Anbieterkennung gemäß TMG |
| **Datenschutz** | `/datenschutz` | DSGVO-Datenschutzerklärung |
| **AGB** | `/agb` | Allgemeine Geschäftsbedingungen |

---

## 🛠 Tech Stack

- **Framework:** Angular 21 (Standalone Components)
- **Sprache:** TypeScript 5.7 (Strict Mode)
- **Styling:** SCSS mit CSS Custom Properties
- **State:** Angular Signals
- **Fonts:** Inter & JetBrains Mono
- **Icons:** Font Awesome 6.5

---

## 📦 Installation

```bash
# Repository klonen
git clone https://github.com/obscuras-media-agency/mailer-campaigner.de.git
cd mailer-campaigner.de

# Dependencies installieren
npm install

# Development Server starten
ng serve
```

Die Anwendung läuft dann unter `http://localhost:4200/`

---

## 🏗 Projektstruktur

```bash
src/app/
├── core/                    # Kern-Komponenten
│   ├── header/              # Navigation & Theme Toggle
│   ├── footer/              # Footer & Newsletter
│   ├── layout/              # Page Layout Wrapper
│   ├── cookie-banner/       # DSGVO Cookie Consent
│   ├── search-spotlight/    # Cmd+K Suche
│   ├── scroll-utilities/    # Progress Bar & Scroll-to-Top
│   ├── theme-picker/        # Akzentfarben-Auswahl
│   ├── keyboard-shortcuts/  # Shortcuts Overlay
│   └── services/            # ThemeService
│
├── pages/                   # Lazy-loaded Seiten
│   ├── home/
│   ├── features/
│   ├── pricing/
│   ├── download/
│   ├── docs/
│   ├── changelog/
│   ├── imprint/
│   ├── privacy/
│   └── terms/
│
├── shared/                  # Geteilte Ressourcen
│   └── styles/              # Wiederverwendbare SCSS
│
├── app.routes.ts            # Routing-Konfiguration
├── app.config.ts            # App-Konfiguration
└── app.ts                   # Root Component
```

---

## 🧪 Scripts

| Command | Beschreibung |
| ------- | ------------ |
| `ng serve` | Development Server (Port 4200) |
| `ng build` | Production Build nach `dist/` |
| `ng build --configuration=production` | Optimierter Production Build |
| `ng test` | Unit Tests mit Vitest |
| `ng lint` | Code Linting |

---

## 📊 Build-Größen

```text
Initial:   ~75 kB (gzipped)
Lazy:      ~30 kB pro Page (gzipped)
Styles:    ~1.8 kB (gzipped)
```

---

## 🎨 Theming

Das Theme-System basiert auf CSS Custom Properties:

```scss
// Dark Theme (Standard)
--bg: #0a0a0f;
--text: #f5f5f7;
--accent: #6366f1;

// Light Theme
[data-theme="light"] {
  --bg: #fafafa;
  --text: #1a1a1a;
}
```

Akzentfarben werden als `--accent` Variable gesetzt und persistent in `localStorage` gespeichert.

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Aktion |
| -------- | ------ |
| `Cmd/Ctrl + K` | Spotlight-Suche öffnen |
| `?` | Shortcuts-Übersicht |
| `ESC` | Dialoge schließen |

---

## 📄 Lizenz

Copyright © 2026 Obscuras Media Agency. Alle Rechte vorbehalten.

---

### Made with ❤️ by Obscuras Media Agency

[Website](https://obscuras-media-agency.de) · [GitHub](https://github.com/obscuras-media-agency) · [Telegram](https://t.me/obscuras_media_agency)
