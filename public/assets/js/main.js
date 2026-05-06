(function () {
  if (!window.corsoApi) {
    var base = window.CORSO_API_BASE || '';
    window.corsoApi = {
      base: base,
      token: function () { try { return localStorage.getItem('apiToken') || ''; } catch (e) { return ''; } },
      get: function (path) {
        if (!base) return Promise.reject(new Error('noapi'));
        var h = { 'Accept': 'application/json' };
        var t = this.token();
        if (t) h['Authorization'] = 'Bearer ' + t;
        return fetch(base + path, { method: 'GET', headers: h });
      },
      post: function (path, data) {
        if (!base) return Promise.reject(new Error('noapi'));
        var h = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        var t = this.token();
        if (t) h['Authorization'] = 'Bearer ' + t;
        return fetch(base + path, { method: 'POST', headers: h, body: JSON.stringify(data || {}) });
      }
    };
  }

  function loadRazorpayScript(callback) {
    if (window.Razorpay) {
      callback();
      return;
    }
    var s = document.createElement('script');
    s.src = 'https://checkout.razorpay.com/v1/checkout.js';
    s.onload = function () { callback(); };
    s.onerror = function () {
      alert('Could not load Razorpay. Check your network connection.');
    };
    document.head.appendChild(s);
  }

  function escapeHtml(s) {
    if (s == null) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * @param {object} opts
   * @param {boolean} opts.emailSent
   * @param {string} [opts.email]
   * @param {string} [opts.tempPassword]
   * @param {string} [opts.emailError]
   * @param {string} [opts.tempLoginUrl]
   * @param {string} [opts.normalLoginUrl]
   */
  function showPostPaymentModal(opts) {
    opts = opts || {};
    var existing = document.getElementById('pay-success-overlay');
    if (existing) existing.remove();

    var overlay = document.createElement('div');
    overlay.id = 'pay-success-overlay';
    overlay.style.position = 'fixed';
    overlay.style.inset = '0';
    overlay.style.background = 'rgba(0,0,0,0.65)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = '100000';

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

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-primary';
    btn.style.width = '100%';
    btn.style.marginTop = '18px';
    btn.textContent = 'Continue';
    btn.addEventListener('click', function () {
      overlay.remove();
    });

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) overlay.remove();
    });

    modal.appendChild(title);
    modal.appendChild(content);
    modal.appendChild(btn);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
  }

  function init() {
    document.documentElement.classList.add('js');
    document.documentElement.setAttribute('data-theme', 'dark');

    var menuToggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav');
    var header = document.querySelector('.header');
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var rafId = 0;

    // Mobile menu handled via hamburger toggle; no select dropdown

    var isHome = !!document.querySelector('.hero');

    // -----------------------------
    //  Registration popup (after idle)
    // -----------------------------
    var registrationPromptTimer = null;
    var registrationPromptShown = false;

    function isLoggedInClientSide() {
      try {
        var u = JSON.parse(localStorage.getItem('sessionUser') || 'null');
        return !!(u && u.email);
      } catch (e) {
        return false;
      }
    }

    function hasPendingActivation() {
      try {
        return !!localStorage.getItem('pendingActivationToken');
      } catch (e) {
        return false;
      }
    }

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
      if (form) {
        form.addEventListener('submit', function (e) { e.preventDefault(); });
      }

      var submitBtn = modal.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.addEventListener('click', function () {
          if (!window.corsoApi || !window.corsoApi.base) return;

          var name = (document.getElementById('reg-name') && document.getElementById('reg-name').value) ? document.getElementById('reg-name').value.trim() : '';
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

          window.corsoApi.post('/auth/pre-register', { name: name, email: email, phone: phone }).then(function (r) {
            return r.json().then(function (data) {
              if (!r.ok) throw new Error(data && data.error ? data.error : 'Registration failed');
              return data;
            });
          }).then(function (data) {
            var tok = data && data.activation_token ? data.activation_token : '';
            var u = data && data.user ? data.user : null;
            if (tok) localStorage.setItem('pendingActivationToken', tok);
            if (u && u.email) localStorage.setItem('pendingUserEmail', u.email);
            if (u && u.name) localStorage.setItem('pendingUserName', u.name);
            // Update quiz name input if it exists
            try {
              var quizNameInput = document.querySelector('.cert-form input[type="text"]');
              if (quizNameInput && u && u.name) quizNameInput.value = u.name;
            } catch (e) {}
            overlay.remove();
            registrationPromptShown = false;
            alert('Registration successful! You can now proceed to payment.');
          }).catch(function (e) {
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

    function startRegistrationPromptTimer() {
      if (isLoggedInClientSide()) return;
      if (hasPendingActivation()) return;

      clearTimeout(registrationPromptTimer);
      registrationPromptTimer = setTimeout(function () {
        showRegistrationModal();
      }, 30000);
    }

    // Start registration prompt timer on all pages (30 seconds)
    startRegistrationPromptTimer();

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
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggleNav();
        }
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

    var revealTargets = ['.section-title', '.feature-card', '.course-card', '.blog-cat-card', '.pricing-feature', '.content-block', '.about-stat', '.about-card', '.about-intro-card', '.footer', '.chip', '.badge-list', '.assess-search', '.hero-card', '.stat-chip', '.subheader', '.test-topics', '.preview-pairs', '.preview-pair', '.cta-inner', '.featured-track', '.companies-track', '.brands-track', '.promo-card', '.site-footer'];
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
      document.querySelectorAll('.assessments .course-test-btn').forEach(function (btn) {
        var course = (btn.dataset.course || '').trim();
        if (course) btn.addEventListener('click', function () { openQuizModal(course); });
      });
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
      var searchInputAssess = document.querySelector('.assessments-search-input') || document.querySelector('.assess-search .search-input');
      if (searchInputAssess && grid) {
        searchInputAssess.addEventListener('input', function () {
          var q = (searchInputAssess.value || '').toLowerCase().trim();
          var activeCat = document.querySelector('.category-tabs .category-tab.is-active');
          var cat = activeCat ? (activeCat.dataset.category || 'all') : 'all';
          cards.forEach(function (card) {
            var titleEl = card.querySelector('h3');
            var title = (titleEl ? titleEl.textContent : '').toLowerCase();
            var matchSearch = !q || title.indexOf(q) !== -1;
            var matchCat = cat === 'all' || (card.getAttribute('data-category') || '') === cat;
            card.style.display = matchSearch && matchCat ? '' : 'none';
          });
        });
      }
    })();

    var subButtons = document.querySelectorAll('.subpage-buttons .btn');
    if (subButtons.length) {
      subButtons.forEach(function (el) {
        el.addEventListener('pointermove', function (ev) {
          var rect = el.getBoundingClientRect();
          var x = ((ev.clientX - rect.left) / rect.width) * 100;
          var y = ((ev.clientY - rect.top) / rect.height) * 100;
          el.style.setProperty('--mx', x.toFixed(2) + '%');
          el.style.setProperty('--my', y.toFixed(2) + '%');
        });
      });
    }
    var searchCta = document.querySelector('.search-button');
    if (searchCta) {
      searchCta.addEventListener('click', function () {
        var val = '';
        var input = document.querySelector('.search-input');
        if (input) val = (input.value || '').trim();
        var keys = ['Data Science Fundamentals','Java Basics','Digital Marketing','Excel for Analysis','Python Basics','SQL Essentials'];
        var match = keys.find(function (k) { return k.toLowerCase() === val.toLowerCase(); });
        openQuizModal(match);
      });
    }
    var searchInput = document.querySelector('.search-input');
    if (searchInput) {
      var topics = ['Python Basics','SQL Essentials','Java Basics','Data Science','Digital Marketing','Web Development','Machine Learning','Cloud Fundamentals','Cybersecurity','React','Node.js','C++','Git & GitHub','Networking','Excel Analytics'];
      var wrap = document.querySelector('.assess-search');
      var suggest = document.createElement('div');
      suggest.className = 'search-suggest';
      suggest.style.display = 'none';
      wrap.appendChild(suggest);
      function positionSuggest() {
        var rectWrap = wrap.getBoundingClientRect();
        var rectIn = searchInput.getBoundingClientRect();
        var left = rectIn.left - rectWrap.left;
        var top = rectIn.bottom - rectWrap.top + 6;
        suggest.style.left = left + 'px';
        suggest.style.top = top + 'px';
        suggest.style.minWidth = rectIn.width + 'px';
      }
      function renderSuggest(val) {
        suggest.innerHTML = '';
        var q = (val || '').toLowerCase();
        var items = topics.filter(function (t) { return t.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
        items.forEach(function (t) {
          var it = document.createElement('div');
          it.className = 'suggest-item';
          it.textContent = t;
          it.addEventListener('mousedown', function () {
            searchInput.value = t;
            suggest.style.display = 'none';
          });
          suggest.appendChild(it);
        });
        suggest.style.display = items.length ? 'block' : 'none';
        if (items.length) positionSuggest();
      }
      searchInput.addEventListener('input', function () { renderSuggest(searchInput.value); });
      searchInput.addEventListener('focus', function () { renderSuggest(searchInput.value); positionSuggest(); });
      searchInput.addEventListener('blur', function () { setTimeout(function () { suggest.style.display = 'none'; }, 120); });
      window.addEventListener('resize', function () { if (suggest.style.display === 'block') positionSuggest(); });
    }

    var companies = document.querySelector('.companies-marquee');
    var companiesTrack = companies && companies.querySelector('.companies-track');
    if (companiesTrack && companiesTrack.dataset.loopDup !== 'true') {
      var original = '';
      companiesTrack.querySelectorAll('.company-badge').forEach(function (el) {
        original += el.outerHTML;
      });
      companiesTrack.innerHTML = original + original;
      companiesTrack.dataset.loopDup = 'true';
    }
    var brands = document.querySelector('.brands-marquee');
    var brandsTrack = brands && brands.querySelector('.brands-track');
    if (brandsTrack && brandsTrack.dataset.loopDup !== 'true') {
      var originalBrands = '';
      brandsTrack.querySelectorAll('.brand-badge').forEach(function (el) {
        originalBrands += el.outerHTML;
      });
      brandsTrack.innerHTML = originalBrands + originalBrands;
      brandsTrack.dataset.loopDup = 'true';
    }

    function openQuizModal(course) {
      // If user hasn't registered yet, start the idle prompt countdown again.
      startRegistrationPromptTimer();
      var modal = document.createElement('div');
      modal.className = 'quiz-modal';

      var card = document.createElement('div');
      card.className = 'quiz-card';

      var header = document.createElement('div');
      header.className = 'quiz-header';
      var title = document.createElement('div');
      title.className = 'quiz-title';
      title.textContent = (course && course.length) ? (course + ' — 10 MCQs') : 'Quick Skill Check — 10 MCQs';
      var timerEl = document.createElement('div');
      timerEl.className = 'quiz-timer';
      header.appendChild(title);
      header.appendChild(timerEl);

      var progress = document.createElement('div');
      progress.className = 'quiz-progress';
      var progressBar = document.createElement('div');
      progressBar.className = 'quiz-progress__bar';
      progress.appendChild(progressBar);

      var body = document.createElement('div');
      body.className = 'quiz-body';

      var footer = document.createElement('div');
      footer.className = 'quiz-footer';
      var closeBtn = document.createElement('button');
      closeBtn.className = 'quiz-close';
      closeBtn.textContent = 'Close';
      var submitBtn = document.createElement('button');
      submitBtn.className = 'btn btn-primary';
      submitBtn.textContent = 'Submit';
      submitBtn.style.display = 'none';
      footer.appendChild(closeBtn);
      footer.appendChild(submitBtn);

      card.appendChild(header);
      card.appendChild(progress);
      card.appendChild(body);
      card.appendChild(footer);
      modal.appendChild(card);
      document.body.appendChild(modal);

      var bank = {
        'Data Science Fundamentals': [
          { q: 'Which reduces overfitting?', opts: ['Using deeper models', 'Regularization', 'Increasing features blindly', 'Lower train/test split'], a: 1 },
          { q: 'Train/test split purpose?', opts: ['Faster training', 'Model evaluation', 'Data cleaning', 'Feature scaling'], a: 1 },
          { q: 'Normalization vs standardization?', opts: ['Same operation', 'Min-max vs z-score', 'Both z-score', 'Both min-max'], a: 1 },
          { q: 'Confusion matrix metric for imbalance?', opts: ['Accuracy', 'Precision', 'Recall', 'ROC AUC'], a: 3 },
          { q: 'K-fold cross-validation helps?', opts: ['Data leakage', 'Robust evaluation', 'Feature selection', 'GPU training'], a: 1 },
          { q: 'Pandas DataFrame is?', opts: ['Row-major array', '2D labeled data structure', 'Image tensor', 'Sparse matrix only'], a: 1 },
          { q: 'Feature scaling needed for?', opts: ['Tree models', 'Distance-based models', 'Naive Bayes', 'Rule-based models'], a: 1 },
          { q: 'Supervised learning example?', opts: ['K-means', 'Linear regression', 'PCA', 'DBSCAN'], a: 1 },
          { q: 'ROC curve plots?', opts: ['Precision vs Recall', 'TPR vs FPR', 'TP vs TN', 'Loss vs Epoch'], a: 1 },
          { q: 'Median is robust to?', opts: ['Outliers', 'Duplicates', 'Missing labels', 'Scaling'], a: 0 }
        ],
        'Java Basics': [
          { q: 'Entry point signature?', opts: ['public static void main(String[] args)', 'void main()', 'public void main()', 'static int main()'], a: 0 },
          { q: 'String comparison by content?', opts: ['==', 'equals()', 'compareTo()', 'hashCode()'], a: 1 },
          { q: 'Primitive type?', opts: ['String', 'Integer', 'int', 'BigDecimal'], a: 2 },
          { q: 'Access modifier most restrictive?', opts: ['public', 'protected', 'default', 'private'], a: 3 },
          { q: 'OOP pillars include?', opts: ['Encapsulation', 'Pointers', 'Macros', 'Preprocessing'], a: 0 },
          { q: 'ArrayList grows by?', opts: ['Fixed size', 'Dynamic resizing', 'Linked nodes', 'Stack frames'], a: 1 },
          { q: 'Interface can define?', opts: ['Concrete methods only', 'Constants and abstract methods', 'Constructors', 'Instance fields'], a: 1 },
          { q: 'finally block executes?', opts: ['Only on exception', 'Always if reached', 'Never', 'Only with return'], a: 1 },
          { q: 'JDK includes?', opts: ['Only JVM', 'JRE + tools', 'Only JRE', 'Only compiler'], a: 1 },
          { q: 'Package import keyword?', opts: ['include', 'using', 'import', 'require'], a: 2 }
        ],
        'Digital Marketing': [
          { q: 'On-page SEO critical?', opts: ['Title tag', 'Server RAM', 'CDN region', 'IP address'], a: 0 },
          { q: 'CTR stands for?', opts: ['Customer Time Rate', 'Click-Through Rate', 'Conversion Target Ratio', 'Content Timing Rank'], a: 1 },
          { q: 'UTM parameters used for?', opts: ['Tracking campaigns', 'Securing cookies', 'Improving SEO directly', 'Compressing images'], a: 0 },
          { q: 'Organic traffic is from?', opts: ['Paid ads', 'Search engines', 'Email only', 'Referral only'], a: 1 },
          { q: 'Keyword research tool?', opts: ['Photoshop', 'Google Keyword Planner', 'Excel', 'Figma'], a: 1 },
          { q: 'Conversion rate formula?', opts: ['Clicks/Sessions', 'Conversions/Visitors', 'Visitors/Conversions', 'Revenue/Impressions'], a: 1 },
          { q: 'Bounce rate is?', opts: ['Pages per session', 'Single-page sessions', 'Time on site', 'New visitors only'], a: 1 },
          { q: 'Meta description length ~?', opts: ['20–40 chars', '50–160 chars', '200–300 chars', 'Any length'], a: 1 },
          { q: 'Content marketing pillar?', opts: ['Cold calls', 'Blog posts', 'Server tuning', 'SSL config'], a: 1 },
          { q: 'Analytics tracks?', opts: ['Network latency', 'User behavior', 'Firmware updates', 'CPU cache'], a: 1 }
        ],
        'Excel for Analysis': [
          { q: 'Absolute reference example?', opts: ['A1', '$A$1', 'R1C1', 'A$1'], a: 1 },
          { q: 'PivotTable purpose?', opts: ['Styling cells', 'Summarize data', 'Chart only', 'Spell check'], a: 1 },
          { q: 'Lookup across columns?', opts: ['COUNTIF', 'VLOOKUP/XLOOKUP', 'SUM', 'LEFT'], a: 1 },
          { q: 'SUMIFS does?', opts: ['Sum with multiple criteria', 'Average values', 'Count cells', 'Join text'], a: 0 },
          { q: 'Remove duplicates located under?', opts: ['Data tab', 'Formulas tab', 'Review tab', 'View tab'], a: 0 },
          { q: 'Conditional Formatting helps?', opts: ['Sort rows', 'Highlight rules', 'Rename sheets', 'Protect cells'], a: 1 },
          { q: 'TEXT function does?', opts: ['Calculates sum', 'Formats numbers/dates as text', 'Creates charts', 'Imports CSV'], a: 1 },
          { q: 'Slicer used with?', opts: ['PivotTables', 'Macros', 'PowerPoint', 'Outlook'], a: 0 },
          { q: 'Concatenate text?', opts: ['CONCAT/&,', 'SUM', 'COUNT', 'ROUND'], a: 0 },
          { q: 'IF with AND example?', opts: ['IF(AND(A1>0,B1>0),1,0)', 'IFOR(A1,B1)', 'IFF(A1,B1)', 'IFX(A1,B1)'], a: 0 }
        ],
        'Python Basics': [
          { q: 'Immutable sequence?', opts: ['list', 'tuple', 'dict', 'set'], a: 1 },
          { q: 'Dict value by key?', opts: ['d.value(k)', 'd[k]', 'd.getKey(k)', 'd.item(k)'], a: 1 },
          { q: 'List comprehension creates?', opts: ['tuple', 'dict', 'list', 'set'], a: 2 },
          { q: 'PEP 8 relates to?', opts: ['Packaging', 'Style guide', 'Networking', 'Security'], a: 1 },
          { q: 'Virtual environment tool?', opts: ['pip', 'venv', 'make', 'npm'], a: 1 },
          { q: 'Slice last item?', opts: ['s[0]', 's[-1]', 's[1:]', 's[:-1]'], a: 1 },
          { q: 'Function definition?', opts: ['func my():', 'def my():', 'fn my():', 'function my():'], a: 1 },
          { q: 'Import math module?', opts: ['include math', 'require("math")', 'import math', 'use math'], a: 2 },
          { q: 'Handle exception?', opts: ['try/except', 'catch/throw', 'on error resume', 'panic'], a: 0 },
          { q: 'Variable args?', opts: ['args[]', '*args/**kwargs', 'argv', 'rest'], a: 1 }
        ],
        'SQL Essentials': [
          { q: 'PRIMARY KEY ensures?', opts: ['Nullability', 'Uniqueness', 'Text only', 'Foreign rows'], a: 1 },
          { q: 'INNER JOIN returns?', opts: ['All rows', 'Matching rows in both tables', 'Left table only', 'Right table only'], a: 1 },
          { q: 'GROUP BY used with?', opts: ['Window functions', 'Aggregates', 'DDL only', 'Constraints'], a: 1 },
          { q: 'WHERE vs HAVING?', opts: ['Both after GROUP BY', 'WHERE before, HAVING after', 'HAVING before WHERE', 'Same stage'], a: 1 },
          { q: 'Index helps?', opts: ['Speed reads', 'Speed writes only', 'Disable constraints', 'Increase size only'], a: 0 },
          { q: 'DELETE vs TRUNCATE?', opts: ['Same effect', 'TRUNCATE faster, no WHERE', 'DELETE alters schema', 'TRUNCATE logs row-by-row'], a: 1 },
          { q: 'FOREIGN KEY enforces?', opts: ['Referential integrity', 'Unique text', 'Not null', 'Auto increment'], a: 0 },
          { q: 'Check NULL?', opts: ['= NULL', 'IS NULL', '== NULL', 'EQUALS NULL'], a: 1 },
          { q: 'LIKE wildcard for any length?', opts: ['_', '%', '*', '#'], a: 1 },
          { q: 'Normalization aims to?', opts: ['Redundancy reduction', 'Query speed only', 'UI design', 'ETL scheduling'], a: 0 }
        ]
      };
      var fallback = [
        { q: 'Which HTTP method is idempotent?', opts: ['POST', 'PUT', 'PATCH', 'CONNECT'], a: 1 },
        { q: 'SQL: Which clause filters rows?', opts: ['SELECT', 'WHERE', 'ORDER BY', 'GROUP BY'], a: 1 },
        { q: 'JS: const x = []; typeof x ?', opts: ['array', 'object', 'list', 'map'], a: 1 },
        { q: 'Git: Create new branch and switch?', opts: ['git branch', 'git checkout -b', 'git switch', 'git init'], a: 1 },
        { q: 'CSS: Center with flexbox?', opts: ['justify-items: center', 'align: center', 'display: grid', 'justify-content: center; align-items: center'], a: 3 },
        { q: 'Python: List comprehension creates?', opts: ['tuple', 'dict', 'list', 'set'], a: 2 },
        { q: 'Security: Store secrets in?', opts: ['code', '.env', 'logs', 'README'], a: 1 },
        { q: 'API: 201 Created is for?', opts: ['Deletion', 'Creation', 'Validation error', 'Unauthorized'], a: 1 },
        { q: 'Data: CSV best for?', opts: ['Binary blobs', 'Tabular text data', 'Images', 'Compiled code'], a: 1 },
        { q: 'Testing: Unit tests focus on?', opts: ['Whole system', 'Single component', 'UI only', 'Network only'], a: 1 }
      ];
      var questions = bank[course] || fallback;

      var idx = 0;
      var score = 0;
      var selected = -1;
      var seconds = 600;
      var intervalId = 0;

      function fmt(n) {
        var m = Math.floor(n / 60);
        var s = n % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
      }
      timerEl.textContent = fmt(seconds);

      function tick() {
        seconds -= 1;
        if (seconds <= 0) {
          seconds = 0;
          finish();
        }
        timerEl.textContent = fmt(seconds);
      }
      intervalId = setInterval(tick, 1000);

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
        progressBar.style.width = (p * 100).toFixed(2) + '%';
      }

      function finish() {
        clearInterval(intervalId);
        try {
          var tok = localStorage.getItem('apiToken');
          if (tok && window.corsoApi && window.corsoApi.base) {
            var ct = (course != null && String(course).trim()) ? String(course).trim() : '';
            if (ct) {
              var apiBase = String(window.corsoApi.base).replace(/\/?$/, '');
              fetch(apiBase + '/quiz-attempts/log', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'Authorization': 'Bearer ' + tok
                },
                body: JSON.stringify({ course_title: ct, score: score, total: questions.length })
              }).catch(function () {});
            }
          }
        } catch (e) {}
        submitBtn.disabled = true;
        body.innerHTML = '';
        progressBar.style.width = '100%';
        var result = document.createElement('div');
        result.className = 'quiz-result';
        var scoreEl = document.createElement('div');
        scoreEl.className = 'quiz-score';
        var pct = Math.round((score / questions.length) * 100);
        scoreEl.textContent = 'Score: ' + score + '/' + questions.length + ' (' + pct + '%)';
        var note = document.createElement('div');
        note.className = 'quiz-note';
        note.textContent = pct >= 60
          ? 'Enter your name, then complete payment to unlock your certificate.'
          : 'Minimum 60% required to proceed to payment. Please retake the test.';
        result.appendChild(scoreEl);
        result.appendChild(note);
        body.appendChild(result);

        if (pct >= 60) {
          var form = document.createElement('div');
          form.className = 'cert-form';
          var nameInput = document.createElement('input');
          nameInput.type = 'text';
          nameInput.placeholder = 'Your full name';
          try {
            var pendingName = localStorage.getItem('pendingUserName') || '';
            if (pendingName) nameInput.value = pendingName;
          } catch (e) {}
          var payBtn = document.createElement('button');
          payBtn.className = 'btn btn-primary';
          payBtn.textContent = 'Proceed to Payment';
          var share = document.createElement('div');
          share.className = 'cert-share';
          form.appendChild(nameInput);
          form.appendChild(payBtn);
          result.appendChild(form);
          result.appendChild(share);
          var paymentOpen = false;
          payBtn.addEventListener('click', function () {
            if (paymentOpen) return;
            var name = (nameInput.value || '').trim();
            if (!name) {
              alert('Please enter your full name to proceed.');
              nameInput.focus();
              return;
            }
            paymentOpen = true;
            var pay = document.createElement('div');
            pay.className = 'cert-form';
            var razorpayBtn = document.createElement('button');
            razorpayBtn.className = 'btn btn-primary';
            razorpayBtn.textContent = 'Pay with Razorpay';
            pay.appendChild(razorpayBtn);
            var payHint = document.createElement('p');
            payHint.style.fontSize = '0.85rem';
            payHint.style.color = 'var(--text-muted, rgba(255,255,255,0.55))';
            payHint.style.marginTop = '10px';
            payHint.style.lineHeight = '1.45';
            payHint.textContent = 'If you see "International cards are not supported", choose Netbanking or UPI on the Razorpay screen, or enable international card payments in Razorpay Dashboard > Settings > International payments.';
            pay.appendChild(payHint);
            result.appendChild(pay);
            razorpayBtn.addEventListener('click', function () {
              var pendingEmail = localStorage.getItem('pendingUserEmail') || '';
              var pendingToken = localStorage.getItem('pendingActivationToken') || '';
              if (!pendingEmail || !pendingToken) {
                registrationPromptShown = false;
                showRegistrationModal();
                setTimeout(function() {
                  alert('Please complete registration before paying. Enter your details in the form that appeared.');
                }, 100);
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
                      key: orderData.key_id,
                      amount: orderData.amount,
                      currency: orderData.currency,
                      order_id: orderData.order_id,
                      name: 'Corso E-Learning',
                      description: 'Account activation',
                      prefill: { email: pendingEmail, name: name },
                      handler: function (response) {
                        razorpayBtn.disabled = true;
                        razorpayBtn.textContent = 'Activating...';
                        window.corsoApi.post('/payments/razorpay/verify', {
                          razorpay_payment_id: response.razorpay_payment_id,
                          razorpay_order_id: response.razorpay_order_id,
                          razorpay_signature: response.razorpay_signature,
                          email: pendingEmail,
                          activation_token: pendingToken,
                          course_name: course || '',
                          quiz_score: score,
                          quiz_total: questions.length
                        }).then(function (r) {
                          return r.json().then(function (data) {
                            if (!r.ok) {
                              var errMsg = (data && data.error) ? data.error : ('Activation failed (HTTP ' + r.status + ')');
                              throw new Error(errMsg);
                            }
                            return data;
                          });
                        }).then(function (data) {
                          localStorage.removeItem('pendingActivationToken');
                          localStorage.removeItem('pendingUserEmail');
                          localStorage.removeItem('pendingUserName');

                          if (data && data.email_sent) {
                            showPostPaymentModal({ emailSent: true, email: pendingEmail });
                          } else {
                            try {
                              localStorage.removeItem('tempUserEmail');
                              localStorage.removeItem('tempUserPassword');
                            } catch (err) {}
                            if (data && data.temp_password) {
                              try {
                                localStorage.setItem('tempUserEmail', pendingEmail);
                                localStorage.setItem('tempUserPassword', data.temp_password);
                              } catch (err2) {}
                            }
                            showPostPaymentModal({
                              emailSent: false,
                              email: pendingEmail,
                              tempPassword: data && data.temp_password,
                              emailError: data && data.email_error,
                              tempLoginUrl: data && data.temp_login_url,
                              normalLoginUrl: data && data.normal_login_url
                            });
                          }

                          // Auto-download certificate PDF from backend
                          if (data && data.certificate_download_url) {
                            var certLink = document.createElement('a');
                            certLink.href = data.certificate_download_url;
                            certLink.download = 'corso-certificate-' + (data.certificate_number || 'cert') + '.pdf';
                            document.body.appendChild(certLink);
                            certLink.click();
                            document.body.removeChild(certLink);
                          }

                          // Store certificate info in localStorage for my-certificates page
                          var cert = {
                            id: data.certificate_id || 0,
                            name: name,
                            score: score,
                            total: questions.length,
                            ts: Date.now(),
                            course: (course || 'General'),
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
                    rzp.on('payment.failed', function (resp) {
                      razorpayBtn.disabled = false;
                      razorpayBtn.textContent = 'Pay with Razorpay';
                      var err = resp && resp.error ? resp.error : {};
                      var meta = err.metadata || {};
                      var d = err.description || 'Payment failed';
                      if (window.corsoApi && window.corsoApi.base && pendingEmail) {
                        window.corsoApi.post('/payments/razorpay/payment-failed', {
                          email: pendingEmail,
                          activation_token: pendingToken || '',
                          razorpay_order_id: meta.order_id || currentRzpOrderId || '',
                          razorpay_payment_id: meta.payment_id || '',
                          course_name: course || '',
                          error_code: err.code || '',
                          error_description: d
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
          var retry = document.createElement('button');
          retry.className = 'btn btn-outline';
          retry.textContent = 'Retake Test';
          result.appendChild(retry);
          retry.addEventListener('click', function () {
            idx = 0;
            score = 0;
            selected = -1;
            seconds = 600;
            if (intervalId) clearInterval(intervalId);
            intervalId = setInterval(tick, 1000);
            timerEl.textContent = fmt(seconds);
            submitBtn.disabled = false;
            body.innerHTML = '';
            progressBar.style.width = '0%';
            updateProgress();
            renderQuestion();
          });
        }
      }

      function drawCertificate(c, name, score, total, course) {
        var ctx = c.getContext('2d');
        var gr = ctx.createLinearGradient(0, 0, c.width, c.height);
        gr.addColorStop(0, '#0b1220');
        gr.addColorStop(1, '#0a0e17');
        ctx.fillStyle = gr;
        ctx.fillRect(0, 0, c.width, c.height);
        ctx.strokeStyle = '#06b6d4';
        ctx.lineWidth = 6;
        roundRect(ctx, 22, 22, c.width - 44, c.height - 44, 22, false, true);
        ctx.strokeStyle = 'rgba(148,163,184,0.35)';
        ctx.lineWidth = 2;
        ctx.setLineDash([10, 8]);
        roundRect(ctx, 42, 42, c.width - 84, c.height - 84, 18, false, true);
        ctx.setLineDash([]);
        ctx.fillStyle = '#f1f5f9';
        ctx.font = '800 44px Outfit, system-ui, sans-serif';
        centerText(ctx, 'Certificate of Completion', c.width / 2, 140);
        ctx.font = '600 20px DM Sans, system-ui, sans-serif';
        ctx.fillStyle = '#94a3b8';
        centerText(ctx, 'This is to certify that', c.width / 2, 200);
        ctx.font = '800 40px Outfit, system-ui, sans-serif';
        ctx.fillStyle = '#ffffff';
        centerText(ctx, name, c.width / 2, 270);
        ctx.font = '600 20px DM Sans, system-ui, sans-serif';
        ctx.fillStyle = '#94a3b8';
        centerText(ctx, 'has successfully completed', c.width / 2, 330);
        ctx.font = '800 28px Outfit, system-ui, sans-serif';
        ctx.fillStyle = '#06b6d4';
        var pctTxt = Math.round((score / total) * 100) + '%';
        centerText(ctx, ((course && course.length) ? (course + ' Skill Check') : 'Quick Skill Check') + ' (' + pctTxt + ')', c.width / 2, 390);
        ctx.font = '700 22px Outfit, system-ui, sans-serif';
        ctx.fillStyle = '#94a3b8';
        centerText(ctx, 'Corso E-Learning', c.width / 2, 480);
        var grad = ctx.createLinearGradient(330, 498, 470, 506);
        grad.addColorStop(0, '#06b6d4');
        grad.addColorStop(1, '#67e8f9');
        ctx.fillStyle = grad;
        ctx.roundRect(330, 498, 140, 8, 4);
        ctx.fill();
      }

      function roundRect(ctx, x, y, w, h, r, fill, stroke) {
        if (w < 2 * r) r = w / 2;
        if (h < 2 * r) r = h / 2;
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
        if (fill) ctx.fill();
        if (stroke) ctx.stroke();
      }

      function centerText(ctx, text, x, y) {
        var m = ctx.measureText(text);
        ctx.fillText(text, x - m.width / 2, y);
      }

      closeBtn.addEventListener('click', function () {
        clearInterval(intervalId);
        modal.classList.remove('is-open');
        document.body.removeChild(modal);
      });
      submitBtn.addEventListener('click', finish);
      modal.classList.add('is-open');
      updateProgress();
      renderQuestion();
    }

    var userNavMyCerts = document.querySelector('.nav-mycerts');
    var loginLink = document.querySelector('a[href="login.php"]');
    var profileBtn = document.querySelector('.nav-profile');
    var profileMenu = document.querySelector('.nav-profile-menu');
    var logoutBtn = document.querySelector('.nav-logout');
    var navName = document.querySelector('.nav-name');
    (function updateAuthNav() {
      var user = null;
      try { user = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
      var logged = !!(user && user.email);
      if (userNavMyCerts) userNavMyCerts.style.display = logged ? 'inline-block' : 'none';
      if (loginLink) loginLink.style.display = logged ? 'none' : 'inline-block';
      if (profileBtn) profileBtn.style.display = logged ? 'inline-flex' : 'none';
      if (navName) navName.textContent = logged ? (user.name || user.email.split('@')[0]) : 'Profile';
      var role = (user && user.role) ? String(user.role) : '';
      var isAdmin = logged && (role === 'admin' || role === 'hr' || role === 'super_admin' || user.isAdmin === true);
      var dashboardLink = profileMenu && profileMenu.querySelector('.nav-dashboard-link');
      var adminLink = profileMenu && profileMenu.querySelector('.nav-admin-link');
      if (dashboardLink) dashboardLink.style.display = isAdmin ? 'none' : '';
      if (adminLink) adminLink.style.display = isAdmin ? '' : 'none';
      if (profileMenu) profileMenu.hidden = true;
    })();
    if (profileBtn && profileMenu) {
      function openProfileMenu() {
        var navEl = document.querySelector('.nav');
        var navRect = navEl.getBoundingClientRect();
        var btnRect = profileBtn.getBoundingClientRect();
        var left = btnRect.left - navRect.left;
        var top = (btnRect.bottom - navRect.top) + 8;
        profileMenu.style.left = left + 'px';
        profileMenu.style.top = top + 'px';
        profileBtn.setAttribute('aria-expanded', 'true');
        profileMenu.hidden = false;
      }
      function closeProfileMenu() {
        profileMenu.hidden = true;
        profileBtn.setAttribute('aria-expanded', 'false');
      }
      profileBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        var expanded = profileBtn.getAttribute('aria-expanded') === 'true';
        if (expanded) closeProfileMenu(); else openProfileMenu();
      });
      document.addEventListener('click', function (e) {
        if (!profileMenu.hidden && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
          closeProfileMenu();
        }
      });
      document.addEventListener('pointerdown', function (e) {
        if (!profileMenu.hidden && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
          closeProfileMenu();
        }
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
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () {
        localStorage.removeItem('sessionUser');
        localStorage.removeItem('pendingActivationToken');
        localStorage.removeItem('pendingUserEmail');
        localStorage.removeItem('pendingUserName');
        localStorage.removeItem('tempUserEmail');
        localStorage.removeItem('tempUserPassword');
        if (profileMenu) profileMenu.hidden = true;
        if (profileBtn) profileBtn.setAttribute('aria-expanded', 'false');
        // Restart registration timer for next visit
        registrationPromptShown = false;
        startRegistrationPromptTimer();
        location.href = 'index.php';
      });
    }
    var themeCheckbox = document.querySelector('.dash-theme-checkbox');
    var themeThumb = document.querySelector('.dash-theme-thumb');
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

    var dash = document.querySelector('.dashboard');
    if (dash) {
      var u = null;
      try { u = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
      var nameEl = document.querySelector('.dash-user');
      if (nameEl) {
        var n = u && (u.name || (u.email || '').split('@')[0]) || 'User';
        nameEl.textContent = n;
      }
      var statsCerts = [];
      try { statsCerts = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
      if (!Array.isArray(statsCerts)) statsCerts = [];
      var totalCerts = statsCerts.length;
      var passedCount = statsCerts.filter(function (c) { var pct = (c.score / (c.total || 10)) * 100; return pct >= 60; }).length;
      var avgScore = totalCerts ? Math.round(statsCerts.reduce(function (sum, c) { return sum + (c.score / (c.total || 10)) * 100; }, 0) / totalCerts) : 0;
      var totalEl = document.querySelector('.dash-stat-value[data-stat="total"]');
      var averageEl = document.querySelector('.dash-stat-value[data-stat="average"]');
      var passedEl = document.querySelector('.dash-stat-value[data-stat="passed"]');
      if (totalEl) totalEl.textContent = totalCerts;
      if (averageEl) averageEl.textContent = avgScore + '%';
      if (passedEl) passedEl.textContent = passedCount;
      var certsList = document.querySelector('.dash-certs');
      if (certsList) {
        var clist = [];
        try { clist = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
        if (!Array.isArray(clist)) clist = [];
        clist = clist.slice().sort(function(a,b){ return (b.ts||0)-(a.ts||0); }).slice(0,3);
        if (!clist.length) {
          var none = document.createElement('p');
          none.textContent = 'No certificates yet.';
          certsList.parentNode.replaceChild(none, certsList);
        } else {
          clist.forEach(function (c) {
            var li = document.createElement('li');
            var badge = document.createElement('span');
            badge.className = 'badge badge-user';
            var initials = (c.name || 'SC').split(' ').map(function(w){return w[0];}).join('').slice(0,2).toUpperCase();
            badge.textContent = initials;
            var center = document.createElement('div');
            var title = document.createElement('strong');
            title.textContent = c.name;
            var meta = document.createElement('div');
            meta.className = 'meta';
            var issuedDate = c.issued_at ? new Date(c.issued_at) : new Date(c.ts || Date.now());
            meta.textContent = 'Issued ' + issuedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            center.appendChild(title); center.appendChild(meta);
            var right = document.createElement('a');
            right.className = 'dash-link';
            right.href = 'verify.php?id=' + encodeURIComponent(c.id);
            right.textContent = 'View';
            li.appendChild(badge); li.appendChild(center); li.appendChild(right);
            certsList.appendChild(li);
          });
        }
      }
      var monthEl = document.querySelector('.dash-month');
      var calEl = document.querySelector('.dash-calendar');
      if (monthEl && calEl) {
        var now = new Date();
        var formatter = new Intl.DateTimeFormat('en', { month: 'short', year: 'numeric' });
        monthEl.textContent = formatter.format(now);
        var first = new Date(now.getFullYear(), now.getMonth(), 1);
        var startIdx = first.getDay();
        var days = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
        var labels = ['Mo','Tu','We','Th','Fr','Sa','Su'];
        labels.forEach(function (l) {
          var d = document.createElement('div'); d.className = 'day'; d.textContent = l; calEl.appendChild(d);
        });
        for (var k = 0; k < startIdx; k++) {
          var pad = document.createElement('div'); pad.className = 'day'; pad.textContent = ''; calEl.appendChild(pad);
        }
        for (var dnum = 1; dnum <= days; dnum++) {
          var dcell = document.createElement('div'); dcell.className = 'day'; dcell.textContent = dnum; calEl.appendChild(dcell);
        }
      }
      var dashCalDays = document.getElementById('dash-calendar-days');
      var dashCalMonth = document.getElementById('dash-calendar-month');
      var dashCalNav = document.querySelector('.dash-calendar-nav');
      if (dashCalDays && dashCalMonth) {
        var calState = { year: new Date().getFullYear(), month: new Date().getMonth() };
        var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        function renderDashCalendar() {
          var d = new Date(calState.year, calState.month, 1);
          var today = new Date();
          dashCalMonth.textContent = monthNames[calState.month] + ' ' + d.getDate() + ' ' + dayNames[d.getDay()];
          var first = new Date(calState.year, calState.month, 1);
          var startIdx = first.getDay();
          var daysInMonth = new Date(calState.year, calState.month + 1, 0).getDate();
          dashCalDays.innerHTML = '';
          for (var k = 0; k < startIdx; k++) {
            var pad = document.createElement('span'); pad.className = 'other'; pad.textContent = ''; dashCalDays.appendChild(pad);
          }
          for (var dnum = 1; dnum <= daysInMonth; dnum++) {
            var span = document.createElement('span');
            if (calState.year === today.getFullYear() && calState.month === today.getMonth() && dnum === today.getDate()) span.className = 'today';
            else span.className = '';
            span.textContent = dnum;
            dashCalDays.appendChild(span);
          }
        }
        renderDashCalendar();
        if (dashCalNav) {
          var prev = dashCalNav.querySelector('button[aria-label="Previous month"]');
          var next = dashCalNav.querySelector('button[aria-label="Next month"]');
          if (prev) prev.addEventListener('click', function () {
            calState.month--;
            if (calState.month < 0) { calState.month = 11; calState.year--; }
            renderDashCalendar();
          });
          if (next) next.addEventListener('click', function () {
            calState.month++;
            if (calState.month > 11) { calState.month = 0; calState.year++; }
            renderDashCalendar();
          });
        }
      }
      var signout = document.querySelector('.dash-signout');
      if (signout) {
        signout.addEventListener('click', function () {
          localStorage.removeItem('sessionUser');
          localStorage.removeItem('pendingActivationToken');
          localStorage.removeItem('pendingUserEmail');
          localStorage.removeItem('pendingUserName');
          localStorage.removeItem('tempUserEmail');
          localStorage.removeItem('tempUserPassword');
          registrationPromptShown = false;
          location.href = 'index.php';
        });
      }
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
