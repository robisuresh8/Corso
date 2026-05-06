<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Certificate — Corso E‑Learning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>?v=2" />
</head>
<body>
  <header class="header">
    <div class="container header-inner">
      <a href="<?= base_url('/') ?>" class="logo">Corso E‑Learning</a>
      <nav class="nav">
        <a href="<?= base_url('/') ?>#features">Features</a>
        <a href="<?= base_url('/') ?>#learners">Learners</a>
        
        <a href="<?= base_url('verify') ?>">Verify Certificate</a>
        <a class="nav-bell" href="<?= base_url('dashboard') ?>#notifications" aria-label="Notifications">🔔</a>
        <button class="nav-profile" aria-haspopup="true" aria-expanded="false"><span>👤</span><span class="nav-name">Profile</span></button>
        <div class="nav-profile-menu" hidden>
          <a class="btn btn-outline nav-dashboard-link" href="<?= base_url('dashboard') ?>">Dashboard</a>
          <a class="btn btn-outline nav-admin-link" href="<?= base_url('admin') ?>" style="display: none;">Admin Panel</a>
          <button class="btn btn-outline nav-logout">Logout</button>
        </div>
      </nav>
      <button class="menu-toggle" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <main class="verify-page">
    <section class="page-hero">
      <div class="container">
        <div class="verify-hero-icon" aria-hidden="true">🔒</div>
        <h1 class="page-title">Verify Certificate</h1>
        <p class="page-subtitle">Enter the certificate ID from the verification link to confirm authenticity.</p>
      </div>
    </section>
    <section class="content-block verify-content">
      <div class="container verify-container">
        <div class="verify-card">
          <div class="verify-card-inner">
            <label for="id-input" class="verify-label">Certificate ID</label>
            <div class="verify-input-row">
              <input id="id-input" class="input verify-input" type="text" placeholder="Paste or type certificate ID" aria-label="Certificate ID" />
              <button id="id-verify-btn" class="btn btn-primary verify-btn">Verify</button>
            </div>
            <p class="verify-hint" id="id-help">Use the ID from the certificate or the verification link shared with you.</p>
          </div>
        </div>
        <div id="verify-result-wrap" class="verify-result-wrap" hidden>
          <div class="verify-result-header">
            <span class="verify-result-badge" aria-hidden="true">✓</span>
            <h2 class="verify-result-title">Certificate verified</h2>
          </div>
          <div id="verify-result-card" class="verify-result-card"></div>
        </div>
        <p id="verify-info" class="verify-error" role="alert"></p>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-gradient"></div>
    <div class="container footer-inner">
      <div class="footer-brand">
        <div class="brand-mark">Corso E‑Learning</div>
        <p class="brand-copy">Fast, focused skill checks with instant, verifiable certificates.</p>
        <form class="newsletter" onsubmit="return false;">
          <input type="email" class="newsletter-input" placeholder="Your email" aria-label="Your email" />
          <button class="btn btn-primary newsletter-btn">Subscribe</button>
        </form>
      </div>
      <nav class="footer-links">
        <div class="footer-col">
          <h4>Explore</h4>
          <a href="<?= base_url('/') ?>#assessments">Popular Assessments</a>
          <a href="<?= base_url('/') ?>#how">How It Works</a>
        </div>
        <div class="footer-col">
          <h4>Follow</h4>
          <a href="#" aria-label="LinkedIn">LinkedIn</a>
          <a href="#" aria-label="Twitter">Twitter</a>
          <a href="#" aria-label="YouTube">YouTube</a>
        </div>
      </nav>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <p>© 2026 ICTRD • All rights reserved</p>
      </div>
    </div>
  </footer>
  <script>window.CORSO_API_BASE='<?= base_url('api') ?>';</script>
  <script src="<?= base_url('assets/js/main.js') ?>?v=2"></script>
  <script>
    (function () {
      var params = new URLSearchParams(location.search);
      var info = document.getElementById('verify-info');
      var idInput = document.getElementById('id-input');
      var idBtn = document.getElementById('id-verify-btn');
      var list = [];
      try { list = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
      var resultWrap = document.getElementById('verify-result-wrap');
      var resultCard = document.getElementById('verify-result-card');
      function render(cert) {
        var pct = Math.round((cert.score / cert.total) * 100);
        var d = new Date(cert.ts || cert.issued_at || Date.now());
        var dateStr = d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        var link = (location.origin || '') + location.pathname + '?id=' + encodeURIComponent(cert.id);
        if (resultWrap && resultCard) {
          resultWrap.hidden = false;
          resultCard.innerHTML =
            '<div class="verify-result-row"><span class="verify-result-label">Certificate ID</span><span class="verify-result-value">' + (cert.id || '—') + '</span></div>' +
            '<div class="verify-result-row"><span class="verify-result-label">Name</span><span class="verify-result-value">' + (cert.name || '—') + '</span></div>' +
            '<div class="verify-result-row"><span class="verify-result-label">Course</span><span class="verify-result-value">' + (cert.course || '—') + '</span></div>' +
            '<div class="verify-result-row"><span class="verify-result-label">Score</span><span class="verify-result-value">' + pct + '%</span></div>' +
            '<div class="verify-result-row"><span class="verify-result-label">Status</span><span class="verify-result-value verify-result-valid">Valid</span></div>' +
            '<div class="verify-result-row"><span class="verify-result-label">Issued</span><span class="verify-result-value">' + dateStr + '</span></div>' +
            '<div class="verify-result-actions"><button type="button" class="btn btn-outline verify-copy-btn">Copy verification link</button></div>';
          var copyBtn = resultCard.querySelector('.verify-copy-btn');
          if (copyBtn) {
            copyBtn.addEventListener('click', function () {
              try {
                navigator.clipboard.writeText(link);
                copyBtn.textContent = 'Copied';
              } catch (e) {
                var ta = document.createElement('textarea');
                ta.value = link;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e2) {}
                document.body.removeChild(ta);
                copyBtn.textContent = 'Copied';
              }
            });
          }
        }
        info.innerHTML = '';
      }
      function verifyById(id) {
        var useApi = window.corsoApi && window.corsoApi.base;
        if (useApi) {
          window.corsoApi.get('/certificates/verify?id=' + encodeURIComponent(id))
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (data) {
              var cert = { id: data.id, name: data.name, course: data.course, score: data.score, total: data.total, issued_at: data.issued_at };
              render(cert);
            })
            .catch(function () {
              if (resultWrap) resultWrap.hidden = true;
              var cert = list.find(function (c) { return String(c.id).trim() === String(id).trim(); });
              if (!cert) { info.textContent = 'Invalid or unavailable certificate ID.'; return; }
              render(cert);
            });
        } else {
          var cert = list.find(function (c) { return String(c.id).trim() === String(id).trim(); });
          if (!cert) {
            if (resultWrap) resultWrap.hidden = true;
            info.textContent = 'Invalid or unavailable certificate ID.';
            return;
          }
          render(cert);
        }
      }
      var qpId = params.get('id');
      if (qpId) verifyById(qpId);
      idBtn.addEventListener('click', function () {
        if (resultWrap) resultWrap.hidden = true;
        info.textContent = '';
        verifyById(idInput.value);
      });
      idInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          verifyById(idInput.value);
        }
      });
    })();
  </script>
</body>
</html>
