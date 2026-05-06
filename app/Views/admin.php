<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Corso E‑Learning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>?v=2" />
  <style>
    .admin { padding: 64px 0 0; min-height: 100vh; background: #0f1419; }
    .admin .container { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
    .admin-grid { display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: start; min-height: calc(100vh - 64px); }
    .admin-sidebar { background: #1a1f26; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px 12px; position: sticky; top: 84px; }
    .admin-brand { display: flex; align-items: center; gap: 10px; font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; color: #e2e8f0; margin-bottom: 20px; padding: 0 8px; }
    .admin-brand-icon { font-size: 1.4rem; }
    .admin-menu { display: flex; flex-direction: column; gap: 4px; }
    .admin-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; color: var(--text-muted); text-decoration: none; transition: background 0.2s, color 0.2s; }
    .admin-menu a:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
    .admin-menu a.is-active { background: rgba(239,68,68,0.15); color: #f87171; }
    .admin-menu .mi { font-size: 1.1rem; width: 24px; text-align: center; }
    .admin-main { padding-top: 24px; display: flex; flex-direction: column; gap: 24px; }
    .admin-welcome { background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 50%, #dc2626 100%); border-radius: 16px; padding: 28px 32px; }
    .admin-welcome h1 { font-family: var(--font-display); font-weight: 800; font-size: 1.5rem; color: #fff; margin-bottom: 6px; }
    .admin-welcome p { color: rgba(255,255,255,0.9); font-size: 0.95rem; margin: 0; }
    .admin-stats { display: flex; flex-direction: column; gap: 20px; }
    .admin-stat-group { display: flex; flex-direction: column; gap: 12px; }
    .admin-stat-group-title { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; margin: 0; padding-left: 4px; }
    .admin-stat-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .admin-stat-card { background: linear-gradient(165deg, #1e2530 0%, #1a1f26 100%); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 20px 20px 22px; min-height: 104px; display: flex; flex-direction: column; justify-content: center; transition: border-color 0.2s, box-shadow 0.2s; }
    .admin-stat-card:hover { border-color: rgba(248,113,113,0.15); box-shadow: 0 8px 24px rgba(0,0,0,0.25); }
    .admin-stat-card .label { color: var(--text-muted); font-size: 0.82rem; font-weight: 600; margin-bottom: 10px; line-height: 1.3; }
    .admin-stat-card .value { font-family: var(--font-display); font-weight: 800; font-size: 1.75rem; color: #f1f5f9; line-height: 1.2; }
    .admin-stat-card .value.percent::after { content: '%'; font-weight: 600; opacity: 0.75; font-size: 0.85em; }
    .admin-stat-card .value.inr { font-size: 1.55rem; letter-spacing: -0.02em; }
    .admin-stat-card .value.decimal { font-size: 1.65rem; }
    .admin-section { background: #1a1f26; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; }
    .admin-section-head { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); font-weight: 700; font-size: 1rem; color: #e2e8f0; }
    .admin-section-body { padding: 20px; color: var(--text-muted); font-size: 0.95rem; }
    .admin-section-body p { margin: 0 0 12px; line-height: 1.6; }
    .admin-section-body p:last-child { margin-bottom: 0; }
    .admin-badge { display: inline-block; padding: 4px 10px; border-radius: 100px; background: rgba(239,68,68,0.2); color: #f87171; font-size: 0.75rem; font-weight: 700; margin-left: 8px; vertical-align: middle; }
    .admin-table-wrap { margin-top: 12px; }
    .admin-table { width: 100%; border-collapse: collapse; }
    .admin-table th, .admin-table td { padding: 10px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); color: #e2e8f0; font-size: 0.9rem; }
    .admin-table th { color: var(--text-muted); font-weight: 600; }
    .admin-table .btn-sm { padding: 4px 10px; font-size: 0.8rem; }
    .admin-placeholder { color: var(--text-muted); margin: 0; }
    @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } .admin-sidebar { position: static; } }
    @media (max-width: 768px) { .admin-stat-row { grid-template-columns: repeat(2, 1fr); } .admin-stat-card { padding: 18px 16px; min-height: 96px; } .admin-stat-card .value { font-size: 1.5rem; } }
  </style>
</head>
<body>
  <header class="header">
    <div class="container header-inner">
      <a href="<?= base_url('/') ?>" class="logo">Corso E‑Learning</a>
      <nav class="nav">
        <a href="<?= base_url('/') ?>#features">Features</a>
        <a href="<?= base_url('/') ?>#assessments">Assessments</a>
        <a href="<?= base_url('verify') ?>">Verify Certificate</a>
        <button class="nav-profile" aria-haspopup="true" aria-expanded="false"><span>👤</span><span class="nav-name admin-nav-name">Admin</span></button>
        <div class="nav-profile-menu" hidden>
          <button class="btn btn-outline nav-logout">Logout</button>
        </div>
      </nav>
      <button class="menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
    </div>
  </header>

  <main>
    <section class="admin">
      <div class="container">
        <div class="admin-grid">
          <aside class="admin-sidebar">
            <div class="admin-brand"><span class="admin-brand-icon">⚙️</span><span>Admin</span></div>
            <nav class="admin-menu">
              <a class="admin-nav-link is-active" href="<?= base_url('admin') ?>#dashboard" data-view="dashboard" data-permission="access_admin_panel"><span class="mi">📊</span><span>Dashboard</span></a>
              <a href="<?= base_url('super-admin') ?>" id="admin-link-superadmin" style="display: none;"><span class="mi">👑</span><span>Super Admin</span></a>
              <a class="admin-nav-link" href="<?= base_url('admin') ?>#users" data-view="users" data-permission="users_view"><span class="mi">👥</span><span>Users</span></a>
              <a class="admin-nav-link" href="<?= base_url('admin') ?>#certificates" data-view="certificates" data-permission="certificates_view"><span class="mi">📜</span><span>Certificates</span></a>
              <a class="admin-nav-link" href="<?= base_url('admin') ?>#courses" data-view="courses" data-permission="courses_manage"><span class="mi">📚</span><span>Courses</span></a>
              <a class="admin-nav-link" href="<?= base_url('admin') ?>#questions" data-view="questions" data-permission="questions_manage"><span class="mi">❓</span><span>Quiz questions</span></a>
              <a class="admin-nav-link" href="<?= base_url('admin') ?>#payments" data-view="payments" data-permission="payments_view"><span class="mi">💰</span><span>Payments</span></a>
              <a href="<?= base_url('verify') ?>" data-permission="certificates_view"><span class="mi">🔍</span><span>Verify</span></a>
            </nav>
          </aside>
          <section class="admin-main">
            <!-- Dashboard: analytics only -->
            <div class="admin-view" id="admin-view-dashboard">
              <div class="admin-welcome" data-permission="access_admin_panel">
                <h1>Welcome, <span id="admin-user-name">Admin</span> <span class="admin-badge">ADMIN</span></h1>
                <p>Overview and analytics. Use the side panel to open Users, Certificates, Courses, and more.</p>
              </div>
              <div class="admin-stats" data-permission="reports_view">
                <div class="admin-stat-group">
                  <p class="admin-stat-group-title">Users</p>
                  <div class="admin-stat-row">
                    <div class="admin-stat-card"><div class="label">Total users</div><div class="value" id="admin-stat-total_users" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Active</div><div class="value" id="admin-stat-active_users" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Inactive</div><div class="value" id="admin-stat-inactive_users" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Students</div><div class="value" id="admin-stat-total_students" data-type="number">—</div></div>
                  </div>
                </div>
                <div class="admin-stat-group">
                  <p class="admin-stat-group-title">Quiz activity</p>
                  <div class="admin-stat-row">
                    <div class="admin-stat-card"><div class="label">Total attempts</div><div class="value" id="admin-stat-total_attempts" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Today</div><div class="value" id="admin-stat-attempts_today" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Last 7 days</div><div class="value" id="admin-stat-attempts_last_7_days" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Pass rate</div><div class="value percent" id="admin-stat-pass_rate" data-type="percent">—</div></div>
                    <div class="admin-stat-card"><div class="label">Avg. score</div><div class="value decimal" id="admin-stat-average_score" data-type="decimal">—</div></div>
                  </div>
                </div>
                <div class="admin-stat-group">
                  <p class="admin-stat-group-title">Catalog &amp; revenue (₹)</p>
                  <div class="admin-stat-row">
                    <div class="admin-stat-card"><div class="label">Courses</div><div class="value" id="admin-stat-total_courses" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Quizzes</div><div class="value" id="admin-stat-total_quizzes" data-type="number">—</div></div>
                    <div class="admin-stat-card"><div class="label">Total revenue</div><div class="value inr" id="admin-stat-revenue" data-type="inr">—</div></div>
                    <div class="admin-stat-card"><div class="label">Today</div><div class="value inr" id="admin-stat-revenue_today" data-type="inr">—</div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Users page -->
            <div class="admin-view" id="admin-view-users" style="display: none;">
              <div class="admin-section" data-permission="users_view">
                <div class="admin-section-head">Users</div>
                <div class="admin-section-body">
                  <div class="admin-table-wrap" style="overflow-x: auto;"><table class="admin-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead><tbody id="admin-users-tbody"></tbody></table></div>
                </div>
              </div>
            </div>

            <!-- Certificates page -->
            <div class="admin-view" id="admin-view-certificates" style="display: none;">
              <div class="admin-section" data-permission="certificates_view">
                <div class="admin-section-head">Certificates</div>
                <div class="admin-section-body">
                  <p><input type="search" id="admin-cert-search" placeholder="Search by number, name or email" style="max-width: 320px; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /> <button type="button" class="btn btn-outline" id="admin-cert-search-btn">Search</button></p>
                  <div class="admin-table-wrap" style="overflow-x: auto;"><table class="admin-table"><thead><tr><th>Number</th><th>User</th><th>Course</th><th>Issued</th><th>Status</th><th data-permission="certificates_manage">Actions</th></tr></thead><tbody id="admin-cert-tbody"></tbody></table></div>
                </div>
              </div>
            </div>

            <!-- Courses page -->
            <div class="admin-view" id="admin-view-courses" style="display: none;">
              <div class="admin-section" data-permission="courses_manage">
                <div class="admin-section-head">Courses</div>
                <div class="admin-section-body">
                  <form id="admin-course-form" style="margin-bottom: 20px; display: grid; grid-template-columns: minmax(140px, 1fr) 1fr 1fr auto auto; gap: 12px; align-items: end; flex-wrap: wrap;">
                    <div><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Category</label><select name="category_id" id="admin-course-category" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;"></select></div>
                    <div><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Title</label><input type="text" name="title" required placeholder="Course title" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                    <div><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Description</label><input type="text" name="description" placeholder="Short description" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                    <input type="hidden" name="id" value="" />
                    <div style="display:flex; gap:8px; flex-wrap:wrap;"><button type="submit" class="btn btn-outline">Save course</button><button type="button" class="btn btn-outline" id="admin-course-new">New course</button></div>
                  </form>
                  <div class="admin-table-wrap" style="overflow-x: auto;"><table class="admin-table"><thead><tr><th>ID</th><th>Title</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody id="admin-courses-tbody"></tbody></table></div>
                </div>
              </div>
            </div>

            <!-- Quiz questions page -->
            <div class="admin-view" id="admin-view-questions" style="display: none;">
              <div class="admin-section" data-permission="questions_manage">
                <div class="admin-section-head">Quiz questions</div>
                <div class="admin-section-body">
                  <p style="margin-bottom: 12px; display: flex; flex-wrap: wrap; gap: 12px 20px; align-items: center;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" id="admin-show-existing-q" checked /> Show existing questions</label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;"><input type="checkbox" id="admin-q-fulltext" /> Show full question text</label>
                  </p>
                  <p style="margin-bottom: 12px;"><label>Select quiz </label><select id="admin-quiz-select" style="padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0; min-width: 280px; max-width: 100%;"></select></p>
                  <div id="admin-questions-existing-wrap">
                    <p style="margin: 0 0 10px; font-weight: 600; color: #e2e8f0;">Existing questions <span id="admin-existing-q-count" style="color: var(--text-muted); font-weight: 500;"></span></p>
                    <div id="admin-questions-list"></div>
                  </div>
                  <p style="margin-top: 12px;"><button type="button" class="btn btn-outline" id="admin-question-add-new">Add new question</button></p>
                  <form id="admin-question-form" style="margin-top: 16px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; display: none;">
                    <input type="hidden" name="question_id" value="" />
                    <div style="margin-bottom: 12px;"><label>Question</label><input type="text" name="question" required placeholder="Question text" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                      <div><label>A</label><input type="text" name="option_a" style="width:100%; padding: 6px 10px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e2e8f0;" /></div>
                      <div><label>B</label><input type="text" name="option_b" style="width:100%; padding: 6px 10px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e2e8f0;" /></div>
                      <div><label>C</label><input type="text" name="option_c" style="width:100%; padding: 6px 10px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e2e8f0;" /></div>
                      <div><label>D</label><input type="text" name="option_d" style="width:100%; padding: 6px 10px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e2e8f0;" /></div>
                    </div>
                    <div style="margin-bottom: 12px;"><label>Correct option</label><select name="correct_option" style="padding: 6px 10px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #e2e8f0;"><option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option></select></div>
                    <button type="submit" class="btn btn-outline">Save question</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Payments page -->
            <div class="admin-view" id="admin-view-payments" style="display: none;">
              <div class="admin-section" data-permission="payments_view">
                <div class="admin-section-head">Payments</div>
                <div class="admin-section-body">
                  <div class="admin-table-wrap" style="overflow-x: auto;"><table class="admin-table"><thead><tr><th>ID</th><th>User</th><th>Course</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead><tbody id="admin-payments-tbody"></tbody></table></div>
                </div>
              </div>
            </div>
            
          </section>
        </div>
      </div>
    </section>
  </main>

  <script>window.CORSO_API_BASE='<?= base_url('api') ?>';</script>
  <script src="<?= base_url('assets/js/main.js') ?>?v=2"></script>
  <script>
    (function () {
      // Prevent "flash then redirect" while permissions load
      try { document.body.style.visibility = 'hidden'; } catch (e) {}

      var sessionUser = null;
      try { sessionUser = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
      var token = '';
      try { token = localStorage.getItem('apiToken') || ''; } catch (e) {}
      var role = (sessionUser && sessionUser.role) ? String(sessionUser.role) : '';
      var roleLower = role ? String(role).toLowerCase() : '';
      var isPotentialAdmin = sessionUser && (roleLower === 'admin' || roleLower === 'super_admin' || roleLower === 'hr' || sessionUser.isAdmin === true || (sessionUser.email && sessionUser.email.toLowerCase() === 'admin@gmail.com'));
      if (!isPotentialAdmin) {
        location.replace('<?= base_url('admin-login') ?>');
        return;
      }
      var superAdminLink = document.getElementById('admin-link-superadmin');
      if (superAdminLink && roleLower === 'super_admin') superAdminLink.style.display = '';
      var nameEl = document.getElementById('admin-user-name');
      if (nameEl && sessionUser && sessionUser.name) nameEl.textContent = sessionUser.name;
      var navName = document.querySelector('.admin-nav-name');
      if (navName && sessionUser && sessionUser.name) navName.textContent = sessionUser.name;
      document.querySelectorAll('.nav-logout').forEach(function (btn) {
        btn.addEventListener('click', function () {
          localStorage.removeItem('sessionUser');
          localStorage.removeItem('apiToken');
          location.href = '<?= base_url('admin-login') ?>';
        });
      });

      var permissions = [];
      function esc(s) {
        return String(s == null ? '' : s)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }
      function can(perm) {
        if (roleLower === 'super_admin') return true;
        if (permissions.indexOf(perm) !== -1) return true;
        if (perm === 'users_view' && permissions.indexOf('users_manage') !== -1) return true;
        if (perm === 'certificates_view' && permissions.indexOf('certificates_manage') !== -1) return true;
        if (perm === 'questions_manage' && permissions.indexOf('quizzes_manage') !== -1) return true;
        if (perm === 'questions_manage' && permissions.indexOf('courses_manage') !== -1) return true;
        return false;
      }
      function applyPermissions() {
        document.querySelectorAll('[data-permission]').forEach(function (el) {
          var p = el.getAttribute('data-permission');
          el.style.display = can(p) ? '' : 'none';
        });
      }
      function showView(viewId) {
        var v = viewId || 'dashboard';
        document.querySelectorAll('.admin-view').forEach(function (el) { el.style.display = 'none'; });
        var panel = document.getElementById('admin-view-' + v);
        if (panel) panel.style.display = 'block';
        document.querySelectorAll('.admin-menu .admin-nav-link').forEach(function (a) {
          a.classList.toggle('is-active', a.getAttribute('data-view') === v);
        });
        if (v === 'certificates') loadCertificates();
        else if (v === 'users') loadUsers();
        else if (v === 'courses') loadCourses();
        else if (v === 'payments') loadPayments();
        else if (v === 'questions') { if (can('questions_manage') || can('quizzes_manage')) loadQuizzes(); }
      }

      function showPage() {
        try { document.body.style.visibility = ''; } catch (e) {}
      }

      if (window.corsoApi && window.corsoApi.base && token) {
        window.corsoApi.get('/admin/my-permissions').then(function (r) {
          if (!r.ok) return Promise.reject(r);
          return r.json();
        }).then(function (data) {
          permissions = Array.isArray(data.permissions) ? data.permissions : [];
          if (data.user) {
            if (data.user.name) {
              if (nameEl) nameEl.textContent = data.user.name;
              if (navName) navName.textContent = data.user.name;
            }
            if (data.user.role && sessionUser) {
              sessionUser.role = data.user.role;
              roleLower = String(data.user.role).toLowerCase();
              try { localStorage.setItem('sessionUser', JSON.stringify(sessionUser)); } catch (e) {}
            }
          }
          var hasAdminAccess = (roleLower === 'super_admin') || (permissions.indexOf('access_admin_panel') !== -1);
          if (!hasAdminAccess) {
            location.replace('<?= base_url('dashboard') ?>');
            return;
          }
          applyPermissions();
          showPage();
          document.querySelectorAll('.admin-menu .admin-nav-link').forEach(function (a) {
            a.addEventListener('click', function (e) {
              var view = a.getAttribute('data-view');
              if (view) { e.preventDefault(); location.hash = view; showView(view); }
            });
          });
          window.addEventListener('hashchange', function () { showView((location.hash || '#dashboard').slice(1)); });
          showView((location.hash || '#dashboard').slice(1));
        }).catch(function () {
          // If we can't verify permissions, don't grant access (except super admin).
        if (roleLower === 'super_admin') {
          permissions = ['access_admin_panel', 'reports_view', 'users_view', 'users_manage', 'certificates_view', 'certificates_manage', 'courses_manage'];
          applyPermissions();
          showPage();
          document.querySelectorAll('.admin-menu .admin-nav-link').forEach(function (a) {
            a.addEventListener('click', function (e) { var view = a.getAttribute('data-view'); if (view) { e.preventDefault(); location.hash = view; showView(view); } });
          });
          window.addEventListener('hashchange', function () { showView((location.hash || '#dashboard').slice(1)); });
          showView((location.hash || '#dashboard').slice(1));
          return;
        }
        location.replace('<?= base_url('admin-login') ?>');
        });
      } else {
        if (roleLower === 'super_admin') {
          permissions = ['access_admin_panel', 'reports_view', 'users_view', 'users_manage', 'certificates_view', 'certificates_manage', 'courses_manage'];
          applyPermissions();
          showPage();
          document.querySelectorAll('.admin-menu .admin-nav-link').forEach(function (a) {
            a.addEventListener('click', function (e) { var view = a.getAttribute('data-view'); if (view) { e.preventDefault(); location.hash = view; showView(view); } });
          });
          window.addEventListener('hashchange', function () { showView((location.hash || '#dashboard').slice(1)); });
          showView((location.hash || '#dashboard').slice(1));
        } else {
          location.replace('<?= base_url('admin-login') ?>');
        }
      }

      function adminApi(method, path, body) {
        var base = (window.corsoApi && window.corsoApi.base) ? window.corsoApi.base : '';
        var t = (window.corsoApi && window.corsoApi.token) ? window.corsoApi.token() : (token || '');
        var opts = { method: method, headers: { 'Accept': 'application/json' } };
        if (t) opts.headers['Authorization'] = 'Bearer ' + t;
        if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
          opts.headers['Content-Type'] = 'application/json';
          opts.body = JSON.stringify(body);
        }
        return fetch(base + path, opts);
      }

      if (window.corsoApi && window.corsoApi.base) {
        var statKeys = ['total_users', 'active_users', 'inactive_users', 'total_students', 'total_courses', 'total_quizzes', 'total_attempts', 'attempts_today', 'attempts_last_7_days', 'pass_rate', 'average_score', 'revenue', 'revenue_today'];
        function formatInr(n) {
          var x = Number(n);
          if (isNaN(x)) return '—';
          return '₹' + x.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        window.corsoApi.get('/admin/stats').then(function (r) {
          if (r.ok) return r.json();
          return Promise.reject();
        }).then(function (data) {
          statKeys.forEach(function (key) {
            var el = document.getElementById('admin-stat-' + key);
            if (!el) return;
            var raw = data[key];
            var type = el.getAttribute('data-type') || 'number';
            if (raw === undefined || raw === null) return;
            if (type === 'percent') el.textContent = Number(raw);
            else if (type === 'inr') el.textContent = formatInr(raw);
            else if (type === 'decimal') el.textContent = Number(raw).toFixed(1);
            else el.textContent = Number(raw);
          });
        }).catch(function () {});
      }

      function loadCertificates() {
        if (!can('certificates_view')) return;
        var q = document.getElementById('admin-cert-search');
        var path = '/admin/certificates' + (q && q.value ? '?q=' + encodeURIComponent(q.value) : '');
        adminApi('GET', path).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
          var tbody = document.getElementById('admin-cert-tbody');
          if (!tbody) return;
            var rows = (data.certificates || []).map(function (c) {
            var actions = '';
            var canRevoke = c.revoke_supported !== false;
            if (can('certificates_manage') && canRevoke && c.status === 'active') actions = '<button type="button" class="btn btn-outline btn-sm" data-revoke="' + c.id + '">Revoke</button>';
            else if (can('certificates_manage') && canRevoke && c.status === 'revoked') actions = '<button type="button" class="btn btn-outline btn-sm" data-reissue="' + c.id + '">Reissue</button>';
            return '<tr><td>' + (c.certificate_number || c.id || '') + '</td><td>' + (c.user_name || '') + ' <small>' + (c.user_email || '') + '</small></td><td>' + (c.course_title || c.course || '') + '</td><td>' + (c.issued_at || '') + '</td><td>' + (c.status || '') + '</td><td>' + actions + '</td></tr>';
          });
          tbody.innerHTML = rows.join('');
          tbody.querySelectorAll('[data-revoke]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              adminApi('POST', '/admin/certificates/' + btn.getAttribute('data-revoke') + '/revoke').then(function (r) { if (r.ok) loadCertificates(); });
            });
          });
          tbody.querySelectorAll('[data-reissue]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              adminApi('POST', '/admin/certificates/' + btn.getAttribute('data-reissue') + '/reissue').then(function (r) { if (r.ok) loadCertificates(); });
            });
          });
        }).catch(function () {});
      }
      document.getElementById('admin-cert-search-btn') && document.getElementById('admin-cert-search-btn').addEventListener('click', loadCertificates);
      document.getElementById('admin-cert-search') && document.getElementById('admin-cert-search').addEventListener('keypress', function (e) { if (e.key === 'Enter') loadCertificates(); });

      function loadUsers() {
        if (!can('users_view')) return;
        adminApi('GET', '/admin/users').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
          var tbody = document.getElementById('admin-users-tbody');
          if (!tbody) return;
          var rows = (data.users || []).map(function (u) {
            var st = u.status != null && u.status !== '' ? u.status : '—';
            var joined = u.created_at || '';
            return '<tr><td>' + u.id + '</td><td>' + (u.name || '') + '</td><td>' + (u.email || '') + '</td><td>' + (u.role || '') + '</td><td>' + st + '</td><td>' + joined + '</td></tr>';
          });
          tbody.innerHTML = rows.join('');
        }).catch(function () {});
      }

      function fillCourseCategorySelect(categories) {
        var sel = document.getElementById('admin-course-category');
        if (!sel) return;
        var keep = sel.value;
        sel.innerHTML = (categories || []).map(function (c) {
          return '<option value="' + c.id + '">' + esc(c.name) + '</option>';
        }).join('');
        if (keep && [].some.call(sel.options, function (o) { return o.value === keep; })) sel.value = keep;
        else if (sel.options.length) sel.selectedIndex = 0;
      }

      function loadCourses() {
        if (!can('courses_manage')) return;
        Promise.all([
          adminApi('GET', '/admin/categories').then(function (r) { return r.ok ? r.json() : Promise.reject(); }),
          adminApi('GET', '/admin/courses').then(function (r) { return r.ok ? r.json() : Promise.reject(); })
        ]).then(function (results) {
          fillCourseCategorySelect(results[0].categories || []);
          var data = results[1];
          var tbody = document.getElementById('admin-courses-tbody');
          if (!tbody) return;
          var rows = (data.courses || []).map(function (c) {
            return '<tr><td>' + c.id + '</td><td>' + esc(c.title || '') + '</td><td>' + esc(c.category_name || '') + '</td><td>' + esc(c.status || '') + '</td><td><button type="button" class="btn btn-outline btn-sm" data-edit="' + c.id + '">Edit</button> <button type="button" class="btn btn-outline btn-sm" data-del="' + c.id + '">Delete</button></td></tr>';
          });
          tbody.innerHTML = rows.join('');
          tbody.querySelectorAll('[data-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var id = btn.getAttribute('data-edit');
              var c = (data.courses || []).find(function (x) { return String(x.id) === String(id); });
              if (!c) return;
              var form = document.getElementById('admin-course-form');
              if (form) {
                form.querySelector('[name=id]').value = c.id;
                form.querySelector('[name=title]').value = c.title || '';
                form.querySelector('[name=description]').value = c.description || '';
                if (c.category_id != null) form.querySelector('[name=category_id]').value = String(c.category_id);
              }
            });
          });
          tbody.querySelectorAll('[data-del]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var cid = btn.getAttribute('data-del');
              if (!confirm('Delete this course? Quizzes and questions in this course will be removed. This cannot be undone.')) return;
              adminApi('DELETE', '/admin/courses/' + cid).then(function (r) {
                if (r.ok) { loadCourses(); return; }
                r.json().then(function (j) { alert(j.error || 'Could not delete'); }).catch(function () { alert('Could not delete'); });
              });
            });
          });
        }).catch(function () {});
      }
      var courseForm = document.getElementById('admin-course-form');
      if (courseForm) {
        courseForm.addEventListener('submit', function (e) {
          e.preventDefault();
          var id = courseForm.querySelector('[name=id]').value;
          var catEl = courseForm.querySelector('[name=category_id]');
          var catId = catEl ? parseInt(catEl.value, 10) : 0;
          var payload = {
            title: courseForm.querySelector('[name=title]').value,
            description: courseForm.querySelector('[name=description]').value,
            category_id: catId || 1
          };
          var p = id ? adminApi('PUT', '/admin/courses/' + id, payload) : adminApi('POST', '/admin/courses', payload);
          p.then(function (r) { if (r.ok) { courseForm.querySelector('[name=id]').value = ''; courseForm.reset(); loadCourses(); } });
        });
      }
      document.getElementById('admin-course-new') && document.getElementById('admin-course-new').addEventListener('click', function () {
        var form = document.getElementById('admin-course-form');
        if (!form) return;
        form.querySelector('[name=id]').value = '';
        form.querySelector('[name=title]').value = '';
        form.querySelector('[name=description]').value = '';
        var cat = form.querySelector('[name=category_id]');
        if (cat && cat.options.length) cat.selectedIndex = 0;
      });

      function loadPayments() {
        if (!can('payments_view')) return;
        adminApi('GET', '/admin/payments').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
          var tbody = document.getElementById('admin-payments-tbody');
          if (!tbody) return;
          var rows = (data.payments || []).map(function (p) {
            var date = p.paid_at || p.created_at || '';
            return '<tr><td>' + p.id + '</td><td>' + (p.user_name || '') + ' <small>' + (p.user_email || '') + '</small></td><td>' + (p.course_title || '') + '</td><td>₹' + Number(p.amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td><td>' + (p.payment_method || '') + '</td><td>' + date + '</td></tr>';
          });
          tbody.innerHTML = rows.join('');
        }).catch(function () {});
      }

      var lastQuizzesList = [];
      function loadQuizzes() {
        adminApi('GET', '/admin/quizzes').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
          var sel = document.getElementById('admin-quiz-select');
          if (!sel) return;
          lastQuizzesList = data.quizzes || [];
          sel.innerHTML = '<option value="">— Select quiz —</option>' + lastQuizzesList.map(function (q) {
            var n = (q.question_count !== undefined && q.question_count !== null) ? q.question_count : 0;
            return '<option value="' + q.id + '">' + esc(q.title || ('Quiz ' + q.id)) + ' — ' + n + ' qs · ' + esc(q.course_title || '') + '</option>';
          }).join('');
          sel.onchange = function () { loadQuizQuestions(sel.value); };
          var showEx = document.getElementById('admin-show-existing-q');
          if (lastQuizzesList.length && showEx && showEx.checked) {
            sel.value = String(lastQuizzesList[0].id);
            loadQuizQuestions(lastQuizzesList[0].id);
          } else {
            var ql = document.getElementById('admin-questions-list');
            if (ql) ql.innerHTML = '';
            var cnt = document.getElementById('admin-existing-q-count');
            if (cnt) cnt.textContent = '';
          }
        }).catch(function () {});
      }
      function loadQuizQuestions(quizId) {
        var listEl = document.getElementById('admin-questions-list');
        var formEl = document.getElementById('admin-question-form');
        var wrap = document.getElementById('admin-questions-existing-wrap');
        var cntEl = document.getElementById('admin-existing-q-count');
        var showExisting = document.getElementById('admin-show-existing-q');
        var fullText = document.getElementById('admin-q-fulltext');
        if (!listEl) return;
        if (!quizId) {
          listEl.innerHTML = '';
          if (cntEl) cntEl.textContent = '';
          if (formEl) formEl.style.display = 'none';
          return;
        }
        if (formEl) formEl.style.display = 'block';
        if (!showExisting || !showExisting.checked) {
          if (wrap) wrap.style.display = 'none';
          listEl.innerHTML = '';
          if (cntEl) cntEl.textContent = '';
          return;
        }
        if (wrap) wrap.style.display = '';
        adminApi('GET', '/admin/quizzes/' + quizId + '/questions').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
          var questions = data.questions || [];
          if (cntEl) cntEl.textContent = '(' + questions.length + ')';
          var useFull = fullText && fullText.checked;
          listEl.innerHTML = '<table class="admin-table"><thead><tr><th>#</th><th>Question</th><th>Correct</th><th>Actions</th></tr></thead><tbody>' + questions.map(function (q) {
            var qt = q.question || '';
            var shown = useFull ? esc(qt) : (esc(qt.length > 120 ? qt.substring(0, 120) + '…' : qt));
            return '<tr><td>' + (q.position || '') + '</td><td>' + shown + '</td><td>' + esc(q.correct_option || '') + '</td><td><button type="button" class="btn btn-outline btn-sm" data-qedit="' + q.id + '">Edit</button> <button type="button" class="btn btn-outline btn-sm" data-qdel="' + q.id + '">Delete</button></td></tr>';
          }).join('') + '</tbody></table>';
          listEl.querySelectorAll('[data-qedit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var q = questions.find(function (x) { return String(x.id) === btn.getAttribute('data-qedit'); });
              if (!q || !formEl) return;
              formEl.style.display = 'block';
              formEl.querySelector('[name=question_id]').value = q.id;
              formEl.querySelector('[name=question]').value = q.question || '';
              formEl.querySelector('[name=option_a]').value = q.option_a || '';
              formEl.querySelector('[name=option_b]').value = q.option_b || '';
              formEl.querySelector('[name=option_c]').value = q.option_c || '';
              formEl.querySelector('[name=option_d]').value = q.option_d || '';
              var co = String(q.correct_option || 'A').toUpperCase().charAt(0);
              formEl.querySelector('[name=correct_option]').value = (co >= 'A' && co <= 'D') ? co : 'A';
            });
          });
          listEl.querySelectorAll('[data-qdel]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              if (!confirm('Delete this question?')) return;
              adminApi('DELETE', '/admin/questions/' + btn.getAttribute('data-qdel')).then(function (r) { if (r.ok) loadQuizQuestions(document.getElementById('admin-quiz-select').value); });
            });
          });
          if (formEl) { formEl.style.display = 'block'; formEl.querySelector('[name=question_id]').value = ''; formEl.querySelector('[name=question]').value = ''; formEl.querySelector('[name=option_a]').value = ''; formEl.querySelector('[name=option_b]').value = ''; formEl.querySelector('[name=option_c]').value = ''; formEl.querySelector('[name=option_d]').value = ''; formEl.querySelector('[name=correct_option]').value = 'A'; }
        }).catch(function () {});
      }
      var questionForm = document.getElementById('admin-question-form');
      if (questionForm) {
        questionForm.addEventListener('submit', function (e) {
          e.preventDefault();
          var quizId = document.getElementById('admin-quiz-select') && document.getElementById('admin-quiz-select').value;
          if (!quizId) return;
          var qid = questionForm.querySelector('[name=question_id]').value;
          var payload = { question: questionForm.querySelector('[name=question]').value, option_a: questionForm.querySelector('[name=option_a]').value, option_b: questionForm.querySelector('[name=option_b]').value, option_c: questionForm.querySelector('[name=option_c]').value, option_d: questionForm.querySelector('[name=option_d]').value, correct_option: questionForm.querySelector('[name=correct_option]').value };
          var p = qid ? adminApi('PUT', '/admin/questions/' + qid, payload) : adminApi('POST', '/admin/quizzes/' + quizId + '/questions', payload);
          p.then(function (r) { if (r.ok) { questionForm.querySelector('[name=question_id]').value = ''; questionForm.reset(); loadQuizQuestions(quizId); } });
        });
      }

      document.getElementById('admin-show-existing-q') && document.getElementById('admin-show-existing-q').addEventListener('change', function () {
        var sel = document.getElementById('admin-quiz-select');
        if (!sel) return;
        if (this.checked && sel.value) loadQuizQuestions(sel.value);
        else if (this.checked && lastQuizzesList.length) {
          sel.value = String(lastQuizzesList[0].id);
          loadQuizQuestions(lastQuizzesList[0].id);
        } else if (!this.checked && sel.value) {
          loadQuizQuestions(sel.value);
        }
      });
      document.getElementById('admin-q-fulltext') && document.getElementById('admin-q-fulltext').addEventListener('change', function () {
        var sel = document.getElementById('admin-quiz-select');
        if (sel && sel.value) loadQuizQuestions(sel.value);
      });
      document.getElementById('admin-question-add-new') && document.getElementById('admin-question-add-new').addEventListener('click', function () {
        var sel = document.getElementById('admin-quiz-select');
        if (!sel || !sel.value) { alert('Select a quiz first.'); return; }
        var formEl = document.getElementById('admin-question-form');
        if (!formEl) return;
        formEl.style.display = 'block';
        formEl.querySelector('[name=question_id]').value = '';
        formEl.querySelector('[name=question]').value = '';
        formEl.querySelector('[name=option_a]').value = '';
        formEl.querySelector('[name=option_b]').value = '';
        formEl.querySelector('[name=option_c]').value = '';
        formEl.querySelector('[name=option_d]').value = '';
        formEl.querySelector('[name=correct_option]').value = 'A';
      });

    })();
  </script>
</body>
</html>
