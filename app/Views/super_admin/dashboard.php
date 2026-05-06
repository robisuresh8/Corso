<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Super Admin — Corso E‑Learning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>?v=2" />
  <style>
    .sa { padding: 64px 0 0; min-height: 100vh; background: #0f1419; }
    .sa .container { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
    .sa-grid { display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: start; min-height: calc(100vh - 64px); }
    .sa-sidebar { background: #1a1f26; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; padding: 20px 12px; position: sticky; top: 84px; }
    .sa-brand { display: flex; align-items: center; gap: 10px; font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; color: #fbbf24; margin-bottom: 20px; padding: 0 8px; }
    .sa-menu { display: flex; flex-direction: column; gap: 4px; }
    .sa-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; color: var(--text-muted); text-decoration: none; transition: background 0.2s, color 0.2s; }
    .sa-menu a:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
    .sa-menu a.is-active { background: rgba(251,191,36,0.15); color: #fbbf24; }
    .sa-main { padding-top: 24px; display: flex; flex-direction: column; gap: 32px; }
    .sa-card { background: #1a1f26; border: 1px solid rgba(255,255,255,0.06); border-radius: 16px; overflow: hidden; }
    .sa-card-head { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.06); font-weight: 700; font-size: 1rem; color: #e2e8f0; }
    .sa-card-body { padding: 20px; }
    .sa-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; }
    .sa-stat { padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; }
    .sa-stat .label { color: var(--text-muted); font-size: 0.8rem; margin-bottom: 4px; }
    .sa-stat .value { font-family: var(--font-display); font-weight: 800; font-size: 1.5rem; color: #e2e8f0; }
    .sa-table { width: 100%; border-collapse: collapse; }
    .sa-table th, .sa-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); color: #e2e8f0; }
    .sa-table th { color: var(--text-muted); font-weight: 600; font-size: 0.85rem; }
    .sa-table select { background: #0f1419; color: #e2e8f0; border: 1px solid rgba(255,255,255,0.1); padding: 6px 10px; border-radius: 8px; }
    .sa-form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto auto; gap: 12px; align-items: end; margin-bottom: 20px; flex-wrap: wrap; }
    .sa-form-grid .input-wrap input { width: 100%; padding: 10px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0; }
    .sa-form-grid label { display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px; }
    .sa-badge { display: inline-block; padding: 4px 10px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; }
    .sa-badge.super_admin { background: rgba(251,191,36,0.2); color: #fbbf24; }
    .sa-badge.admin { background: rgba(239,68,68,0.2); color: #f87171; }
    .sa-badge.hr { background: rgba(34,197,94,0.2); color: #22c55e; }
    .sa-badge.student { background: rgba(59,130,246,0.2); color: #3b82f6; }
    .sa-perm-matrix { overflow-x: auto; }
    .sa-perm-matrix table { min-width: 600px; }
    .sa-perm-matrix th { white-space: nowrap; }
    .sa-perm-matrix input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }
    .sa-help { background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.3); border-radius: 12px; }
    .sa-help summary { padding: 14px 20px; font-weight: 700; color: #fbbf24; cursor: pointer; list-style: none; }
    .sa-help summary::-webkit-details-marker { display: none; }
    .sa-help summary::before { content: '▶ '; font-size: 0.7em; }
    .sa-help[open] summary::before { content: '▼ '; }
    .sa-help-inner { padding: 0 20px 20px; color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }
    .sa-help-inner ol { margin: 0 0 12px; padding-left: 20px; }
    .sa-help-inner li { margin-bottom: 6px; }
    .btn-sa { background: #fbbf24; color: #0f1419; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .btn-sa:hover { background: #f59e0b; }
    .btn-sa-outline { background: transparent; color: #fbbf24; border: 1px solid #fbbf24; padding: 8px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    @media (max-width: 900px) { .sa-grid { grid-template-columns: 1fr; } .sa-sidebar { position: static; } .sa-form-grid { grid-template-columns: 1fr 1fr; } }
  </style>
</head>
<body>
  <header class="header">
    <div class="container header-inner">
      <a href="<?= base_url('/') ?>" class="logo">Corso E‑Learning</a>
      <nav class="nav">
        <a href="<?= base_url('super-admin') ?>">Super Admin</a>
        <a href="<?= base_url('admin') ?>">Admin Panel</a>
        <a href="<?= base_url('verify') ?>">Verify</a>
        <button class="nav-profile" aria-haspopup="true"><span>👤</span><span class="nav-name" id="sa-user-name">Super Admin</span></button>
        <div class="nav-profile-menu" hidden>
          <button class="btn btn-outline nav-logout">Logout</button>
        </div>
      </nav>
      <button class="menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
    </div>
  </header>

  <main>
    <section class="sa">
      <div class="container">
        <div class="sa-grid">
          <aside class="sa-sidebar">
            <div class="sa-brand"><span>👑</span><span>Super Admin</span></div>
            <nav class="sa-menu">
              <a class="sa-nav-link is-active" href="<?= base_url('super-admin') ?>#dashboard" data-view="dashboard"><span>📊</span><span>Dashboard</span></a>
              <a class="sa-nav-link" href="<?= base_url('super-admin') ?>#users" data-view="users"><span>👥</span><span>Users</span></a>
              <a class="sa-nav-link" href="<?= base_url('super-admin') ?>#roles" data-view="roles"><span>🔐</span><span>Role permissions</span></a>
              <a class="sa-nav-link" href="<?= base_url('super-admin') ?>#announcements" data-view="announcements"><span>📢</span><span>Announcements</span></a>
              <a href="<?= base_url('admin') ?>"><span>⚙️</span><span>Admin Panel</span></a>
            </nav>
          </aside>
          <section class="sa-main">

            <!-- Dashboard: analytics only -->
            <div class="sa-view" id="sa-view-dashboard">
              <div class="sa-card">
                <div class="sa-card-head">Dashboard</div>
                <div class="sa-card-body">
                  <div class="sa-stats" id="sa-stats"></div>
                  <p style="margin-top: 20px; font-weight: 700; color: #e2e8f0;">Recent signups</p>
                  <table class="sa-table" id="sa-recent-signups" style="margin-top: 8px;">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Signed up</th></tr></thead>
                    <tbody></tbody>
                  </table>
                <p style="margin-top: 20px; font-weight: 700; color: #e2e8f0;">Analytics</p>
                <div class="sa-stats" id="sa-analytics-summary" style="margin-top: 8px;"></div>
                <p style="color: var(--text-muted); margin-top: 16px;"><strong>Revenue by month</strong></p>
                <div id="sa-analytics-revenue" style="min-height: 48px; color: var(--text-muted); font-size: 0.9rem;"></div>
                <p style="color: var(--text-muted); margin-top: 16px;"><strong>Quiz attempts by month</strong></p>
                <div id="sa-analytics-attempts" style="min-height: 48px; color: var(--text-muted); font-size: 0.9rem;"></div>
                </div>
              </div>
            </div>

            <!-- Users page -->
            <div class="sa-view" id="sa-view-users" style="display: none;">
            <div class="sa-card" id="users">
              <div class="sa-card-head">Users & roles</div>
              <div class="sa-card-body">
                <form id="sa-add-user-form" class="sa-form-grid">
                  <div>
                    <label>Name</label>
                    <div class="input-wrap"><input type="text" name="name" required placeholder="Full name" /></div>
                  </div>
                  <div>
                    <label>Email</label>
                    <div class="input-wrap"><input type="email" name="email" required placeholder="user@example.com" /></div>
                  </div>
                  <div>
                    <label>Password</label>
                    <div class="input-wrap"><input type="password" name="password" required minlength="8" placeholder="Min 8 chars" /></div>
                  </div>
                  <div>
                    <label>Role</label>
                    <select name="role" id="sa-new-user-role"></select>
                  </div>
                  <div><button type="submit" class="btn-sa">Add user</button></div>
                </form>
                <div class="sa-perm-matrix">
                  <table class="sa-table" id="sa-users-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
            </div>

            <!-- Role permissions page -->
            <div class="sa-view" id="sa-view-roles" style="display: none;">
            <div class="sa-card" id="roles">
              <div class="sa-card-head">Set role & permissions for a user</div>
              <div class="sa-card-body">
                <p style="color: var(--text-muted); margin-bottom: 16px;">Select a user, then set their role and what that role can do. Changes apply to the selected user and (for permissions) to everyone with that role.</p>
                <div class="sa-user-perm-row" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; margin-bottom: 24px;">
                  <div>
                    <label for="sa-select-user">Select user</label>
                    <select id="sa-select-user" class="sa-select-user" style="min-width: 220px; padding: 10px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;">
                      <option value="">— Choose a user —</option>
                    </select>
                  </div>
                  <div id="sa-user-role-wrap" style="display: none;">
                    <label for="sa-user-role">Role for this user</label>
                    <select id="sa-user-role" style="min-width: 160px; padding: 10px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;"></select>
                  </div>
                  <div id="sa-user-perm-actions" style="display: none;">
                    <button type="button" class="btn-sa" id="sa-save-user-role">Save role</button>
                    <button type="button" class="btn-sa-outline" id="sa-save-role-perms" style="margin-left: 8px;">Save permissions for this role</button>
                  </div>
                </div>
                <div id="sa-perm-for-role-wrap" style="display: none;">
                  <div class="sa-card-head" style="border-radius: 0; margin: 0 -20px 16px -20px;">Permissions for role: <span id="sa-perm-role-label"></span></div>
                  <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <label>Apply template</label>
                    <select id="sa-template-select" style="padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0; min-width: 180px;">
                      <option value="">— Choose preset —</option>
                    </select>
                    <button type="button" class="btn-sa-outline" id="sa-apply-template-btn">Apply to this role</button>
                  </div>
                  <div id="sa-perm-checkboxes" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
              </div>
            </div>
            </div>

            <!-- Announcements page -->
            <div class="sa-view" id="sa-view-announcements" style="display: none;">
            <div class="sa-card" id="announcements">
              <div class="sa-card-head">Announcements / notices</div>
              <div class="sa-card-body">
                <p style="color: var(--text-muted); margin-bottom: 16px;">System notices shown to users on the dashboard. Use <em>Target roles</em> = <code>all</code> for everyone, or e.g. <code>student,admin</code>.</p>
                <form id="sa-announcement-form" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; margin-bottom: 20px; flex-wrap: wrap;">
                  <div><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Title</label><input type="text" name="title" required placeholder="Notice title" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                  <div><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Target roles (all or comma-separated)</label><input type="text" name="target_roles" value="all" placeholder="all" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                  <div><button type="submit" class="btn-sa">Add announcement</button></div>
                  <div style="grid-column: 1 / -1;"><label style="display:block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">Body (optional)</label><input type="text" name="body" placeholder="Message body" style="width:100%; padding: 8px 12px; background: #0f1419; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e2e8f0;" /></div>
                </form>
                <table class="sa-table" id="sa-announcements-table">
                  <thead><tr><th>Title</th><th>Target</th><th>Actions</th></tr></thead>
                  <tbody></tbody>
                </table>
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
  var sessionUser = null;
  try { sessionUser = JSON.parse(localStorage.getItem('sessionUser') || 'null'); } catch (e) {}
  var token = '';
  try { token = localStorage.getItem('apiToken') || ''; } catch (e) {}
  if (!sessionUser || (sessionUser.role !== 'super_admin')) {
    location.replace('<?= base_url('admin-login') ?>');
    return;
  }
  var nameEl = document.getElementById('sa-user-name');
  if (nameEl && sessionUser.name) nameEl.textContent = sessionUser.name;
  document.querySelectorAll('.nav-logout').forEach(function (btn) {
    btn.addEventListener('click', function () {
      localStorage.removeItem('sessionUser');
      localStorage.removeItem('apiToken');
      location.href = '<?= base_url('admin-login') ?>';
    });
  });

  function api(method, path, body) {
    var opts = { method: method, headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token } };
    if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
    return fetch((window.CORSO_API_BASE || '') + path, opts);
  }

  // Stats + recent signups
  api('GET', '/super-admin/stats').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
    var el = document.getElementById('sa-stats');
    if (el) {
      var html = '<div class="sa-stat"><div class="label">Total users</div><div class="value">' + (data.total_users || 0) + '</div></div>';
      if (data.by_role) {
        for (var role in data.by_role) {
          html += '<div class="sa-stat"><div class="label">' + role + '</div><div class="value">' + data.by_role[role] + '</div></div>';
        }
      }
      el.innerHTML = html;
    }
    var tbody = document.querySelector('#sa-recent-signups tbody');
    if (tbody && data.recent_signups) {
      tbody.innerHTML = data.recent_signups.map(function (u) {
        return '<tr><td>' + (u.name || '') + '</td><td>' + (u.email || '') + '</td><td>' + (u.role || '') + '</td><td>' + (u.created_at || '') + '</td></tr>';
      }).join('');
    }

    var sum = document.getElementById('sa-analytics-summary');
    if (sum) {
      sum.innerHTML =
        '<div class="sa-stat"><div class="label">Revenue total</div><div class="value">$' + Number(data.revenue_total || 0).toFixed(2) + '</div></div>' +
        '<div class="sa-stat"><div class="label">Attempts total</div><div class="value">' + (data.attempts_total || 0) + '</div></div>';
    }
    var rev = data.revenue_by_month || {};
    var revEl = document.getElementById('sa-analytics-revenue');
    if (revEl) revEl.innerHTML = Object.keys(rev).sort().reverse().slice(0, 12).map(function (m) { return m + ': $' + Number(rev[m]).toFixed(2); }).join(' &nbsp;|&nbsp; ') || 'No data yet.';
    var att = data.attempts_by_month || {};
    var attEl = document.getElementById('sa-analytics-attempts');
    if (attEl) attEl.innerHTML = Object.keys(att).sort().reverse().slice(0, 12).map(function (m) { return m + ': ' + att[m] + ' attempts'; }).join(' &nbsp;|&nbsp; ') || 'No data yet.';
  }).catch(function () {});

  // Roles list for dropdowns
  var assignableRoles = {};
  var rolesData = { roles: {}, permissions: {}, matrix: {} };
  var allUsers = [];

  function fillRoleDropdown(selectId, includeSuperAdmin) {
    var sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '';
    for (var slug in assignableRoles) {
      var o = document.createElement('option');
      o.value = slug;
      o.textContent = assignableRoles[slug];
      sel.appendChild(o);
    }
    if (includeSuperAdmin) {
      var o = document.createElement('option');
      o.value = 'super_admin';
      o.textContent = 'Super Admin';
      sel.appendChild(o);
    }
  }

  function loadRoles() {
    api('GET', '/super-admin/roles').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
      assignableRoles = data.roles || {};
      rolesData = { roles: data.roles || {}, permissions: data.permissions || {}, matrix: data.matrix || {} };
      fillRoleDropdown('sa-new-user-role', false);
      fillRoleDropdown('sa-user-role', true);
      loadUsersForDropdown();
      var roleSel = document.getElementById('sa-user-role');
      if (roleSel && roleSel.value) renderPermCheckboxes(roleSel.value);
    }).catch(function () { document.getElementById('sa-new-user-role').innerHTML = '<option value="student">Student</option>'; });
  }
  api('GET', '/super-admin/role-templates').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
    var sel = document.getElementById('sa-template-select');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Choose preset —</option>';
    var t = data.templates || {};
    for (var slug in t) sel.appendChild(new Option(t[slug], slug));
  }).catch(function () {});
  document.getElementById('sa-apply-template-btn') && document.getElementById('sa-apply-template-btn').addEventListener('click', function () {
    var roleSel = document.getElementById('sa-user-role');
    var templateSel = document.getElementById('sa-template-select');
    if (!roleSel || !roleSel.value || !templateSel || !templateSel.value) { alert('Select a user role and a template.'); return; }
    api('POST', '/super-admin/roles/' + encodeURIComponent(roleSel.value) + '/apply-template', { template: templateSel.value }).then(function (r) {
      if (!r.ok) return r.json().then(function (d) { return Promise.reject(d); });
      return r.json();
    }).then(function (data) {
      if (rolesData.matrix) rolesData.matrix[data.role] = data.permissions || [];
      renderPermCheckboxes(roleSel.value);
      alert('Template applied.');
    }).catch(function (err) { alert(err && err.error ? err.error : 'Failed'); });
  });
  function showView(viewId) {
    var v = viewId || 'dashboard';
    document.querySelectorAll('.sa-view').forEach(function (el) { el.style.display = 'none'; });
    var panel = document.getElementById('sa-view-' + v);
    if (panel) panel.style.display = 'block';
    document.querySelectorAll('.sa-menu .sa-nav-link').forEach(function (a) {
      a.classList.toggle('is-active', a.getAttribute('data-view') === v);
    });
    if (v === 'users') loadUsers();
    else if (v === 'announcements') loadAnnouncements();
  }
  document.querySelectorAll('.sa-menu .sa-nav-link').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var view = a.getAttribute('data-view');
      if (view) { e.preventDefault(); location.hash = view; showView(view); }
    });
  });
  window.addEventListener('hashchange', function () { showView((location.hash || '#dashboard').slice(1)); });

  loadRoles();

  // "Select user" dropdown: when user is selected, show their role and permissions for that role
  function renderPermCheckboxes(roleSlug) {
    var wrap = document.getElementById('sa-perm-checkboxes');
    var label = document.getElementById('sa-perm-role-label');
    if (!wrap || !label) return;
    var roleName = rolesData.roles[roleSlug] || roleSlug;
    if (roleSlug === 'super_admin') roleName = 'Super Admin';
    label.textContent = roleName;
    wrap.innerHTML = '';
    var permList = rolesData.permissions || {};
    var checked = (rolesData.matrix && rolesData.matrix[roleSlug]) ? rolesData.matrix[roleSlug] : [];
    for (var slug in permList) {
      var labelEl = document.createElement('label');
      labelEl.style.display = 'flex';
      labelEl.style.alignItems = 'center';
      labelEl.style.gap = '8px';
      labelEl.style.cursor = 'pointer';
      var cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.setAttribute('data-permission', slug);
      cb.checked = checked.indexOf(slug) !== -1;
      if (roleSlug === 'super_admin') { cb.checked = true; cb.disabled = true; }
      labelEl.appendChild(cb);
      labelEl.appendChild(document.createTextNode(permList[slug]));
      wrap.appendChild(labelEl);
    }
    document.getElementById('sa-perm-for-role-wrap').style.display = 'block';
  }

  function loadUsersForDropdown() {
    api('GET', '/super-admin/users').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
      allUsers = data.users || [];
      var sel = document.getElementById('sa-select-user');
      if (!sel) return;
      var keep = sel.value;
      sel.innerHTML = '<option value="">— Choose a user —</option>';
      allUsers.forEach(function (u) {
        var o = document.createElement('option');
        o.value = u.id;
        o.textContent = (u.name || u.email) + ' (' + (u.email || '') + ') — ' + (u.role || '');
        sel.appendChild(o);
      });
      if (keep) sel.value = keep;
      onSelectUserChange();
    });
  }

  function onSelectUserChange() {
    var userId = document.getElementById('sa-select-user').value;
    var roleWrap = document.getElementById('sa-user-role-wrap');
    var actionsWrap = document.getElementById('sa-user-perm-actions');
    var permWrap = document.getElementById('sa-perm-for-role-wrap');
    if (!userId) {
      roleWrap.style.display = 'none';
      actionsWrap.style.display = 'none';
      permWrap.style.display = 'none';
      return;
    }
    var user = allUsers.find(function (u) { return String(u.id) === String(userId); });
    var role = (user && user.role) ? user.role : 'student';
    var roleSel = document.getElementById('sa-user-role');
    roleWrap.style.display = '';
    actionsWrap.style.display = '';
    roleSel.value = role;
    renderPermCheckboxes(role);
  }

  document.getElementById('sa-select-user').addEventListener('change', onSelectUserChange);
  document.getElementById('sa-user-role').addEventListener('change', function () {
    renderPermCheckboxes(this.value);
  });

  document.getElementById('sa-save-user-role').addEventListener('click', function () {
    var userId = document.getElementById('sa-select-user').value;
    var role = document.getElementById('sa-user-role').value;
    if (!userId) { alert('Select a user first.'); return; }
    api('PATCH', '/super-admin/users/' + userId, { role: role }).then(function (r) {
      if (!r.ok) return r.json().then(function (d) { return Promise.reject(d); });
      alert('Role saved.');
      loadUsersForDropdown();
      loadUsers();
    }).catch(function (err) { alert(err && err.error ? err.error : 'Failed to save.'); });
  });

  document.getElementById('sa-save-role-perms').addEventListener('click', function () {
    var role = document.getElementById('sa-user-role').value;
    if (!role || role === 'super_admin') { alert('Cannot change Super Admin permissions.'); return; }
    var checkboxes = document.querySelectorAll('#sa-perm-checkboxes input[type=checkbox]:not([disabled])');
    var permissions = [];
    checkboxes.forEach(function (cb) { if (cb.checked) permissions.push(cb.getAttribute('data-permission')); });
    api('PUT', '/super-admin/roles/' + encodeURIComponent(role), { permissions: permissions }).then(function (r) {
      if (!r.ok) return r.json().then(function (d) { return Promise.reject(d); });
      alert('Permissions for this role saved.');
      api('GET', '/super-admin/roles').then(function (res) { return res.ok ? res.json() : {}; }).then(function (data) {
        rolesData = { roles: data.roles || {}, permissions: data.permissions || {}, matrix: data.matrix || {} };
        renderPermCheckboxes(role);
      });
    }).catch(function (err) { alert(err && err.error ? err.error : 'Failed to save.'); });
  });

  // Users list
  function loadUsers() {
    api('GET', '/super-admin/users').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
      var tbody = document.querySelector('#sa-users-table tbody');
      tbody.innerHTML = '';
      (data.users || []).forEach(function (u) {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' + (u.name || '') + '</td><td>' + (u.email || '') + '</td><td><span class="sa-badge ' + (u.role || '') + '">' + (u.role || '') + '</span></td><td>' + (u.status || '') + '</td><td></td>';
        var roleCell = tr.querySelector('td:nth-child(3)');
        var statusCell = tr.querySelector('td:nth-child(4)');
        var actionCell = tr.querySelector('td:nth-child(5)');
        if ((u.role || '') !== 'super_admin') {
          var sel = document.createElement('select');
          sel.className = 'sa-role-select';
          sel.dataset.userId = u.id;
          for (var slug in assignableRoles) {
            var o = document.createElement('option');
            o.value = slug;
            o.textContent = assignableRoles[slug];
            if (slug === (u.role || '')) o.selected = true;
            sel.appendChild(o);
          }
          sel.addEventListener('change', function () {
            api('PATCH', '/super-admin/users/' + this.dataset.userId, { role: this.value }).then(function (res) {
              if (res.ok) loadUsers();
            });
          });
          roleCell.innerHTML = '';
          roleCell.appendChild(sel);

          var statusSel = document.createElement('select');
          statusSel.className = 'sa-role-select';
          statusSel.style.marginLeft = '8px';
          statusSel.dataset.userId = u.id;
          var activeOpt = document.createElement('option');
          activeOpt.value = 'active';
          activeOpt.textContent = 'active';
          var inactiveOpt = document.createElement('option');
          inactiveOpt.value = 'inactive';
          inactiveOpt.textContent = 'inactive';
          if ((u.status || '') === 'active') activeOpt.selected = true;
          else inactiveOpt.selected = true;
          statusSel.appendChild(activeOpt);
          statusSel.appendChild(inactiveOpt);
          statusSel.addEventListener('change', function () {
            api('PATCH', '/super-admin/users/' + this.dataset.userId, { status: this.value }).then(function (res) {
              if (res.ok) loadUsers();
            });
          });

          statusCell.innerHTML = '';
          statusCell.appendChild(statusSel);

          actionCell.textContent = 'Role/status updated on change';
        }
        tbody.appendChild(tr);
      });
    });
  }
  api('GET', '/super-admin/roles').then(function (r) { return r.ok ? r.json() : {}; }).then(function (data) {
    assignableRoles = data.roles || { admin: 'Admin', hr: 'HR', student: 'Student', instructor: 'Instructor' };
    loadUsers();
  });
  loadUsers();

  document.getElementById('sa-add-user-form').addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(this);
    var payload = { name: fd.get('name'), email: fd.get('email'), password: fd.get('password'), role: fd.get('role') || 'student' };
    api('POST', '/super-admin/users', payload).then(function (r) {
      return r.json().then(function (data) {
        if (!r.ok) return Promise.reject(data);
        return data;
      });
    }).then(function () {
      this.reset();
      loadUsers();
    }.bind(this)).catch(function (err) {
      alert(err && err.error ? err.error : 'Failed to create user');
    });
  });

  function loadAnnouncements() {
    api('GET', '/super-admin/announcements').then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (data) {
      var tbody = document.querySelector('#sa-announcements-table tbody');
      if (!tbody) return;
      tbody.innerHTML = (data.announcements || []).map(function (a) {
        return '<tr><td>' + (a.title || '') + '</td><td>' + (a.target_roles || 'all') + '</td><td><button type="button" class="btn-sa-outline" data-del="' + a.id + '">Delete</button></td></tr>';
      }).join('');
      tbody.querySelectorAll('[data-del]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (!confirm('Delete this announcement?')) return;
          api('DELETE', '/super-admin/announcements/' + btn.getAttribute('data-del')).then(function (r) { if (r.ok) loadAnnouncements(); });
        });
      });
    }).catch(function () {});
  }
  var saAnnForm = document.getElementById('sa-announcement-form');
  if (saAnnForm) {
    saAnnForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var f = this;
      api('POST', '/super-admin/announcements', { title: f.querySelector('[name=title]').value, body: f.querySelector('[name=body]').value, target_roles: f.querySelector('[name=target_roles]').value || 'all' }).then(function (r) {
        if (!r.ok) return Promise.reject();
        f.reset();
        f.querySelector('[name=target_roles]').value = 'all';
        loadAnnouncements();
      }).catch(function () { alert('Failed to add.'); });
    });
  }
  loadAnnouncements();
  showView((location.hash || '#dashboard').slice(1));
})();
  </script>
</body>
</html>
