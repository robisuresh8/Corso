import './styles.css';

(function () {
  function init() {
    document.documentElement.classList.add('js');

    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');
    const header = document.querySelector('.header');
    const heroGradient = document.querySelector('.hero-gradient');
    

    if (menuToggle && nav) {
      menuToggle.addEventListener('click', function () {
        nav.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', nav.classList.contains('is-open'));
      });
    }

    // Close mobile nav when clicking a link
    document.querySelectorAll('.nav a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav && nav.classList.remove('is-open');
        menuToggle && menuToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // Scroll-driven UI polish (smooth + lightweight)
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let rafId = 0;

    // Scroll progress (top bar)
    let progressBar = document.querySelector('.scroll-progress__bar');
    if (!progressBar && !prefersReducedMotion) {
      const progress = document.createElement('div');
      progress.className = 'scroll-progress';
      const bar = document.createElement('div');
      bar.className = 'scroll-progress__bar';
      progress.appendChild(bar);
      document.body.appendChild(progress);
      progressBar = bar;
    }

    function onScroll() {
      if (rafId) return;
      rafId = window.requestAnimationFrame(function () {
        rafId = 0;
        const y = window.scrollY || window.pageYOffset || 0;
        const max = Math.max(1, (document.documentElement.scrollHeight || 1) - window.innerHeight);
        const p = Math.max(0, Math.min(1, y / max));

        if (header) {
          header.classList.toggle('is-scrolled', y > 8);
        }

        if (progressBar) {
          progressBar.style.width = (p * 100).toFixed(2) + '%';
        }

        if (prefersReducedMotion) return;

        // Subtle parallax + fade for hero glow
        if (heroGradient) {
          const offset = Math.min(70, Math.max(0, y * 0.16));
          const opacity = Math.max(0.18, 1 - y / 800);
          heroGradient.style.transform = 'translate3d(-50%, ' + offset.toFixed(1) + 'px, 0)';
          heroGradient.style.opacity = String(opacity.toFixed(3));
        }
      });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Reveal-on-scroll (IntersectionObserver)
    const revealTargets = [
      '.section-title',
      '.certificate-card',
      '.feature-card',
      '.learner-card',
      '.featured-logo',
      '.cta-inner',
      '.footer',
    ];

    const elements = [];
    revealTargets.forEach(function (selector) {
      document.querySelectorAll(selector).forEach(function (el) {
        if (!el.classList.contains('reveal')) elements.push(el);
      });
    });

    if (!prefersReducedMotion) {
      elements.forEach(function (el, idx) {
        el.classList.add('reveal');
        // gentle stagger (caps so deep grids don't get huge delays)
        const delay = Math.min(240, idx * 25);
        el.style.setProperty('--reveal-delay', delay + 'ms');
        if (el.classList.contains('cta-inner') || el.classList.contains('certificate-card')) {
          el.setAttribute('data-reveal', 'scale');
        }
      });

      if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(
          function (entries) {
            entries.forEach(function (entry) {
              if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
              }
            });
          },
          { root: null, threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
        );

        elements.forEach(function (el) {
          io.observe(el);
        });
      } else {
        // Fallback: show everything if IO isn't supported
        elements.forEach(function (el) {
          el.classList.add('is-visible');
        });
      }
    }

    // Pointer-reactive “spotlight” on buttons/cards + subtle tilt
    function applyPointerVars(el, ev) {
      const rect = el.getBoundingClientRect();
      const x = ((ev.clientX - rect.left) / rect.width) * 100;
      const y = ((ev.clientY - rect.top) / rect.height) * 100;
      el.style.setProperty('--mx', x.toFixed(2) + '%');
      el.style.setProperty('--my', y.toFixed(2) + '%');
    }

    function enableTilt(el) {
      if (prefersReducedMotion) return;
      let tiltRaf = 0;

      function onMove(ev) {
        if (tiltRaf) return;
        tiltRaf = window.requestAnimationFrame(function () {
          tiltRaf = 0;
          const rect = el.getBoundingClientRect();
          const px = (ev.clientX - rect.left) / rect.width - 0.5;
          const py = (ev.clientY - rect.top) / rect.height - 0.5;
          const rx = (-py * 5).toFixed(2);
          const ry = (px * 6).toFixed(2);
          el.style.transform = 'translateY(-2px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
        });
      }

      function onLeave() {
        el.style.transform = '';
      }

      el.addEventListener('pointermove', function (ev) {
        applyPointerVars(el, ev);
        onMove(ev);
      });
      el.addEventListener('pointerleave', onLeave);
    }

    // Buttons: spotlight follows cursor
    document.querySelectorAll('.btn').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        applyPointerVars(el, ev);
      });
    });

    // Cards: spotlight + tilt
    document.querySelectorAll('.feature-card, .learner-card').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        applyPointerVars(el, ev);
      });
      enableTilt(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
