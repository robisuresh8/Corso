<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — Corso E-Learning</title>
  <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>?v=2" />
</head>
<body>
  <header class="header">
    <div class="container header-inner">
      <a href="<?= base_url('/') ?>" class="logo">Corso E-Learning</a>
    </div>
  </header>

  <main>
    <section class="auth" style="padding:64px 0;">
      <div class="container">
        <div class="auth-grid" style="max-width:1000px;margin:0 auto;">
          <div class="auth-copy">
            <p class="hero-badge">Admin access</p>
            <h1 class="hero-title">Sign in to manage courses</h1>
            <p class="hero-subtitle">Super Admin can grant permissions. Admins may request access.</p>
          </div>

          <div class="auth-card">
            <div class="auth-tabs" style="margin-bottom:12px;">
              <button class="tab-btn is-active" type="button" data-tab="login">Login</button>
              <button class="tab-btn" type="button" data-tab="request">Request access</button>
            </div>

            <div class="auth-pane auth-login is-active" id="tab-login">
              <form class="auth-form" id="admin-login-form" onsubmit="return false;">
                <div class="form-row">
                  <label for="admin-email">Email</label>
                  <div class="input-wrap">
                    <input id="admin-email" type="email" class="input" placeholder="admin@example.com" required />
                  </div>
                </div>
                <div class="form-row">
                  <label for="admin-password">Password</label>
                  <div class="input-wrap">
                    <input id="admin-password" type="password" class="input" placeholder="Password" required />
                  </div>
                </div>
                <div class="auth-error" id="admin-login-error" role="alert" hidden></div>
                <button class="btn btn-primary auth-submit" type="submit">Login</button>
              </form>
            </div>

            <div class="auth-pane" id="tab-request" style="display:none;">
              <form class="auth-form" id="admin-request-form" onsubmit="return false;">
                <div class="form-row">
                  <label for="req-name">Name</label>
                  <div class="input-wrap">
                    <input id="req-name" type="text" class="input" placeholder="Your name" required />
                  </div>
                </div>
                <div class="form-row">
                  <label for="req-email">Email</label>
                  <div class="input-wrap">
                    <input id="req-email" type="email" class="input" placeholder="admin@example.com" required />
                  </div>
                </div>
                <div class="form-row">
                  <label for="req-phone">Phone (marketing only)</label>
                  <div class="input-wrap">
                    <input id="req-phone" type="text" class="input" placeholder="+1 555 123 4567" />
                  </div>
                </div>
                <div class="form-row">
                  <label for="req-password">Password</label>
                  <div class="input-wrap">
                    <input id="req-password" type="password" class="input" placeholder="Choose a password" required />
                  </div>
                </div>
                <div class="auth-error" id="req-error" role="alert" hidden></div>
                <div style="display:flex;gap:10px;">
                  <button class="btn btn-primary auth-submit" type="submit">Request access</button>
                  <button class="btn btn-outline auth-submit" type="button" id="switch-to-login">Back to login</button>
                </div>
              </form>
              <div style="margin-top:12px;color:var(--text-muted);font-size:.95rem;">
                Your account will be created as <strong>inactive</strong> until Super Admin activates it and assigns permissions.
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>window.CORSO_API_BASE = '<?= base_url('api') ?>';</script>
  <script>
    (function () {
      function api(method, path, body, token) {
        var apiBase = window.CORSO_API_BASE || '';
        var headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        if (body && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
          headers['Content-Type'] = 'application/json';
        }
        return fetch(apiBase + path, {
          method: method,
          headers: headers,
          body: body ? JSON.stringify(body) : undefined
        });
      }

      function showError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.hidden = false;
      }

      // Tabs
      document.querySelectorAll('.tab-btn[data-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var tab = btn.getAttribute('data-tab');
          document.querySelectorAll('.tab-btn[data-tab]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
          document.getElementById('tab-login').style.display = (tab === 'login') ? '' : 'none';
          document.getElementById('tab-request').style.display = (tab === 'request') ? '' : 'none';
        });
      });

      var loginForm = document.getElementById('admin-login-form');
      var loginError = document.getElementById('admin-login-error');
      var emailEl = document.getElementById('admin-email');
      var passwordEl = document.getElementById('admin-password');

      if (loginForm) {
        loginForm.addEventListener('submit', async function () {
          loginError.hidden = true;
          var email = (emailEl.value || '').trim();
          var password = passwordEl.value || '';
          try {
            var r = await api('POST', '/auth/login', { email: email, password: password, login_surface: 'user_login' });
            var data = await r.json().catch(function(){ return {}; });
            if (!r.ok) throw new Error(data && data.error ? data.error : 'Login failed');

            var token = data.access_token || data.token || '';
            if (!token) throw new Error('Missing access token');

            var user = data.user || {};
            localStorage.setItem('apiToken', token);
            localStorage.setItem('sessionUser', JSON.stringify(user));

            var role = String(user.role || '').toLowerCase();
            if (role === 'super_admin') {
              window.location.href = '<?= base_url('super-admin') ?>';
            } else {
              window.location.href = '<?= base_url('admin') ?>';
            }
          } catch (e) {
            showError(loginError, e && e.message ? e.message : 'Login failed');
          }
        });
      }

      var switchBtn = document.getElementById('switch-to-login');
      if (switchBtn) switchBtn.addEventListener('click', function () { window.location.reload(); });

      var reqForm = document.getElementById('admin-request-form');
      var reqError = document.getElementById('req-error');
      if (reqForm) {
        reqForm.addEventListener('submit', async function () {
          reqError.hidden = true;
          var payload = {
            name: (document.getElementById('req-name').value || '').trim(),
            email: (document.getElementById('req-email').value || '').trim(),
            phone: (document.getElementById('req-phone').value || '').trim(),
            password: document.getElementById('req-password').value || ''
          };
          try {
            var r = await api('POST', '/auth/request-admin', payload);
            var data = await r.json().catch(function(){ return {}; });
            if (!r.ok) throw new Error(data && data.error ? data.error : 'Request failed');
            alert('Request submitted. Super Admin will activate and assign permissions.');
            window.location.reload();
          } catch (e) {
            showError(reqError, e && e.message ? e.message : 'Request failed');
          }
        });
      }
    })();
  </script>
</body>
</html>

