/**
 * =============================================================================
 * CORSO E-LEARNING — main.js
 * =============================================================================
 *
 * Pages covered by this file:
 *   1.  index.php         — Home / Landing page
 *   2.  login.php         — Admin login (email + password)
 *   3.  user_login.php    — Student normal login
 *   4.  temp_user_login.php — Student first-time / temp-password login
 *   5.  dashboard.php     — Student dashboard (certs, calendar, stats)
 *   6.  admin.php         — Admin panel
 *   7.  super_admin/dashboard.php — Super admin panel
 *   8.  my_certificates.php — Student certificate list
 *   9.  verify.php        — Public certificate verification
 *   10. reset_password.php — Password reset page
 *
 * Shared utilities (used across all pages):
 *   - corsoApi            — Fetch wrapper (GET / POST with JWT bearer token)
 *   - loadRazorpayScript  — Lazy-load Razorpay checkout SDK
 *   - escapeHtml          — XSS-safe HTML escaping
 *   - showPostPaymentModal — Post-payment success/failure modal
 *   - showRegistrationModal — Idle-triggered registration popup (30s timer)
 *   - openQuizModal       — Full quiz flow with Razorpay payment + activation
 *   - updateAuthNav       — Show/hide nav links based on login state
 *
 * API base URL is injected by PHP in each view:
 *   <script>window.CORSO_API_BASE = '<?= base_url('api') ?>';</script>
 * =============================================================================
 */

