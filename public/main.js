(function () {
  function init() {
    document.documentElement.classList.add('js');
    var menuToggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav');
    var header = document.querySelector('.header');
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var rafId = 0;

    if (menuToggle && nav) {
      menuToggle.addEventListener('click', function () {
        nav.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', nav.classList.contains('is-open'));
      });
    }
    document.querySelectorAll('.nav a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (nav) nav.classList.remove('is-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
      });
    });

    var progressBar = document.querySelector('.scroll-progress__bar');
    if (!progressBar && !prefersReducedMotion) {
      var progress = document.createElement('div');
      progress.className = 'scroll-progress';
      var bar = document.createElement('div');
      bar.className = 'scroll-progress__bar';
      progress.appendChild(bar);
      document.body.appendChild(progress);
      progressBar = bar;
    }

    function onScroll() {
      if (rafId) return;
      rafId = requestAnimationFrame(function () {
        rafId = 0;
        var y = window.scrollY || window.pageYOffset || 0;
        var max = Math.max(1, (document.documentElement.scrollHeight || 1) - window.innerHeight);
        var p = Math.max(0, Math.min(1, y / max));
        if (header) header.classList.toggle('is-scrolled', y > 8);
        if (progressBar) progressBar.style.width = (p * 100).toFixed(2) + '%';
      });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var revealTargets = ['.section-title', '.feature-card', '.course-card', '.blog-cat-card', '.pricing-feature', '.content-block', '.about-stat', '.about-card', '.about-intro-card', '.footer'];
    var elements = [];
    revealTargets.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        if (!el.classList.contains('reveal')) elements.push(el);
      });
    });
    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
      elements.forEach(function (el, i) {
        el.classList.add('reveal');
        el.style.setProperty('--reveal-delay', Math.min(200, i * 20) + 'ms');
      });
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { root: null, threshold: 0.1, rootMargin: '0px 0px -5% 0px' });
      elements.forEach(function (el) { io.observe(el); });
    } else {
      elements.forEach(function (el) { el.classList.add('reveal', 'is-visible'); });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
