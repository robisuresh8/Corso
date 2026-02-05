(function () {
  function init() {
    document.documentElement.classList.add('js');
    var menuToggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav');
    var header = document.querySelector('.header');
    var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var rafId = 0;

    // Mobile menu handled via hamburger toggle; no select dropdown

    var isHome = !!document.querySelector('.hero');
    if (!isHome && nav && !nav.querySelector('.nav-home')) {
      var homeLink = document.createElement('a');
      homeLink.href = 'index.html';
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
        btn.addEventListener('click', function () { openQuizModal(); });
      });
    }
    bindSkillCheckButtons();

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
        openQuizModal();
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

    function openQuizModal() {
      var modal = document.createElement('div');
      modal.className = 'quiz-modal';

      var card = document.createElement('div');
      card.className = 'quiz-card';

      var header = document.createElement('div');
      header.className = 'quiz-header';
      var title = document.createElement('div');
      title.className = 'quiz-title';
      title.textContent = 'Quick Skill Check — 10 MCQs';
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
      footer.appendChild(closeBtn);
      footer.appendChild(submitBtn);

      card.appendChild(header);
      card.appendChild(progress);
      card.appendChild(body);
      card.appendChild(footer);
      modal.appendChild(card);
      document.body.appendChild(modal);

      var questions = [
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
        var nextBtn = document.createElement('button');
        nextBtn.className = 'btn btn-outline';
        nextBtn.textContent = idx < questions.length - 1 ? 'Next' : 'Finish';
        nextBtn.addEventListener('click', function () {
          if (selected === -1) return;
          if (selected === questions[idx].a) score += 1;
          idx += 1;
          updateProgress();
          if (idx < questions.length) renderQuestion();
          else finish();
        });
        actions.appendChild(nextBtn);
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
            if (!name) return;
            paymentOpen = true;
            var pay = document.createElement('div');
            pay.className = 'cert-form';
            var card = document.createElement('input');
            card.type = 'text';
            card.placeholder = 'Card number';
            var cvv = document.createElement('input');
            cvv.type = 'text';
            cvv.placeholder = 'CVV';
            var payNow = document.createElement('button');
            payNow.className = 'btn btn-primary';
            payNow.textContent = 'Pay Now';
            pay.appendChild(card);
            pay.appendChild(cvv);
            pay.appendChild(payNow);
            result.appendChild(pay);
            payNow.addEventListener('click', function () {
              var idArr = new Uint8Array(8);
              if (window.crypto && window.crypto.getRandomValues) window.crypto.getRandomValues(idArr);
              var id = Array.prototype.map.call(idArr, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
              var cert = { id: id, name: name, score: score, total: questions.length, ts: Date.now() };
              var list = [];
              try { list = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
              list.push(cert);
              localStorage.setItem('certificates', JSON.stringify(list));
              var canvas = document.createElement('canvas');
              canvas.width = 800;
              canvas.height = 560;
              drawCertificate(canvas, name, score, questions.length);
              var url = canvas.toDataURL('image/png');
              var a = document.createElement('a');
              a.href = url;
              a.download = 'corso-certificate-' + id + '.png';
              a.click();
              var link = (location.origin || '') + '/public/verify.html?id=' + encodeURIComponent(id);
              share.textContent = 'Verification link: ' + link;
              payNow.disabled = true;
              payNow.textContent = 'Paid';
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

      function drawCertificate(c, name, score, total) {
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
        centerText(ctx, 'Quick Skill Check (' + Math.round((score / total) * 100) + '%)', c.width / 2, 390);
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
    var loginLink = document.querySelector('a[href="login.html"]');
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
      if (profileBtn) profileBtn.display = logged ? 'inline-flex' : 'none';
      if (navName) navName.textContent = logged ? (user.name || user.email.split('@')[0]) : 'Profile';
      if (profileMenu) profileMenu.hidden = true;
    })();
    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', function () {
        var expanded = profileBtn.getAttribute('aria-expanded') === 'true';
        var navEl = document.querySelector('.nav');
        if (!expanded) {
          var navRect = navEl.getBoundingClientRect();
          var btnRect = profileBtn.getBoundingClientRect();
          var left = btnRect.left - navRect.left;
          var top = (btnRect.bottom - navRect.top) + 8;
          profileMenu.style.left = left + 'px';
          profileMenu.style.top = top + 'px';
        }
        profileBtn.setAttribute('aria-expanded', (!expanded).toString());
        profileMenu.hidden = expanded;
      });
      document.addEventListener('click', function (e) {
        if (!profileMenu.hidden && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
          profileMenu.hidden = true;
          profileBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () {
        localStorage.removeItem('sessionUser');
        if (profileMenu) profileMenu.hidden = true;
        if (profileBtn) profileBtn.setAttribute('aria-expanded', 'false');
        location.href = 'index.html';
      });
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