(function () {

  // ===========================================================================
  // SHARED: corsoApi — lightweight fetch wrapper
  // Used by: ALL pages
  // Reads JWT from localStorage key 'apiToken'.
  // window.CORSO_API_BASE is set by the PHP view before this script loads.
  // ===========================================================================
  if (!window.corsoApi) {
    var base = window.CORSO_API_BASE || '';
    window.corsoApi = {
      base: base,

      /** Get JWT from localStorage */
      token: function () {
        try { return localStorage.getItem('apiToken') || ''; } catch (e) { return ''; }
      },

      /** GET request with optional Authorization header */
      get: function (path) {
        if (!base) return Promise.reject(new Error('noapi'));
        var h = { 'Accept': 'application/json' };
        var t = this.token();
        if (t) h['Authorization'] = 'Bearer ' + t;
        return fetch(base + path, { method: 'GET', headers: h });
      },

      /** POST request with JSON body and optional Authorization header */
      post: function (path, data) {
        if (!base) return Promise.reject(new Error('noapi'));
        var h = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        var t = this.token();
        if (t) h['Authorization'] = 'Bearer ' + t;
        return fetch(base + path, { method: 'POST', headers: h, body: JSON.stringify(data || {}) });
      }
    };
  }

  // ===========================================================================
  // SHARED: loadRazorpayScript
  // Lazy-loads Razorpay checkout.js from CDN.
  // Used by: index.php (quiz modal → payment)
  // ===========================================================================
  function loadRazorpayScript(callback) {
    if (window.Razorpay) { callback(); return; }
    var s = document.createElement('script');
    s.src = 'https://checkout.razorpay.com/v1/checkout.js';
    s.onload = function () { callback(); };
    s.onerror = function () { alert('Could not load Razorpay. Check your network connection.'); };
    document.head.appendChild(s);
  }

  // ===========================================================================
  // SHARED: escapeHtml
  // Sanitize user-supplied strings before inserting into innerHTML.
  // ===========================================================================
  function escapeHtml(s) {
    if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ===========================================================================
  // SHARED: showPostPaymentModal
  // Shown after Razorpay payment succeeds or email delivery fails.
  // Used by: index.php (after quiz + payment flow)
  //
  // opts.emailSent      {boolean} — true if backend sent credentials email
  // opts.email          {string}  — user email address
  // opts.tempPassword   {string}  — temp password (only if email failed)
  // opts.emailError     {string}  — error code from backend (optional)
  // opts.tempLoginUrl   {string}  — first-time login URL
  // opts.normalLoginUrl {string}  — normal login URL
  // ===========================================================================
  function showPostPaymentModal(opts) {
    opts = opts || {};
    var existing = document.getElementById('pay-success-overlay');
    if (existing) existing.remove();

    // --- Overlay backdrop ---
    var overlay = document.createElement('div');
    overlay.id = 'pay-success-overlay';
    overlay.style.position = 'fixed';
    overlay.style.inset = '0';
    overlay.style.background = 'rgba(0,0,0,0.65)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '100000';

    // --- Modal card ---
    var modal = document.createElement('div');
    modal.style.width = 'min(460px, calc(100vw - 24px))';
    modal.style.background = '#0f1419';
    modal.style.border = '1px solid rgba(255,255,255,0.08)';
    modal.style.borderRadius = '16px';
    modal.style.padding = '22px';
    modal.style.color = '#e2e8f0';

    var title = document.createElement('div');
    title.style.fontWeight = '800';
    title.style.fontSize = '1.15rem';
    title.style.marginBottom = '12px';
    title.textContent = 'Payment successful';

    var content = document.createElement('div');
    content.style.fontSize = '0.95rem';
    content.style.lineHeight = '1.55';
    content.style.color = 'rgba(255,255,255,0.88)';

    if (opts.emailSent) {
      // Email sent successfully — tell user to check inbox
      var p1 = document.createElement('p');
      p1.style.margin = '0 0 10px';
      p1.innerHTML = 'We sent your login details to <strong>' + escapeHtml(opts.email) + '</strong>.';
      var p2 = document.createElement('p');
      p2.style.margin = '0';
      p2.style.color = 'rgba(255,255,255,0.72)';
      p2.textContent = 'Check your inbox, Promotions tab, and spam. If nothing arrives, the "From" address must be verified in Brevo (Senders) and match email.fromEmail in .env.';
      content.appendChild(p1);
      content.appendChild(p2);
    } else {
      // Email failed — show credentials directly on screen
      var errNote = opts.emailError ? ' (' + escapeHtml(opts.emailError) + ')' : '';
      var p0 = document.createElement('p');
      p0.style.margin = '0 0 10px';
      p0.textContent = 'We could not send email' + errNote + '. Save these details in a safe place:';
      content.appendChild(p0);

      var ul = document.createElement('ul');
      ul.style.margin = '8px 0 0 18px';
      ul.style.color = 'rgba(255,255,255,0.78)';
      var li1 = document.createElement('li');
      li1.innerHTML = 'Email: <strong>' + escapeHtml(opts.email) + '</strong>';
      ul.appendChild(li1);
      if (opts.tempPassword) {
        var li2 = document.createElement('li');
        li2.innerHTML = 'Temporary password: <strong>' + escapeHtml(opts.tempPassword) + '</strong>';
        ul.appendChild(li2);
      }
      content.appendChild(ul);

      // Temp login link (first-time login page)
      if (opts.tempLoginUrl) {
        var pl = document.createElement('p');
        pl.style.margin = '12px 0 4px';
        pl.style.fontSize = '0.85rem';
        pl.style.color = 'rgba(255,255,255,0.65)';
        pl.textContent = 'Sign in (main login — use email + temporary password from email):';
        content.appendChild(pl);
        var a1 = document.createElement('a');
        a1.href = opts.tempLoginUrl;
        a1.target = '_blank';
        a1.rel = 'noopener';
        a1.style.color = '#7dd3fc';
        a1.style.wordBreak = 'break-all';
        a1.textContent = opts.tempLoginUrl;
        content.appendChild(a1);
      }

      // Normal login link (after password changed)
      if (opts.normalLoginUrl) {
        var pn = document.createElement('p');
        pn.style.margin = '12px 0 4px';
        pn.style.fontSize = '0.85rem';
        pn.style.color = 'rgba(255,255,255,0.65)';
        pn.textContent = 'Normal sign-in (after you set your password):';
        content.appendChild(pn);
        var a2 = document.createElement('a');
        a2.href = opts.normalLoginUrl;
        a2.target = '_blank';
        a2.rel = 'noopener';
        a2.style.color = '#7dd3fc';
        a2.style.wordBreak = 'break-all';
        a2.textContent = opts.normalLoginUrl;
        content.appendChild(a2);
      }

      var hint = document.createElement('p');
      hint.style.margin = '14px 0 0';
      hint.style.fontSize = '0.85rem';
      hint.style.color = 'rgba(255,255,255,0.55)';
      hint.textContent = 'For production, configure SMTP and email.fromEmail in .env.';
      content.appendChild(hint);
    }

    // Close button
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-primary';
    btn.style.width = '100%';
    btn.style.marginTop = '18px';
    btn.textContent = 'Continue';
    btn.addEventListener('click', function () { overlay.remove(); });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) overlay.remove(); });

    modal.appendChild(title);
    modal.appendChild(content);
    modal.appendChild(btn);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
  }

  // ===========================================================================
  // MAIN init() — runs on DOMContentLoaded
  // ===========================================================================
  function init() {
    document.documentElement.classList.add('js');
    document.documentElement.setAttribute('data-theme', 'dark');

    var menuToggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav');
    var header = document.querySelector('.header');
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var rafId = 0;

    var isHome = !!document.querySelector('.hero');

    // =========================================================================
    // PAGE: index.php — Registration popup (shown after 30s of idle)
    // Asks for name / email / phone → calls POST /api/auth/pre-register
    // Stores activation_token in localStorage for payment step.
    // =========================================================================
    var registrationPromptTimer = null;
    var registrationPromptShown = false;

    /** Check if user is already logged in (sessionUser in localStorage) */
    function isLoggedInClientSide() {
      try {
        var u = JSON.parse(localStorage.getItem('sessionUser') || 'null');
        return !!(u && u.email);
      } catch (e) { return false; }
    }

    /** Check if user has completed pre-registration but not yet paid */
    function hasPendingActivation() {
      try { return !!localStorage.getItem('pendingActivationToken'); } catch (e) { return false; }
    }

    /** Show the registration popup modal */
    function showRegistrationModal() {
      if (registrationPromptShown) return;
      if (isLoggedInClientSide()) return;
      if (hasPendingActivation()) return;

      registrationPromptShown = true;

      var existing = document.getElementById('reg-prompt-overlay');
      if (existing) existing.remove();

      var overlay = document.createElement('div');
      overlay.id = 'reg-prompt-overlay';
      overlay.style.position = 'fixed';
      overlay.style.inset = '0';
      overlay.style.background = 'rgba(0,0,0,0.65)';
      overlay.style.display = 'flex';
      overlay.style.alignItems = 'center';
      overlay.style.justifyContent = 'center';
      overlay.style.zIndex = '99999';

      var modal = document.createElement('div');
      modal.style.width = 'min(560px, calc(100vw - 24px))';
      modal.style.background = '#0f1419';
      modal.style.border = '1px solid rgba(255,255,255,0.08)';
      modal.style.borderRadius = '16px';
      modal.style.padding = '18px';
      modal.style.color = '#e2e8f0';

      modal.innerHTML = [
        '<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px;">',
        '<div><div style="font-weight:800;font-size:1.1rem;">Unlock your dashboard</div>',
        '<div style="color:rgba(255,255,255,0.7);font-size:.95rem;margin-top:4px;">Enter your details and pay to activate your account.</div></div>',
        '<button type="button" id="reg-prompt-close" style="background:transparent;border:0;color:#e2e8f0;font-size:20px;cursor:pointer;line-height:1;">&times;</button>',
        '</div>',
        '<form id="reg-prompt-form" onsubmit="return false;">',
        '<div style="display:grid;grid-template-columns:1fr;gap:10px;">',
        '<div><label style="display:block;font-size:.85rem;color:rgba(255,255,255,0.7);margin-bottom:4px;">Name</label><input required id="reg-name" type="text" style="width:100%;padding:10px 12px;background:#0f1419;border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#e2e8f0;"></div>',
        '<div><label style="display:block;font-size:.85rem;color:rgba(255,255,255,0.7);margin-bottom:4px;">Email</label><input required id="reg-email" type="email" style="width:100%;padding:10px 12px;background:#0f1419;border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#e2e8f0;"></div>',
        '<div><label style="display:block;font-size:.85rem;color:rgba(255,255,255,0.7);margin-bottom:4px;">Phone (marketing only)</label><input id="reg-phone" type="text" placeholder="Optional" style="width:100%;padding:10px 12px;background:#0f1419;border:1px solid rgba(255,255,255,0.12);border-radius:10px;color:#e2e8f0;"></div>',
        '<div id="reg-prompt-error" style="display:none;color:#f87171;font-size:.95rem;"></div>',
        '<button type="submit" class="btn btn-primary" style="width:100%;margin-top:4px;">Register</button>',
        '</div>',
        '</form>'
      ].join('');

      overlay.appendChild(modal);
      document.body.appendChild(overlay);

      // Close button — restart timer for next idle period
      var closeBtn = document.getElementById('reg-prompt-close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function () {
          overlay.remove();
          registrationPromptShown = false;
          startRegistrationPromptTimer();
        });
      }

      var form = document.getElementById('reg-prompt-form');
      var errEl = document.getElementById('reg-prompt-error');
      if (form) { form.addEventListener('submit', function (e) { e.preventDefault(); }); }

      // Submit → POST /api/auth/pre-register
      var submitBtn = modal.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.addEventListener('click', function () {
          if (!window.corsoApi || !window.corsoApi.base) return;

          var name  = (document.getElementById('reg-name')  && document.getElementById('reg-name').value)  ? document.getElementById('reg-name').value.trim()  : '';
          var email = (document.getElementById('reg-email') && document.getElementById('reg-email').value) ? document.getElementById('reg-email').value.trim() : '';
          var phone = (document.getElementById('reg-phone') && document.getElementById('reg-phone').value) ? document.getElementById('reg-phone').value.trim() : '';

          if (!name || !email) {
            errEl.textContent = 'Name and email are required.';
            errEl.style.display = 'block';
            return;
          }
          if (errEl) errEl.style.display = 'none';
          submitBtn.disabled = true;
          submitBtn.textContent = 'Saving...';

          window.corsoApi.post('/auth/pre-register', { name: name, email: email, phone: phone })
            .then(function (r) {
              return r.json().then(function (data) {
                if (!r.ok) throw new Error(data && data.error ? data.error : 'Registration failed');
                return data;
              });
            })
            .then(function (data) {
              var tok = data && data.activation_token ? data.activation_token : '';
              var u   = data && data.user ? data.user : null;
              // Store activation token and user info for payment step
              if (tok) localStorage.setItem('pendingActivationToken', tok);
              if (u && u.email) localStorage.setItem('pendingUserEmail', u.email);
              if (u && u.name)  localStorage.setItem('pendingUserName',  u.name);
              // Pre-fill quiz name input if visible
              try {
                var quizNameInput = document.querySelector('.cert-form input[type="text"]');
                if (quizNameInput && u && u.name) quizNameInput.value = u.name;
              } catch (e) {}
              overlay.remove();
              registrationPromptShown = false;
              alert('Registration successful! You can now take the quiz.');
            })
            .catch(function (e) {
              if (errEl) {
                errEl.textContent = e && e.message ? e.message : 'Registration failed';
                errEl.style.display = 'block';
              }
              submitBtn.disabled = false;
              submitBtn.textContent = 'Register';
            });
        });
      }
    }

    /** Start 30-second idle timer before showing registration modal */
    function startRegistrationPromptTimer() {
      if (isLoggedInClientSide()) return;
      if (hasPendingActivation()) return;
      clearTimeout(registrationPromptTimer);
      registrationPromptTimer = setTimeout(function () {
        showRegistrationModal();
      }, 30000);
    }

    // Kick off timer on all pages
    startRegistrationPromptTimer();

    // =========================================================================
    // SHARED: Nav — mobile hamburger toggle
    // Used by: ALL pages that have .menu-toggle + .nav
    // =========================================================================
    if (!isHome && nav && !nav.querySelector('.nav-home')) {
      var homeLink = document.createElement('a');
      homeLink.href = 'index.php';
      homeLink.className = 'nav-home';
      homeLink.textContent = 'Home';
      nav.insertBefore(homeLink, nav.firstChild);
    }

    function toggleNav() {
      if (!nav || !menuToggle) return;
      nav.classList.toggle('is-open');
      menuToggle.setAttribute('aria-expanded', nav.classList.contains('is-open'));
    }
    if (menuToggle && nav) {
      menuToggle.addEventListener('click', toggleNav);
      menuToggle.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleNav(); }
      });
      if (header) {
        header.addEventListener('click', function (e) {
          var btn = e.target.closest && e.target.closest('.menu-toggle');
          if (btn) toggleNav();
        });
      }
      menuToggle.setAttribute('tabindex', '0');
      menuToggle.setAttribute('role', 'button');
      menuToggle.setAttribute('aria-controls', 'nav-panel');
    }
    document.querySelectorAll('.nav a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (nav) nav.classList.remove('is-open');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
      });
    });

    // =========================================================================
    // SHARED: Scroll progress bar + header shadow on scroll
    // Used by: ALL pages
    // =========================================================================
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
        var y   = window.scrollY || window.pageYOffset || 0;
        var max = Math.max(1, (document.documentElement.scrollHeight || 1) - window.innerHeight);
        var p   = Math.max(0, Math.min(1, y / max));
        if (header) header.classList.toggle('is-scrolled', y > 8);
        if (progressBar) progressBar.style.width = (p * 100).toFixed(2) + '%';
      });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // =========================================================================
    // SHARED: Scroll reveal animations
    // Elements matching selectors get .reveal class; IntersectionObserver
    // adds .is-visible when they enter viewport.
    // Used by: index.php (mainly), other pages with matching elements
    // =========================================================================
    var revealTargets = [
      '.section-title', '.feature-card', '.course-card', '.blog-cat-card',
      '.pricing-feature', '.content-block', '.about-stat', '.about-card',
      '.about-intro-card', '.footer', '.chip', '.badge-list', '.assess-search',
      '.hero-card', '.stat-chip', '.subheader', '.test-topics', '.preview-pairs',
      '.preview-pair', '.cta-inner', '.featured-track', '.companies-track',
      '.brands-track', '.promo-card', '.site-footer'
    ];
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

    // =========================================================================
    // PAGE: index.php — Assessments section
    // #startSkillCheck button scrolls to #assessments.
    // .course-test-btn buttons open the quiz modal for a specific course.
    // Category tabs + search filter .course-card elements.
    // =========================================================================
    function bindSkillCheckButtons() {
      document.querySelectorAll('#startSkillCheck').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var target = document.getElementById('assessments');
          if (target) target.scrollIntoView({ block: 'start' });
        });
      });
    }
    bindSkillCheckButtons();

    (function initCourseSkillButtons() {
      // Individual course "Take Test" buttons
      document.querySelectorAll('.assessments .course-test-btn').forEach(function (btn) {
        var course = (btn.dataset.course || '').trim();
        var quizId = (btn.dataset.quizId || '').trim();
        if (course) btn.addEventListener('click', function () { openQuizModal(course, quizId); });
      });

      // Category tab filtering
      var grid = document.getElementById('assessments-grid');
      var cards = grid ? grid.querySelectorAll('.course-card') : [];
      var categoryTabs = document.querySelectorAll('.category-tabs .category-tab');
      function filterByCategory(cat) {
        cards.forEach(function (card) {
          var cardCat = card.getAttribute('data-category') || '';
          var show = cat === 'all' || cardCat === cat;
          card.style.display = show ? '' : 'none';
        });
      }
      categoryTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          categoryTabs.forEach(function (t) { t.classList.remove('is-active'); t.setAttribute('aria-selected', 'false'); });
          tab.classList.add('is-active');
          tab.setAttribute('aria-selected', 'true');
          filterByCategory(tab.dataset.category || 'all');
        });
      });

      // Search input filtering
      var searchInputAssess = document.querySelector('.assessments-search-input') || document.querySelector('.assess-search .search-input');
      if (searchInputAssess && grid) {
        searchInputAssess.addEventListener('input', function () {
          var q = (searchInputAssess.value || '').toLowerCase().trim();
          var activeCat = document.querySelector('.category-tabs .category-tab.is-active');
          var cat = activeCat ? (activeCat.dataset.category || 'all') : 'all';
          cards.forEach(function (card) {
            var titleEl = card.querySelector('h3');
            var title   = (titleEl ? titleEl.textContent : '').toLowerCase();
            var matchSearch = !q || title.indexOf(q) !== -1;
            var matchCat    = cat === 'all' || (card.getAttribute('data-category') || '') === cat;
            card.style.display = matchSearch && matchCat ? '' : 'none';
          });
        });
      }
    })();

    // =========================================================================
    // PAGE: index.php — Subpage buttons hover gradient effect
    // =========================================================================
    var subButtons = document.querySelectorAll('.subpage-buttons .btn');
    if (subButtons.length) {
      subButtons.forEach(function (el) {
        el.addEventListener('pointermove', function (ev) {
          var rect = el.getBoundingClientRect();
          var x = ((ev.clientX - rect.left) / rect.width)  * 100;
          var y = ((ev.clientY - rect.top)  / rect.height) * 100;
          el.style.setProperty('--mx', x.toFixed(2) + '%');
          el.style.setProperty('--my', y.toFixed(2) + '%');
        });
      });
    }

    // =========================================================================
    // PAGE: index.php — Hero search bar
    // .search-button click opens quiz modal for matching course.
    // .search-input shows autocomplete suggestions dropdown.
    // =========================================================================
    var searchCta = document.querySelector('.search-button');
    if (searchCta) {
      searchCta.addEventListener('click', function () {
        var val   = '';
        var input = document.querySelector('.search-input');
        if (input) val = (input.value || '').trim();
        var keys  = ['Data Science Fundamentals', 'Java Basics', 'Digital Marketing', 'Excel for Analysis', 'Python Basics', 'SQL Essentials'];
        var match = keys.find(function (k) { return k.toLowerCase() === val.toLowerCase(); });
        openQuizModal(match);
      });
    }

    var searchInput = document.querySelector('.search-input');
    if (searchInput) {
      var topics = ['Python Basics', 'SQL Essentials', 'Java Basics', 'Data Science', 'Digital Marketing', 'Web Development', 'Machine Learning', 'Cloud Fundamentals', 'Cybersecurity', 'React', 'Node.js', 'C++', 'Git & GitHub', 'Networking', 'Excel Analytics'];
      var wrap    = document.querySelector('.assess-search');
      var suggest = document.createElement('div');
      suggest.className = 'search-suggest';
      suggest.style.display = 'none';
      wrap.appendChild(suggest);

      function positionSuggest() {
        var rectWrap = wrap.getBoundingClientRect();
        var rectIn   = searchInput.getBoundingClientRect();
        suggest.style.left     = (rectIn.left - rectWrap.left) + 'px';
        suggest.style.top      = (rectIn.bottom - rectWrap.top + 6) + 'px';
        suggest.style.minWidth = rectIn.width + 'px';
      }

      function renderSuggest(val) {
        suggest.innerHTML = '';
        var q     = (val || '').toLowerCase();
        var items = topics.filter(function (t) { return t.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
        items.forEach(function (t) {
          var it = document.createElement('div');
          it.className = 'suggest-item';
          it.textContent = t;
          it.addEventListener('mousedown', function () {
            searchInput.value    = t;
            suggest.style.display = 'none';
          });
          suggest.appendChild(it);
        });
        suggest.style.display = items.length ? 'block' : 'none';
        if (items.length) positionSuggest();
      }

      searchInput.addEventListener('input',  function () { renderSuggest(searchInput.value); });
      searchInput.addEventListener('focus',  function () { renderSuggest(searchInput.value); positionSuggest(); });
      searchInput.addEventListener('blur',   function () { setTimeout(function () { suggest.style.display = 'none'; }, 120); });
      window.addEventListener('resize',      function () { if (suggest.style.display === 'block') positionSuggest(); });
    }

    // =========================================================================
    // PAGE: index.php — Marquee duplication (companies + brands)
    // Clones inner items so CSS animation loops seamlessly.
    // =========================================================================
    var companiesTrack = document.querySelector('.companies-marquee .companies-track');
    if (companiesTrack && companiesTrack.dataset.loopDup !== 'true') {
      var original = '';
      companiesTrack.querySelectorAll('.company-badge').forEach(function (el) { original += el.outerHTML; });
      companiesTrack.innerHTML = original + original;
      companiesTrack.dataset.loopDup = 'true';
    }
    var brandsTrack = document.querySelector('.brands-marquee .brands-track');
    if (brandsTrack && brandsTrack.dataset.loopDup !== 'true') {
      var originalBrands = '';
      brandsTrack.querySelectorAll('.brand-badge').forEach(function (el) { originalBrands += el.outerHTML; });
      brandsTrack.innerHTML = originalBrands + originalBrands;
      brandsTrack.dataset.loopDup = 'true';
    }

    // =========================================================================
    // PAGE: index.php — Quiz modal + Razorpay payment flow
    //
    // Flow:
    //   1. User clicks course card / search → openQuizModal(course)
    //   2. 10 MCQs displayed with 10-min countdown
    //   3. On submit: score >= 60% → show name input + "Proceed to Payment"
    //   4. Payment button → POST /api/payments/razorpay/create-order
    //   5. Razorpay popup → on success → POST /api/payments/razorpay/verify
    //   6. Backend activates account, issues certificate → showPostPaymentModal
    //
    // API endpoints used:
    //   POST /api/payments/razorpay/create-order
    //   POST /api/payments/razorpay/verify
    //   POST /api/payments/razorpay/payment-failed
    //   POST /api/quiz-attempts/log   (logged-in users only)
    // =========================================================================
    function openQuizModal(course, quizId) {
      startRegistrationPromptTimer();

      // --- Build modal DOM structure ---
      var modal = document.createElement('div');
      modal.className = 'quiz-modal';
      var card = document.createElement('div');
      card.className = 'quiz-card';

      var quizHeader = document.createElement('div');
      quizHeader.className = 'quiz-header';
      var titleEl = document.createElement('div');
      titleEl.className = 'quiz-title';
      titleEl.textContent = (course && course.length) ? (course + ' — 10 MCQs') : 'Quick Skill Check — 10 MCQs';
      var timerEl = document.createElement('div');
      timerEl.className = 'quiz-timer';
      quizHeader.appendChild(titleEl);
      quizHeader.appendChild(timerEl);

      var quizProgress = document.createElement('div');
      quizProgress.className = 'quiz-progress';
      var quizProgressBar = document.createElement('div');
      quizProgressBar.className = 'quiz-progress__bar';
      quizProgress.appendChild(quizProgressBar);

      var body     = document.createElement('div');
      body.className = 'quiz-body';

      var footer = document.createElement('div');
      footer.className = 'quiz-footer';
      var closeBtn  = document.createElement('button');
      closeBtn.className = 'quiz-close';
      closeBtn.textContent = 'Close';
      var submitBtn = document.createElement('button');
      submitBtn.className = 'btn btn-primary';
      submitBtn.textContent = 'Submit';
      submitBtn.style.display = 'none';
      footer.appendChild(closeBtn);
      footer.appendChild(submitBtn);

      card.appendChild(quizHeader);
      card.appendChild(quizProgress);
      card.appendChild(body);
      card.appendChild(footer);
      modal.appendChild(card);
      document.body.appendChild(modal);

      // --- Questions: Database se fetch karo ---
      // --- Question bank (10 per course) ---
      var questions = [];

      // API se fetch karo
      if (quizId) {
        fetch((window.corsoApi && window.corsoApi.base ? window.corsoApi.base : '') + '/quiz/' + quizId + '/questions')
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data && data.questions && data.questions.length) {
              // DB format → JS format convert karo
              questions = data.questions.map(function(q) {
                var optMap = { 'A': 0, 'B': 1, 'C': 2, 'D': 3 };
                return {
                  q:    q.question,
                  opts: [q.option_a, q.option_b, q.option_c, q.option_d],
                  a:    optMap[(q.correct_option || 'A').toUpperCase()] || 0
                };
              });
              renderQuestion();
            } else {
              body.innerHTML = '<p style="color:#f87171;text-align:center;">Questions load nahi hue. Please refresh karein.</p>';
            }
          })
          .catch(function() {
            body.innerHTML = '<p style="color:#f87171;text-align:center;">Server se connect nahi ho pa raha.</p>';
          });
      }

      // --- Quiz state ---
      var idx       = 0;
      var score     = 0;
      var selected  = -1;
      var seconds   = 600; // 10 minutes
      var intervalId = 0;

      function fmt(n) {
        var m = Math.floor(n / 60);
        var s = n % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
      }
      timerEl.textContent = fmt(seconds);

      function tick() {
        seconds -= 1;
        if (seconds <= 0) { seconds = 0; finish(); }
        timerEl.textContent = fmt(seconds);
      }
      intervalId = setInterval(tick, 1000);

      /** Render the current question and options */
      function renderQuestion() {
        body.innerHTML = '';
        selected = -1;
        var qEl = document.createElement('div');
        qEl.className = 'quiz-question';
        qEl.textContent = 'Q' + (idx + 1) + '/' + questions.length + ': ' + questions[idx].q;
        var list = document.createElement('div');
        list.className = 'quiz-options';
        questions[idx].opts.forEach(function (opt, i) {
          var b = document.createElement('button');
          b.className = 'quiz-option';
          b.type = 'button';
          b.textContent = opt;
          b.addEventListener('click', function () {
            document.querySelectorAll('.quiz-option').forEach(function (el) { el.classList.remove('is-selected'); });
            b.classList.add('is-selected');
            selected = i;
          });
          list.appendChild(b);
        });
        var actions = document.createElement('div');
        actions.className = 'quiz-actions';
        if (idx < questions.length - 1) {
          var nextBtn = document.createElement('button');
          nextBtn.className = 'btn btn-outline';
          nextBtn.textContent = 'Next';
          nextBtn.addEventListener('click', function () {
            if (selected === -1) return;
            if (selected === questions[idx].a) score += 1;
            idx += 1;
            updateProgress();
            renderQuestion();
          });
          actions.appendChild(nextBtn);
        } else {
          var submitLast = document.createElement('button');
          submitLast.className = 'btn btn-primary';
          submitLast.textContent = 'Submit';
          submitLast.addEventListener('click', function () {
            if (selected === -1) return;
            if (selected === questions[idx].a) score += 1;
            updateProgress();
            finish();
          });
          actions.appendChild(submitLast);
        }
        body.appendChild(qEl);
        body.appendChild(list);
        body.appendChild(actions);
      }

      function updateProgress() {
        var p = Math.max(0, Math.min(1, idx / questions.length));
        quizProgressBar.style.width = (p * 100).toFixed(2) + '%';
      }

      /** Called when quiz ends (submit or timer expires) */
      function finish() {
        clearInterval(intervalId);

        // Log attempt for logged-in users → POST /api/quiz-attempts/log
        try {
          var tok = localStorage.getItem('apiToken');
          if (tok && window.corsoApi && window.corsoApi.base) {
            var ct = (course != null && String(course).trim()) ? String(course).trim() : '';
            if (ct) {
              var apiBase = String(window.corsoApi.base).replace(/\/?$/, '');
              fetch(apiBase + '/quiz-attempts/log', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + tok },
                body: JSON.stringify({ course_title: ct, score: score, total: questions.length })
              }).catch(function () {});
            }
          }
        } catch (e) {}

        submitBtn.disabled = true;
        body.innerHTML    = '';
        quizProgressBar.style.width = '100%';

        var result = document.createElement('div');
        result.className = 'quiz-result';
        var scoreEl = document.createElement('div');
        scoreEl.className = 'quiz-score';
        var pct = Math.round((score / questions.length) * 100);
        scoreEl.textContent = 'Score: ' + score + '/' + questions.length + ' (' + pct + '%)';
        var note = document.createElement('div');
        note.className = 'quiz-note';
        note.textContent = pct >= 60
          ? 'Congratulations! Complete payment to unlock your certificate.'
          : 'Minimum 60% required to proceed to payment. Please retake the test.';
        result.appendChild(scoreEl);
        result.appendChild(note);
        body.appendChild(result);

        if (pct >= 60) {
          // --- PASS: show name input + payment flow ---
          var form = document.createElement('div');
          form.className = 'cert-form';
          var payBtn = document.createElement('button');
          payBtn.className = 'btn btn-primary';
          payBtn.textContent = 'Proceed to Payment';
          form.appendChild(payBtn);
          result.appendChild(form);

          var paymentOpen = false;
          payBtn.addEventListener('click', function () {
            if (paymentOpen) return;

            // Logged in nahi hai — registration popup dikhao
            if (!isLoggedInClientSide() && !hasPendingActivation()) {
              showRegistrationModal();
              return;
            }

            // Logged in hai — name input dikhao (certificate ke liye)
            var existingNameInput = form.querySelector('.cert-name-input');
            if (!existingNameInput) {
              // Name input banana
              var nameWrap = document.createElement('div');
              nameWrap.style.cssText = 'margin-bottom:12px;';
              var nameInput = document.createElement('input');
              nameInput.type = 'text';
              nameInput.className = 'cert-name-input';
              nameInput.placeholder = 'Enter your name for the certificate';
              nameInput.style.cssText = 'width:100%;padding:10px 14px;border-radius:8px;border:1px solid #444;background:#1e2a3a;color:#fff;font-size:1rem;margin-bottom:8px;';
              // Prefill from sessionUser
              try {
                var u = JSON.parse(localStorage.getItem('sessionUser') || 'null');
                if (u && u.name) nameInput.value = u.name;
              } catch(e) {}
              nameWrap.appendChild(nameInput);
              form.insertBefore(nameWrap, payBtn);

              // Button text change karo
              payBtn.textContent = 'Pay with Razorpay';
              return; // Pehle naam enter karne do
            }

            // Name already show ho raha hai — ab payment proceed karo
            var name = (existingNameInput.value || '').trim();
            if (!name) {
              existingNameInput.focus();
              existingNameInput.style.border = '1px solid #f87171';
              return;
            }

            paymentOpen = true;
            payBtn.style.display = 'none';
            var pay = document.createElement('div');
            pay.className = 'cert-form';
            var razorpayBtn = document.createElement('button');
            razorpayBtn.className = 'btn btn-primary';
            razorpayBtn.textContent = 'Pay with Razorpay';
            pay.appendChild(razorpayBtn);
            var payHint = document.createElement('p');
            payHint.style.cssText = 'font-size:0.85rem;color:var(--text-muted,rgba(255,255,255,0.55));margin-top:10px;line-height:1.45;';
            payHint.textContent = 'If you see "International cards are not supported", choose Netbanking or UPI on the Razorpay screen, or enable international card payments in Razorpay Dashboard > Settings > International payments.';
            pay.appendChild(payHint);
            result.appendChild(pay);

            razorpayBtn.addEventListener('click', function () {
              var pendingEmail = localStorage.getItem('pendingUserEmail') || localStorage.getItem('userEmail') || '';
              var pendingToken = localStorage.getItem('pendingActivationToken') || '';
              // Certificate ke liye naam — name input se lo
              var name = '';
              try {
                var ni = form.querySelector('.cert-name-input');
                name = ni ? ni.value.trim() : '';
                if (!name) {
                  var u = JSON.parse(localStorage.getItem('sessionUser') || 'null');
                  if (u && u.name) name = u.name;
                }
              } catch(e) {}

              // Guard: must have token
              if (!pendingEmail || !pendingToken) {
                registrationPromptShown = false;
                showRegistrationModal();
                setTimeout(function () { alert('Please complete registration before paying. Enter your details in the form that appeared.'); }, 100);
                return;
              }
              if (!window.corsoApi || !window.corsoApi.base) {
                alert('Payment is unavailable. Ensure the server is running.');
                return;
              }

              razorpayBtn.disabled = true;
              razorpayBtn.textContent = 'Please wait...';
              var currentRzpOrderId = '';

              loadRazorpayScript(function () {
                // Step 1: Create Razorpay order → POST /api/payments/razorpay/create-order
                window.corsoApi.post('/payments/razorpay/create-order', {})
                  .then(function (r) {
                    return r.json().then(function (data) {
                      if (!r.ok) throw new Error(data && data.error ? data.error : 'Could not create order');
                      return data;
                    });
                  })
                  .then(function (orderData) {
                    currentRzpOrderId = orderData.order_id || '';
                    razorpayBtn.disabled = false;
                    razorpayBtn.textContent = 'Pay with Razorpay';

                    var options = {
                      key:         orderData.key_id,
                      amount:      orderData.amount,
                      currency:    orderData.currency,
                      order_id:    orderData.order_id,
                      name:        'Corso E-Learning',
                      description: 'Account activation',
                      prefill:     { email: pendingEmail, name: name },

                      // Step 2: Payment captured → verify + activate account
                      handler: function (response) {
                        razorpayBtn.disabled = true;
                        razorpayBtn.textContent = 'Activating...';
                        // POST /api/payments/razorpay/verify
                        window.corsoApi.post('/payments/razorpay/verify', {
                          razorpay_payment_id: response.razorpay_payment_id,
                          razorpay_order_id:   response.razorpay_order_id,
                          razorpay_signature:  response.razorpay_signature,
                          email:               pendingEmail,
                          activation_token:    pendingToken,
                          course_name:         course || '',
                          quiz_score:          score,
                          quiz_total:          questions.length
                        }).then(function (r) {
                          return r.json().then(function (data) {
                            if (!r.ok) throw new Error((data && data.error) ? data.error : ('Activation failed (HTTP ' + r.status + ')'));
                            return data;
                          });
                        }).then(function (data) {
                          // Clean up pending state
                          localStorage.removeItem('pendingActivationToken');
                          localStorage.removeItem('pendingUserEmail');
                          localStorage.removeItem('pendingUserName');

                          // Quiz modal band karo
                          try { modal.classList.remove('is-open'); document.body.removeChild(modal); } catch(e) {}
                          clearInterval(intervalId);

                          // Show success modal
                          if (data && data.email_sent) {
                            showPostPaymentModal({ emailSent: true, email: pendingEmail });
                          } else {
                            if (data && data.temp_password) {
                              try { localStorage.setItem('tempUserEmail', pendingEmail); localStorage.setItem('tempUserPassword', data.temp_password); } catch (e) {}
                            }
                            showPostPaymentModal({
                              emailSent:     false,
                              email:         pendingEmail,
                              tempPassword:  data && data.temp_password,
                              emailError:    data && data.email_error,
                              tempLoginUrl:  data && data.temp_login_url,
                              normalLoginUrl: data && data.normal_login_url
                            });
                          }

                          // Auto-download certificate PDF if backend provides URL
                          if (data && data.certificate_download_url) {
                            var certLink = document.createElement('a');
                            certLink.href = data.certificate_download_url;
                            certLink.download = 'corso-certificate-' + (data.certificate_number || 'cert') + '.pdf';
                            document.body.appendChild(certLink);
                            certLink.click();
                            document.body.removeChild(certLink);
                          }

                          // Store cert info in localStorage for my_certificates.php
                          var cert = {
                            id: data.certificate_id || 0,
                            name: name, score: score, total: questions.length,
                            ts: Date.now(), course: (course || 'General'),
                            certificate_number: data.certificate_number || null
                          };
                          var list = [];
                          try { list = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
                          list.push(cert);
                          localStorage.setItem('certificates', JSON.stringify(list));

                          razorpayBtn.disabled = true;
                          razorpayBtn.textContent = 'Paid';
                        }).catch(function (e) {
                          razorpayBtn.disabled = false;
                          razorpayBtn.textContent = 'Pay with Razorpay';
                          alert('Payment captured but activation failed: ' + (e && e.message ? e.message : 'Unknown error'));
                        });
                      },

                      modal: {
                        ondismiss: function () {
                          razorpayBtn.disabled = false;
                          razorpayBtn.textContent = 'Pay with Razorpay';
                        }
                      }
                    };

                    var rzp = new Razorpay(options);

                    // Step 3: Payment failed → POST /api/payments/razorpay/payment-failed
                    rzp.on('payment.failed', function (resp) {
                      razorpayBtn.disabled = false;
                      razorpayBtn.textContent = 'Pay with Razorpay';
                      var err  = resp && resp.error ? resp.error : {};
                      var meta = err.metadata || {};
                      var d    = err.description || 'Payment failed';
                      if (window.corsoApi && window.corsoApi.base && pendingEmail) {
                        window.corsoApi.post('/payments/razorpay/payment-failed', {
                          email:               pendingEmail,
                          activation_token:    pendingToken || '',
                          razorpay_order_id:   meta.order_id || currentRzpOrderId || '',
                          razorpay_payment_id: meta.payment_id || '',
                          course_name:         course || '',
                          error_code:          err.code || '',
                          error_description:   d
                        }).catch(function () {});
                      }
                      alert(d);
                    });

                    rzp.open();
                  })
                  .catch(function (e) {
                    razorpayBtn.disabled = false;
                    razorpayBtn.textContent = 'Pay with Razorpay';
                    alert('Could not start payment: ' + (e && e.message ? e.message : 'Unknown error'));
                  });
              });
            });
          });

        } else {
          // --- FAIL: show retake button ---
          var retry = document.createElement('button');
          retry.className = 'btn btn-outline';
          retry.textContent = 'Retake Test';
          result.appendChild(retry);
          retry.addEventListener('click', function () {
            idx = 0; score = 0; selected = -1; seconds = 600;
            if (intervalId) clearInterval(intervalId);
            intervalId = setInterval(tick, 1000);
            timerEl.textContent = fmt(seconds);
            submitBtn.disabled = false;
            body.innerHTML = '';
            quizProgressBar.style.width = '0%';
            updateProgress();
            renderQuestion();
          });
        }
      }

      // Close button — remove modal and clear timer
      closeBtn.addEventListener('click', function () {
        clearInterval(intervalId);
        modal.classList.remove('is-open');
        document.body.removeChild(modal);
      });
      submitBtn.addEventListener('click', finish);
      modal.classList.add('is-open');
      updateProgress();
      // renderQuestion() fetch callback mein call hoga (questions load hone ke baad)
    }

    // =========================================================================
    // SHARED: Auth nav — show/hide links based on login state
    // Reads 'sessionUser' from localStorage.
    // Used by: ALL pages with .nav-profile / .nav-logout / .nav-mycerts
    // =========================================================================
    var userNavMyCerts = document.querySelector('.nav-mycerts');
    var loginLink      = document.querySelector('a[href="login.php"]');
    var profileBtn     = document.querySelector('.nav-profile');
    var profileMenu    = document.querySelector('.nav-profile-menu');
    var logoutBtn      = document.querySelector('.nav-logout');
    var navName        = document.querySelector('.nav-name');

    (function updateAuthNav() {
      var user   = null;
      try { user = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
      var logged = !!(user && user.email);
      if (userNavMyCerts) userNavMyCerts.style.display = logged ? 'inline-block' : 'none';
      if (loginLink)      loginLink.style.display      = logged ? 'none' : 'inline-block';
      if (profileBtn)     profileBtn.style.display     = logged ? 'inline-flex' : 'none';
      if (navName)        navName.textContent           = logged ? (user.name || user.email.split('@')[0]) : 'Profile';

      // Show admin panel link for admin/hr/super_admin roles
      var role    = (user && user.role) ? String(user.role) : '';
      var isAdmin = logged && (role === 'admin' || role === 'hr' || role === 'super_admin' || user.isAdmin === true);
      var dashboardLink = profileMenu && profileMenu.querySelector('.nav-dashboard-link');
      var adminLink     = profileMenu && profileMenu.querySelector('.nav-admin-link');
      if (dashboardLink) dashboardLink.style.display = isAdmin ? 'none' : '';
      if (adminLink)     adminLink.style.display     = isAdmin ? '' : 'none';
      if (profileMenu)   profileMenu.hidden = true;
    })();

    // Profile menu open/close
    if (profileBtn && profileMenu) {
      function openProfileMenu() {
        var navEl   = document.querySelector('.nav');
        var navRect = navEl.getBoundingClientRect();
        var btnRect = profileBtn.getBoundingClientRect();
        profileMenu.style.left = (btnRect.left - navRect.left) + 'px';
        profileMenu.style.top  = (btnRect.bottom - navRect.top + 8) + 'px';
        profileBtn.setAttribute('aria-expanded', 'true');
        profileMenu.hidden = false;
      }
      function closeProfileMenu() {
        profileMenu.hidden = true;
        profileBtn.setAttribute('aria-expanded', 'false');
      }
      profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        profileBtn.getAttribute('aria-expanded') === 'true' ? closeProfileMenu() : openProfileMenu();
      });
      document.addEventListener('click', function (e) {
        if (!profileMenu.hidden && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) closeProfileMenu();
      });
      document.addEventListener('pointerdown', function (e) {
        if (!profileMenu.hidden && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) closeProfileMenu();
      }, true);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !profileMenu.hidden) closeProfileMenu();
      });
      window.addEventListener('blur', function () { if (!profileMenu.hidden) closeProfileMenu(); });
      profileMenu.addEventListener('click', function (e) {
        var t = e.target && e.target.closest && e.target.closest('a,button');
        if (t) closeProfileMenu();
      });
    }

    // =========================================================================
    // SHARED: Logout
    // Clears all localStorage keys and redirects to index.php
    // Used by: nav logout button (all pages) + dashboard.php .dash-signout
    // =========================================================================
    function doLogout() {
      localStorage.removeItem('sessionUser');
      localStorage.removeItem('pendingActivationToken');
      localStorage.removeItem('pendingUserEmail');
      localStorage.removeItem('pendingUserName');
      localStorage.removeItem('tempUserEmail');
      localStorage.removeItem('tempUserPassword');
      if (profileMenu) profileMenu.hidden = true;
      if (profileBtn)  profileBtn.setAttribute('aria-expanded', 'false');
      registrationPromptShown = false;
      startRegistrationPromptTimer();
      location.href = 'index.php';
    }
    if (logoutBtn) logoutBtn.addEventListener('click', doLogout);

    // =========================================================================
    // SHARED: Theme toggle (dark / light)
    // .dash-theme-checkbox checkbox toggles data-theme on <html>
    // Persists to localStorage key 'corsoTheme'
    // Used by: dashboard.php
    // =========================================================================
    var themeCheckbox = document.querySelector('.dash-theme-checkbox');
    var themeThumb    = document.querySelector('.dash-theme-thumb');
    if (themeCheckbox) {
      themeCheckbox.checked = (document.documentElement.getAttribute('data-theme') !== 'light');
      if (themeThumb) themeThumb.textContent = themeCheckbox.checked ? '🌙' : '☀️';
      themeCheckbox.addEventListener('change', function () {
        var next = themeCheckbox.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('corsoTheme', next); } catch (e) {}
        if (themeThumb) themeThumb.textContent = themeCheckbox.checked ? '🌙' : '☀️';
      });
    }

    // =========================================================================
    // PAGE: dashboard.php — Student dashboard
    // Reads certificates from localStorage and renders stats + recent list.
    // Also renders a mini calendar widget.
    // =========================================================================
    var dash = document.querySelector('.dashboard');
    if (dash) {
      var u = null;
      try { u = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}

      // Welcome name
      var nameEl = document.querySelector('.dash-user');
      if (nameEl) nameEl.textContent = u && (u.name || (u.email || '').split('@')[0]) || 'User';

      // Stats: total certs, passed, avg score
      var statsCerts = [];
      try { statsCerts = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
      if (!Array.isArray(statsCerts)) statsCerts = [];
      var totalCerts = statsCerts.length;
      var passedCount = statsCerts.filter(function (c) { return (c.score / (c.total || 10)) * 100 >= 60; }).length;
      var avgScore    = totalCerts ? Math.round(statsCerts.reduce(function (sum, c) { return sum + (c.score / (c.total || 10)) * 100; }, 0) / totalCerts) : 0;
      var totalEl   = document.querySelector('.dash-stat-value[data-stat="total"]');
      var averageEl = document.querySelector('.dash-stat-value[data-stat="average"]');
      var passedEl  = document.querySelector('.dash-stat-value[data-stat="passed"]');
      if (totalEl)   totalEl.textContent   = totalCerts;
      if (averageEl) averageEl.textContent = avgScore + '%';
      if (passedEl)  passedEl.textContent  = passedCount;

      // Recent certificates list (latest 3)
      var certsList = document.querySelector('.dash-certs');
      if (certsList) {
        var clist = [];
        try { clist = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
        if (!Array.isArray(clist)) clist = [];
        clist = clist.slice().sort(function (a, b) { return (b.ts || 0) - (a.ts || 0); }).slice(0, 3);
        if (!clist.length) {
          var none = document.createElement('p');
          none.textContent = 'No certificates yet.';
          certsList.parentNode.replaceChild(none, certsList);
        } else {
          clist.forEach(function (c) {
            var li     = document.createElement('li');
            var badge  = document.createElement('span');
            badge.className = 'badge badge-user';
            badge.textContent = (c.name || 'SC').split(' ').map(function (w) { return w[0]; }).join('').slice(0, 2).toUpperCase();
            var center = document.createElement('div');
            var title  = document.createElement('strong');
            title.textContent = c.name;
            var meta  = document.createElement('div');
            meta.className = 'meta';
            var issuedDate = c.issued_at ? new Date(c.issued_at) : new Date(c.ts || Date.now());
            meta.textContent = 'Issued ' + issuedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            center.appendChild(title);
            center.appendChild(meta);
            var right = document.createElement('a');
            right.className = 'dash-link';
            right.href = 'verify.php?id=' + encodeURIComponent(c.id);
            right.textContent = 'View';
            li.appendChild(badge);
            li.appendChild(center);
            li.appendChild(right);
            certsList.appendChild(li);
          });
        }
      }

      // --- Mini calendar widget ---
      var dashCalDays  = document.getElementById('dash-calendar-days');
      var dashCalMonth = document.getElementById('dash-calendar-month');
      var dashCalNav   = document.querySelector('.dash-calendar-nav');
      if (dashCalDays && dashCalMonth) {
        var calState  = { year: new Date().getFullYear(), month: new Date().getMonth() };
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var dayNames   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        function renderDashCalendar() {
          var today        = new Date();
          var firstOfMonth = new Date(calState.year, calState.month, 1);
          // Title mein aaj ki date dikhao (agar current month hai), warna month ka naam
          var isCurrentMonth = (calState.year === today.getFullYear() && calState.month === today.getMonth());
          var titleDate = isCurrentMonth ? today : firstOfMonth;
          dashCalMonth.textContent = monthNames[calState.month] + ' ' + titleDate.getDate() + ' ' + dayNames[titleDate.getDay()];
          var startIdx    = firstOfMonth.getDay();
          var daysInMonth = new Date(calState.year, calState.month + 1, 0).getDate();
          dashCalDays.innerHTML = '';
          for (var k = 0; k < startIdx; k++) {
            var pad = document.createElement('span'); pad.className = 'other'; pad.textContent = ''; dashCalDays.appendChild(pad);
          }
          for (var dnum = 1; dnum <= daysInMonth; dnum++) {
            var span = document.createElement('span');
            span.className = (calState.year === today.getFullYear() && calState.month === today.getMonth() && dnum === today.getDate()) ? 'today' : '';
            span.textContent = dnum;
            dashCalDays.appendChild(span);
          }
        }
        renderDashCalendar();

        if (dashCalNav) {
          var prevBtn = dashCalNav.querySelector('button[aria-label="Previous month"]');
          var nextBtn = dashCalNav.querySelector('button[aria-label="Next month"]');
          if (prevBtn) prevBtn.addEventListener('click', function () {
            calState.month--;
            if (calState.month < 0) { calState.month = 11; calState.year--; }
            renderDashCalendar();
          });
          if (nextBtn) nextBtn.addEventListener('click', function () {
            calState.month++;
            if (calState.month > 11) { calState.month = 0; calState.year++; }
            renderDashCalendar();
          });
        }
      }

      // Dashboard sign-out button
      var signout = document.querySelector('.dash-signout');
      if (signout) signout.addEventListener('click', doLogout);
    }

  } // end init()

  // Run after DOM is ready
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

})();