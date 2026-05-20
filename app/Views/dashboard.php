<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — Corso E‑Learning</title>
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
        <a href="<?= base_url('/') ?>#assessments">Assessments</a>
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
    <section class="dashboard">
      <div class="container">
        <div class="dashboard-grid">
          <aside class="dash-sidebar">
            <div class="dash-brand">
              <span class="dash-brand-icon" aria-hidden="true">🎓</span>
              <div><span class="dash-brand-name">Corso</span><span class="dash-brand-tagline">Learn from home</span></div>
            </div>
            <nav class="dash-menu">
              <a class="is-active" href="<?= base_url('dashboard') ?>" aria-label="Dashboard"><span class="mi">📊</span><span>Dashboard</span></a>
              <a href="<?= base_url('/') ?>#assessments" aria-label="Courses"><span class="mi">📚</span><span>Courses</span></a>
              <a href="<?= base_url('my-certificates') ?>" aria-label="My Certificates"><span class="mi">📜</span><span>My Certificates</span></a>
              <a href="#notifications" aria-label="Notifications"><span class="mi">🔔</span><span>Notifications</span></a>
              <a href="<?= base_url('verify') ?>" aria-label="Verify"><span class="mi">🔍</span><span>Verify</span></a>
            </nav>
            <div class="dash-sidebar-promo">
              <div class="dash-sidebar-promo-text">Unlock more courses and certificates.</div>
              <a href="<?= base_url('/') ?>#assessments" class="btn btn-primary dash-sidebar-promo-btn">Explore courses</a>
            </div>
          </aside>

          <section class="dash-main">
            <div class="dash-topbar">
              <h1 class="dash-page-title">Dashboard</h1>
              <div class="dash-search-wrap">
                <span class="dash-search-icon" aria-hidden="true">🔍</span>
                <input type="text" class="dash-search-input" placeholder="Search courses, documents, activities…" aria-label="Search" />
              </div>
              <div class="dash-topbar-right">
                <a class="dash-bell" href="<?= base_url('dashboard') ?>#notifications" aria-label="Notifications">🔔</a>
                <label class="dash-theme-toggle" aria-label="Toggle theme">
                  <input type="checkbox" class="dash-theme-checkbox" checked aria-checked="true" />
                  <span class="dash-theme-track"><span class="dash-theme-thumb" aria-hidden="true">🌙</span></span>
                </label>
              </div>
            </div>
            <div class="dash-welcome">
              <div class="dash-welcome-text">
                <h2 class="dash-title">Hi <span class="dash-user">User</span>!</h2>
                <p class="dash-welcome-sub">Start your learning today.</p>
              </div>
              <div class="dash-welcome-visual" aria-hidden="true">📖</div>
            </div>
            <div id="dash-announcements" style="margin-bottom: 20px;"></div>
            <div class="dash-row">
              <div class="dash-card dash-stats-card">
                <div class="dash-card-head">
                  <div>Your progress</div>
                  <a class="dash-link" href="<?= base_url('my-certificates') ?>">View all</a>
                </div>
                <div class="dash-card-body dash-stats-body">
                  <div class="dash-stat"><span class="dash-stat-value" id="stat-certs">—</span><span class="dash-stat-label">Certificates earned</span></div>
                  <div class="dash-stat"><span class="dash-stat-value" id="stat-avg">—</span><span class="dash-stat-label">Average score</span></div>
                  <div class="dash-stat"><span class="dash-stat-value" id="stat-passed">—</span><span class="dash-stat-label">Skill checks passed</span></div>
                </div>
              </div>
              <div class="dash-card dash-activity-card">
                <div class="dash-card-head">
                  <div>Learning this week</div>
                  <span class="dash-card-badge">Weekly</span>
                </div>
                <div class="dash-card-body dash-activity-body">
                  <div class="dash-activity-value" id="stat-lessons">—</div>
                  <div class="dash-activity-label">lessons completed</div>
                </div>
              </div>
            </div>
            <div class="dash-row">
              <div class="dash-card" id="recent-certs">
                <div class="dash-card-head">
                  <div>Recent Certificates</div>
                  <a class="dash-link" href="<?= base_url('my-certificates') ?>">See all</a>
                </div>
                <div class="dash-card-body">
                  <ul class="dash-certs" id="dash-certs-list"><li style="color:var(--text-muted);font-size:.9rem;">Loading...</li></ul>
                </div>
              </div>
              <div class="dash-card" id="notifications">
                <div class="dash-card-head">
                  <div>Upcoming</div>
                  <a class="dash-link" href="#notifications">See all</a>
                </div>
                <div class="dash-card-body">
                  <ul class="dash-messages dash-upcoming-list">
                    <li><span class="badge badge-user">CI</span><div><strong>Certificate Issued</strong><p>Saved to your account</p></div><span class="time">Now</span></li>
                    <li><span class="badge badge-user">SK</span><div><strong>New Skill Check</strong><p><a href="<?= base_url('/') ?>#assessments">Try “Python Basics”</a></p></div><span class="time">1h</span></li>
                    <li><span class="badge badge-user">VR</span><div><strong>Verification</strong><p>Use the Verify link in the top bar</p></div><span class="time">Today</span></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="dash-row">
              <div class="dash-card dash-card-full">
                <div class="dash-card-head">
                  <div>My Courses</div>
                  <div class="dash-card-tabs">
                    <span class="dash-tab is-active">All</span>
                    <span class="dash-tab">Ongoing</span>
                    <span class="dash-tab">Complete</span>
                  </div>
                </div>
                <div class="dash-card-body">
                  <ul class="dash-progress-list dash-courses-list" id="dash-courses-list">
                    <li style="color:var(--text-muted);font-size:.9rem;padding:16px;">Loading courses...</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>
          <aside class="dash-aside">
            <div class="dash-card dash-calendar-card">
              <div class="dash-card-head">
                <span class="dash-calendar-title" id="dash-calendar-month"></span>
                <div class="dash-calendar-nav"><button type="button" aria-label="Previous month">‹</button><button type="button" aria-label="Next month">›</button></div>
              </div>
              <div class="dash-card-body">
                <div class="dash-calendar-weekdays"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                <div class="dash-calendar-days" id="dash-calendar-days"></div>
              </div>
            </div>
            <div class="dash-card dash-foryou-card">
              <div class="dash-card-head">
                <div>For you</div>
                <a class="dash-link" href="<?= base_url('/') ?>#assessments">See all</a>
              </div>
              <div class="dash-card-body dash-foryou-body">
                <a href="<?= base_url('/') ?>#assessments" class="course-card dash-foryou-course">
                  <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>python.jpg" alt="Python Basics" /></div>
                  <div class="course-card-body">
                    <h3>Python Basics</h3>
                    <p>Syntax, data structures &amp; practical scripting.</p>
                    <span class="btn btn-primary dash-foryou-btn">Start →</span>
                  </div>
                </a>
                <a href="<?= base_url('/') ?>#assessments" class="course-card dash-foryou-course">
                  <div class="course-card-image"><img src="<?= base_url('assets/images/') ?>data-analysis.jpg" alt="Data Science Fundamentals" /></div>
                  <div class="course-card-body">
                    <h3>Data Science Fundamentals</h3>
                    <p>Core concepts, tooling, and data handling.</p>
                    <span class="btn btn-primary dash-foryou-btn">Start →</span>
                  </div>
                </a>
              </div>
            </div>
            <div class="dash-signout-wrap">
              <a href="<?= base_url('reset-password') ?>" class="dash-reset-pw">Reset password</a>
              <button class="dash-signout" type="button" aria-label="Sign Out">Sign Out</button>
            </div>
          </aside>
        </div>
      </div>
    </section>
  </main>
  
  <script>window.CORSO_API_BASE='<?= base_url('api') ?>';</script>
  <script src="<?= base_url('assets/js/main.js') ?>?v=2"></script>
  <script>
    (function () {
      // Protect dashboard UX: redirect to user login when not authenticated client-side.
      try {
        var su = JSON.parse(localStorage.getItem('sessionUser') || 'null');
        if (!(su && su.email)) {
          location.href = '<?= base_url('user-login') ?>';
          return;
        }
      } catch (e) {
        location.href = '<?= base_url('user-login') ?>';
        return;
      }

      var base = window.CORSO_API_BASE || '';
      var role = '';
      try { var su = JSON.parse(localStorage.getItem('sessionUser') || '{}'); role = (su && su.role) ? su.role : ''; } catch (e) {}
      if (!base) return;
      fetch(base + '/announcements/active?role=' + encodeURIComponent(role)).then(function (r) { return r.json(); }).then(function (data) {
        var list = data.announcements || [];
        var el = document.getElementById('dash-announcements');
        if (!el || list.length === 0) return;
        el.innerHTML = list.map(function (a) {
          return '<div style="background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.3); border-radius: 12px; padding: 14px 18px; margin-bottom: 10px;"><strong style="color: #fbbf24;">' + (a.title || '') + '</strong>' + (a.body ? '<p style="margin: 8px 0 0; color: var(--text-muted); font-size: 0.95rem;">' + a.body + '</p>' : '') + '</div>';
        }).join('');
      }).catch(function () {});

      // Student dashboard stats — backend se fetch karo
      var tok = '';
      try { tok = localStorage.getItem('apiToken') || ''; } catch(e) {}
      if (tok) {
        fetch(base + '/student/dashboard', {
          headers: { 'Authorization': 'Bearer ' + tok }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
          // Stats
          var el;
          el = document.getElementById('stat-certs');   if (el) el.textContent = d.certificates_earned || 0;
          el = document.getElementById('stat-avg');     if (el) el.textContent = (d.avg_score || 0) + '%';
          el = document.getElementById('stat-passed');  if (el) el.textContent = d.skills_passed || 0;
          el = document.getElementById('stat-lessons'); if (el) el.textContent = d.lessons_this_week || 0;

          // Recent certificates
          var certList = document.getElementById('dash-certs-list');
          if (certList) {
            if (d.recent_certificates && d.recent_certificates.length > 0) {
              certList.innerHTML = d.recent_certificates.map(function(c) {
                var date = c.issued_at ? new Date(c.issued_at).toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric'}) : '';
                return '<li><span class="badge badge-user">' + (c.course_title || 'C').charAt(0).toUpperCase() + '</span><div><strong>' + (c.course_title || 'Certificate') + '</strong><p>Issued ' + date + '</p></div><a href="' + (window.CORSO_API_BASE || '') + '/certificates/download/' + c.certificate_number + '" class="dash-link" target="_blank">View</a></li>';
              }).join('');
            } else {
              certList.innerHTML = '<li style="color:var(--text-muted);font-size:.9rem;">No certificates yet.</li>';
            }
          }

          // My courses
          var courseList = document.getElementById('dash-courses-list');
          if (courseList) {
            if (d.courses && d.courses.length > 0) {
              courseList.innerHTML = d.courses.map(function(c) {
                var pct = c.progress || 0;
                var colors = ['rgba(249,115,22,0.2)','rgba(34,197,94,0.2)','rgba(59,130,246,0.2)','rgba(168,85,247,0.2)'];
                var textColors = ['#fb923c','#4ade80','#60a5fa','#c084fc'];
                var ci = c.id % 4;
                return '<li>' +
                  '<span class="dash-course-icon" style="background:' + colors[ci] + ';color:' + textColors[ci] + ';">📂</span>' +
                  '<div class="dash-course-info">' +
                    '<a href="' + (window.CORSO_API_BASE ? window.CORSO_API_BASE.replace('/api','') : '') + '/#assessments" class="dash-course-title">' + c.title + '</a>' +
                    '<span class="dash-course-meta">By Corso</span>' +
                    '<div class="dash-course-progress-wrap"><div class="dash-course-progress-bar" style="width:' + pct + '%;"></div></div>' +
                    '<span class="dash-course-pct">' + pct + '%</span>' +
                  '</div>' +
                  '<a href="' + (window.CORSO_API_BASE ? window.CORSO_API_BASE.replace('/api','') : '') + '/#assessments" class="btn btn-outline dash-course-btn">View course</a>' +
                '</li>';
              }).join('');
            } else {
              courseList.innerHTML = '<li style="color:var(--text-muted);font-size:.9rem;padding:16px;">No courses enrolled yet. <a href="' + (window.CORSO_API_BASE ? window.CORSO_API_BASE.replace('/api','') : '') + '/#assessments">Explore courses</a></li>';
            }
          }
        })
        .catch(function() {
          var el;
          el = document.getElementById('stat-certs');   if (el) el.textContent = '—';
          el = document.getElementById('stat-avg');     if (el) el.textContent = '—';
          el = document.getElementById('stat-passed');  if (el) el.textContent = '—';
          el = document.getElementById('stat-lessons'); if (el) el.textContent = '—';
        });
      }
    })();
  </script>
</body>
</html>