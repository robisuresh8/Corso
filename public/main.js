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
        btn.addEventListener('click', function () {
          var target = document.getElementById('assessments');
          if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      });
    }
    bindSkillCheckButtons();
    (function initCourseSkillButtons() {
      document.querySelectorAll('.assessments .course-card').forEach(function (card) {
        var body = card.querySelector('.course-card-body') || card;
        var titleEl = card.querySelector('h3');
        var name = titleEl ? (titleEl.textContent || '').trim() : '';
        if (!name) return;
        var btn = document.createElement('button');
        btn.className = 'btn btn-outline course-test-btn';
        btn.textContent = 'Start Skill Test';
        btn.dataset.course = name;
        body.appendChild(btn);
        btn.addEventListener('click', function () {
          openQuizModal(name);
        });
      });
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
              var cert = { id: id, name: name, score: score, total: questions.length, ts: Date.now(), course: (course || 'General') };
              var list = [];
              try { list = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
              list.push(cert);
              localStorage.setItem('certificates', JSON.stringify(list));
              var canvas = document.createElement('canvas');
              canvas.width = 800;
              canvas.height = 560;
              drawCertificate(canvas, name, score, questions.length, course);
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
      if (profileBtn) profileBtn.style.display = logged ? 'inline-flex' : 'none';
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
