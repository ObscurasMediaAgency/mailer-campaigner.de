import { ChangeDetectionStrategy, Component, signal, inject, PLATFORM_ID, OnInit, OnDestroy } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-scroll-utilities',
  template: `
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" [style.width.%]="scrollProgress()" aria-hidden="true"></div>

    <!-- Scroll to Top Button -->
    @if (showScrollTop()) {
      <button
        type="button"
        class="scroll-top"
        aria-label="Nach oben scrollen"
        (click)="scrollToTop()"
      >
        <svg class="scroll-top__progress" viewBox="0 0 100 100">
          <circle
            class="scroll-top__track"
            cx="50"
            cy="50"
            r="45"
            fill="none"
            stroke-width="4"
          />
          <circle
            class="scroll-top__indicator"
            cx="50"
            cy="50"
            r="45"
            fill="none"
            stroke-width="4"
            [style.stroke-dashoffset]="strokeDashoffset()"
          />
        </svg>
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
      </button>
    }
  `,
  styles: `
    .scroll-progress {
      position: fixed;
      top: 72px;
      left: 0;
      height: 3px;
      background: var(--gradient-primary);
      z-index: 99;
      transition: width 50ms linear;
    }

    .scroll-top {
      position: fixed;
      bottom: 2rem;
      right: 2rem;
      width: 52px;
      height: 52px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--color-bg-secondary);
      border: 1px solid var(--color-border);
      border-radius: 50%;
      color: var(--color-text-primary);
      cursor: pointer;
      z-index: 90;
      transition: all var(--transition-base);
      box-shadow: var(--shadow-lg);

      &:hover {
        background: var(--color-accent-primary);
        border-color: var(--color-accent-primary);
        color: white;
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl), 0 0 20px rgba(99, 102, 241, 0.4);
      }

      i {
        font-size: 1.1rem;
        z-index: 1;
      }

      &__progress {
        position: absolute;
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
      }

      &__track {
        stroke: var(--color-border);
      }

      &__indicator {
        stroke: var(--color-accent-primary);
        stroke-dasharray: 283;
        stroke-linecap: round;
        transition: stroke-dashoffset 50ms linear;
      }
    }

    @media (max-width: 576px) {
      .scroll-top {
        bottom: 1rem;
        right: 1rem;
        width: 44px;
        height: 44px;
      }
    }
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ScrollUtilitiesComponent implements OnInit, OnDestroy {
  private readonly platformId = inject(PLATFORM_ID);
  private scrollListener: (() => void) | null = null;

  readonly scrollProgress = signal(0);
  readonly showScrollTop = signal(false);

  readonly strokeDashoffset = () => {
    const circumference = 283; // 2 * PI * 45
    return circumference - (this.scrollProgress() / 100) * circumference;
  };

  ngOnInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      this.scrollListener = () => this.onScroll();
      window.addEventListener('scroll', this.scrollListener, { passive: true });
    }
  }

  ngOnDestroy(): void {
    if (this.scrollListener && isPlatformBrowser(this.platformId)) {
      window.removeEventListener('scroll', this.scrollListener);
    }
  }

  private onScroll(): void {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

    this.scrollProgress.set(Math.min(100, Math.max(0, progress)));
    this.showScrollTop.set(scrollTop > 300);
  }

  scrollToTop(): void {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
