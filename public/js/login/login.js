// login.js
// Handles the login modal form. Requires modal.js to be loaded first
// (uses window.KayeModal.setAlert).

(function () {
  var loginBtn = document.getElementById('login-submit-btn');
  if (!loginBtn) return;

  loginBtn.addEventListener('click', function () {
    var email = document.getElementById('login-email').value.trim();
    var pass = document.getElementById('login-password').value;

    if (!email || !pass) {
      window.KayeModal.setAlert('login-error', 'login-error-text', 'Please enter both email and password.');
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      window.KayeModal.setAlert('login-error', 'login-error-text', 'Please enter a valid email address.');
      return;
    }

    window.KayeModal.setAlert('login-error', 'login-error-text', '');

    loginBtn.disabled = true;
    var originalLabel = loginBtn.textContent;
    loginBtn.textContent = 'Logging in...';

    fetch('/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email,
        password: pass,
        remember: document.getElementById('remember-me').checked ? '1' : ''
      })
    })
      .then(function (res) {
        return res.json().then(function (body) {
          return { ok: res.ok, body: body };
        });
      })
      .then(function (result) {
        if (result.ok && result.body && result.body.success) {
          window.location.href = result.body.redirect || '/customer/home';
        } else {
          var message = (result.body && result.body.message) || 'Invalid email or password.';
          window.KayeModal.setAlert('login-error', 'login-error-text', message);
        }
      })
      .catch(function () {
        window.KayeModal.setAlert('login-error', 'login-error-text', 'Network error. Please try again.');
      })
      .finally(function () {
        loginBtn.disabled = false;
        loginBtn.textContent = originalLabel;
      });
  });
})();