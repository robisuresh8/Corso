<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in — Corso E-Learning</title>
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
        <div class="auth-grid" style="max-width:900px;margin:0 auto;">
          <div class="auth-copy">
            <p class="hero-badge">Sign in</p>
            <h1 class="hero-title">Access your dashboard</h1>
            <p class="hero-subtitle">Use the email and password you chose for your account.</p>
          </div>

          <div class="auth-card">
            <div class="auth-pane auth-login is-active">
              <form class="auth-form" id="user-login-form" onsubmit="return false;">
                <div class="form-row">
                  <label for="login-email">Email</label>
                  <div class="input-wrap">
                    <input id="login-email" type="email" class="input" placeholder="you@example.com" required />
                  </div>
                </div>
                <div class="form-row">
                  <label for="login-password">Password</label>
                  <div class="input-wrap">
                    <input id="login-password" type="password" class="input" placeholder="Your password" required />
                  </div>
                </div>
                <p class="auth-note" style="margin:0 0 12px;"><a class="link" href="<?= base_url('login') ?>#forgot-password">Forgot password?</a> (opens the main login page — we email a one-time link to set a new password.)</p>
                <div class="auth-error" id="login-error" role="alert" hidden></div>
                <button class="btn btn-primary auth-submit" type="submit">Login</button>
              </form>

              <div id="pw-change-wrap" style="display:none;margin-top:24px;">
                <h2 class="hero-title" style="font-size:1.1rem;margin:0 0 12px;">Change password</h2>
                <form class="auth-form" id="pw-change-form" onsubmit="return false;">
                  <div class="form-row">
                    <label for="old_password">Old password</label>
                    <div class="input-wrap">
                      <input id="old_password" type="password" class="input" required />
                    </div>
                  </div>
                  <div class="form-row">
                    <label for="new_password">New password</label>
                    <div class="input-wrap">
                      <input id="new_password" type="password" class="input" required />
                    </div>
                  </div>
                  <div class="form-row">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="input-wrap">
                      <input id="confirm_password" type="password" class="input" required />
                    </div>
                  </div>
                  <div class="auth-error" id="pw-change-error" role="alert" hidden></div>
                  <button class="btn btn-primary auth-submit" type="submit">Update password</button>
                </form>
              </div>

              <div style="margin-top:12px;">
                <a href="<?= base_url('dashboard') ?>" class="link" id="skip-change" style="display:none;">Continue to dashboard</a>
              </div>

              <p class="auth-note" style="margin-top:20px;">A <strong>temporary password</strong> is only used for <strong>first sign-in after purchase</strong>. Forgot password uses a <strong>reset link</strong> instead.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>window.CORSO_API_BASE = '<?= base_url('api') ?>';</script>
  <script>
    (function () {
      function qs(name) {
        try {
          var u = new URL(window.location.href);
          return u.searchParams.get(name);
        } catch (e) {
          return null;
        }
      }

      var apiBase = window.CORSO_API_BASE || '';
      function showError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.hidden = false;
      }

      var loginForm = document.getElementById('user-login-form');
      var loginError = document.getElementById('login-error');
      var emailEl = document.getElementById('login-email');
      var passwordEl = document.getElementById('login-password');

      var prefillEmail = qs('email') || '';
      if (prefillEmail && emailEl) emailEl.value = prefillEmail;

      function api(method, path, body, token) {
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

      async function onLogin() {
        if (!emailEl || !passwordEl) return;
        var email = (emailEl.value || '').trim();
        var password = passwordEl.value || '';
        showError(loginError, '');
        loginError.hidden = true;

        try {
          var r = await api('POST', '/auth/login', { email: email, password: password, login_surface: 'user_login' });
          var data = await r.json().catch(function(){ return {}; });
          if (!r.ok) throw new Error(data && data.error ? data.error : 'Login failed');

          var token = data.access_token || data.token || '';
          var user = data.user || {};
          if (!token) throw new Error('Missing access token');

          localStorage.setItem('apiToken', token);
          localStorage.setItem('sessionUser', JSON.stringify(user));

          var mustChange = !!(user && user.force_password_change);
          if (mustChange) {
            document.getElementById('pw-change-wrap').style.display = 'block';
            document.getElementById('skip-change').style.display = 'none';
            document.getElementById('old_password').value = password;
          } else {
            window.location.href = '<?= base_url('dashboard') ?>';
          }
        } catch (e) {
          showError(loginError, e && e.message ? e.message : 'Login failed');
        }
      }

      if (loginForm) loginForm.addEventListener('submit', onLogin);
      if (loginForm) loginForm.addEventListener('click', function (e) {
        // prevent accidental double submits
      });

      var pwChangeForm = document.getElementById('pw-change-form');
      var pwChangeError = document.getElementById('pw-change-error');
      var tokenEl = null;
      async function onChangePassword() {
        tokenEl = localStorage.getItem('apiToken') || '';
        var oldPw = (document.getElementById('old_password').value || '').trim();
        var newPw = (document.getElementById('new_password').value || '').trim();
        var confirmPw = (document.getElementById('confirm_password').value || '').trim();

        pwChangeError.hidden = true;
        if (!oldPw || !newPw) {
          showError(pwChangeError, 'Old and new password are required');
          return;
        }
        if (newPw !== confirmPw) {
          showError(pwChangeError, 'New password and confirmation do not match');
          return;
        }

        try {
          var r = await api('POST', '/auth/change-password', {
            old_password: oldPw,
            new_password: newPw
          }, tokenEl);
          var data = await r.json().catch(function(){ return {}; });
          if (!r.ok) throw new Error(data && data.error ? data.error : 'Password update failed');
          // Clear force_password_change locally; next login will not require change.
          try {
            var su = JSON.parse(localStorage.getItem('sessionUser') || '{}');
            su.force_password_change = 0;
            localStorage.setItem('sessionUser', JSON.stringify(su));
          } catch (e) {}
          window.location.href = '<?= base_url('dashboard') ?>';
        } catch (e) {
          showError(pwChangeError, e && e.message ? e.message : 'Password update failed');
        }
      }

      if (pwChangeForm) pwChangeForm.addEventListener('submit', onChangePassword);
    })();
  </script>
</body>
</html>

