import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
  selector: 'app-imprint',
  templateUrl: './imprint.component.html',
  styleUrl: './imprint.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImprintComponent {
  readonly companyInfo = {
    name: 'Obscuras Media Agency',
    owner: 'Sascha Gebel',
    street: 'Amsdorfer Straße 27',
    city: '06317 Seegebiet Mansfelder Land',
    country: 'Deutschland',
    email: 'info@obscuras-media-agency.de',
    phone: '+49 34601 31 92 41',
    website: 'https://obscuras-media-agency.de',
  };

  readonly taxInfo = {
    vatId: 'DE460608508',
    taxNumber: '118/223/02043',
  };

  readonly responsibleContent = {
    name: 'Sascha Gebel',
    address: 'Amsdorfer Straße 27, 06317 Seegebiet Mansfelder Land, Deutschland',
  };

  readonly disputeResolution = {
    euPlatform: 'https://ec.europa.eu/consumers/odr/',
    note: 'Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.',
  };

  readonly lastUpdated = '02. März 2026';
}
