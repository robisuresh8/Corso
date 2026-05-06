<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Certificates — Corso E‑Learning</title>
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

  <main>
    <section class="page-hero">
      <div class="container">
        <h1 class="page-title">My Certificates</h1>
        <p class="page-subtitle">View, download, and share your certificates with a verification link.</p>
      </div>
    </section>
    <section class="content-block">
      <div class="container">
        <div id="cert-list"></div>
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
      var user = null;
      try { user = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
      if (!(user && user.email)) { location.href = '<?= base_url('user-login') ?>'; return; }
      var container = document.getElementById('cert-list');
      function renderList(list) {
        if (!list || !list.length) {
          var empty = document.createElement('p');
          empty.textContent = 'No certificates yet. Take a quick skill check to generate one.';
          container.appendChild(empty);
          return;
        }
        var host = '<?= base_url('verify') ?>?id=';
        var grid = document.createElement('div');
        grid.className = 'assessments-grid';
        list.slice().reverse().forEach(function (c) {
          var total = Number(c.total || 0);
          var score = Number(c.score || 0);
          var pct = total > 0 ? Math.round((score / total) * 100) : null;
          var certName = c.name || c.user_name || 'Certificate';
          var certCourse = c.course || c.course_name || c.course_title || 'General';
          var card = document.createElement('article');
          card.className = 'feature-card';
          var title = document.createElement('h3');
          title.textContent = certName + ' — ' + (pct === null ? 'Issued' : (pct + '%'));
          var info = document.createElement('p');
          var d = new Date(c.ts || c.issued_at || Date.now());
          info.textContent = certCourse + ' • ID ' + c.id + ' • ' + new Date(d).toLocaleString();
          var actions = document.createElement('div');
          actions.className = 'subpage-buttons';
          var view = document.createElement('button');
          view.className = 'btn btn-outline';
          view.textContent = 'Download';
          view.addEventListener('click', function() {
            var token = localStorage.getItem('apiToken');
            if (!token) {
              alert('Please login to download');
              return;
            }
            // Fetch with JWT token
            fetch('<?= base_url('student/certificates') ?>/' + encodeURIComponent(c.id) + '/download', {
              headers: { 'Authorization': 'Bearer ' + token }
            })
            .then(function(r) {
              if (!r.ok) throw new Error('Download failed: ' + r.status);
              return r.blob();
            })
            .then(function(blob) {
              var url = window.URL.createObjectURL(blob);
              var a = document.createElement('a');
              a.href = url;
              a.download = 'corso-certificate-' + (c.certificate_number || c.id) + '.pdf';
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              window.URL.revokeObjectURL(url);
            })
            .catch(function(e) {
              alert('Download failed: ' + e.message);
            });
          });
          var share = document.createElement('button');
          share.className = 'btn btn-outline';
          share.textContent = 'Copy link';
          share.addEventListener('click', function () {
            var link = host + encodeURIComponent(c.id);
            navigator.clipboard && navigator.clipboard.writeText(link);
          });
          actions.appendChild(view);
          actions.appendChild(share);
          card.appendChild(title);
          card.appendChild(info);
          card.appendChild(actions);
          grid.appendChild(card);
        });
        container.appendChild(grid);
      }
      if (window.corsoApi && window.corsoApi.base) {
        var token = localStorage.getItem('apiToken');
        if (!token) {
          console.error('No authentication token found. Please login first.');
          var err = document.createElement('p');
          err.style.color = '#f87171';
          err.textContent = 'Please login to view your certificates.';
          container.appendChild(err);
          var loginBtn = document.createElement('a');
          loginBtn.className = 'btn btn-primary';
          loginBtn.href = '<?= base_url('user-login') ?>';
          loginBtn.textContent = 'Go to Login';
          loginBtn.style.marginTop = '10px';
          loginBtn.style.display = 'inline-block';
          container.appendChild(loginBtn);
          return;
        }
        window.corsoApi.get('/certificates')
          .then(function (r) { 
            if (!r.ok) {
              return r.json().then(function(errData) {
                throw new Error(errData.error || 'Failed to fetch certificates');
              });
            }
            return r.json(); 
          })
          .then(function (rows) {
            var mapped = (rows || []).map(function (r) {
              return {
                id: r.id,
                name: r.name || r.user_name || '',
                course: r.course || r.course_name || r.course_title || '',
                score: Number(r.score || 0),
                total: Number(r.total || 0),
                issued_at: r.issued_at || null,
                ts: Date.parse(r.issued_at || '') || Date.now()
              };
            });
            renderList(mapped);
          })
          .catch(function (err) {
            console.error('Error fetching certificates:', err);
            var errorMsg = document.createElement('p');
            errorMsg.style.color = '#f87171';
            errorMsg.textContent = 'Error: ' + (err.message || 'Failed to load certificates');
            container.appendChild(errorMsg);
          });
      } else {
        var list = [];
        try { list = JSON.parse(localStorage.getItem('certificates') || '[]'); } catch (e) {}
        renderList(list);
      }
    })();
  </script>
</body>
</html>
