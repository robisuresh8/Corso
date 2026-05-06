<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password — Corso E‑Learning</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
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
        <a href="<?= base_url('user-login') ?>">Login</a>
      </nav>
      <button class="menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
    </div>
  </header>

  <main>
    <section class="auth">
      <div class="container">
        <div class="auth-grid">
          <div class="auth-copy">
            <p class="hero-badge">Account</p>
            <h1 class="hero-title">Set a new password</h1>
            <p class="hero-subtitle" id="reset-intro">Use the link from your email. Choose a strong password, then sign in.</p>
          </div>
          <div class="auth-card">
            <form class="auth-form" id="reset-form" onsubmit="return false;">
              <div class="form-row">
                <label for="new-password">New password</label>
                <div class="input-wrap">
                  <span class="input-icon">🔒</span>
                  <input id="new-password" type="password" class="input" placeholder="At least 8 characters" aria-label="New password" required minlength="8" autocomplete="new-password" />
                </div>
              </div>
              <div class="form-row">
                <label for="confirm-password">Confirm password</label>
                <div class="input-wrap">
                  <span class="input-icon">🔒</span>
                  <input id="confirm-password" type="password" class="input" placeholder="Re-enter password" aria-label="Confirm password" required minlength="8" autocomplete="new-password" />
                </div>
              </div>
              <div class="auth-error" id="reset-error" role="alert" hidden></div>
              <p class="auth-note" id="reset-success" hidden style="margin-bottom:12px;color:var(--accent);"></p>
              <button class="btn btn-primary auth-submit" type="submit" id="reset-submit">Update password</button>
              <p class="auth-note"><a class="link" href="<?= base_url('user-login') ?>">Back to login</a></p>
            </form>
          </div>
        </div>
      </div>
      <div class="hero-gradient"></div>
    </section>
  </main>

  <script>window.CORSO_API_BASE='<?= base_url('api') ?>';</script>
  <script src="<?= base_url('assets/js/main.js') ?>?v=2"></script>
  <script>
    (function () {
      var params = new URLSearchParams(window.location.search);
      var token = (params.get('token') || '').trim();
      var form = document.getElementById('reset-form');
      var errEl = document.getElementById('reset-error');
      var okEl = document.getElementById('reset-success');
      var intro = document.getElementById('reset-intro');
      var submitBtn = document.getElementById('reset-submit');

      function showErr(msg) {
        if (okEl) { okEl.textContent = ''; okEl.hidden = true; }
        if (errEl) {
          errEl.textContent = msg || 'Something went wrong.';
          errEl.hidden = false;
        }
      }
      function hideErr() {
        if (errEl) { errEl.textContent = ''; errEl.hidden = true; }
      }

      if (!token) {
        if (intro) intro.textContent = 'This page needs a valid link from your reset email. Open the link from your inbox, or request a new one from the login page.';
        if (form) form.style.pointerEvents = 'none';
        if (form) form.style.opacity = '0.55';
        if (submitBtn) submitBtn.disabled = true;
        showErr('Missing reset token. Use the link from your email.');
        return;
      }

      if (!form) return;
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideErr();
        var p1 = document.getElementById('new-password').value;
        var p2 = document.getElementById('confirm-password').value;
        if (p1.length < 8) {
          showErr('Password must be at least 8 characters.');
          return;
        }
        if (p1 !== p2) {
          showErr('Passwords do not match.');
          return;
        }
        if (!window.corsoApi || !window.corsoApi.base) {
          showErr('Cannot reach the server. Check that the site is running.');
          return;
        }
        var path = '/auth/reset-password/' + encodeURIComponent(token);
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }
        window.corsoApi.post(path, { password: p1 })
          .then(function (r) { return r.json().then(function (data) { return { r: r, data: data }; }); })
          .then(function (o) {
            if (!o.r.ok) {
              var msg = (o.data && o.data.error) ? o.data.error : 'Could not reset password.';
              showErr(msg);
              if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Update password'; }
              return;
            }
            if (okEl) {
              okEl.textContent = (o.data && o.data.message) ? o.data.message : 'Password updated. Redirecting…';
              okEl.hidden = false;
            }
            setTimeout(function () {
              window.location.href = '<?= base_url('user-login') ?>';
            }, 1500);
          })
          .catch(function () {
            showErr('Network error. Try again.');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Update password'; }
          });
      });
    })();
  </script>
</body>
</html>
