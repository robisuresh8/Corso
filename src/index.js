import './styles.css';

(function () {
  function init() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');

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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
