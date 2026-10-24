// register.js
// Handles the register page form. Requires modal.js to be loaded first
// (uses window.KayeModal.setAlert).

(function () {
  var registerBtn = document.getElementById('register-submit-btn');
  if (!registerBtn) return;

  registerBtn.addEventListener('click', function () {
    var firstName = document.getElementById('register-first-name').value.trim();
    var lastName = document.getElementById('register-last-name').value.trim();
    var email = document.getElementById('register-email').value.trim();
    var phone = document.getElementById('register-phone').value.trim();
    var pass = document.getElementById('register-password').value;
    var confirm = document.getElementById('register-confirm-password').value;
    var terms = document.getElementById('register-terms').checked;

    if (!firstName || !lastName || !email || !phone || !pass || !confirm) {
      window.KayeModal.setAlert('register-error', 'register-error-text', 'Please fill in all required fields.');
      return;
    }
    if (!/^\d{11}$/.test(phone)) {
      window.KayeModal.setAlert('register-error', 'register-error-text', 'Phone number must be exactly 11 digits.');
      return;
    }
    if (pass.length < 8) {
      window.KayeModal.setAlert('register-error', 'register-error-text', 'Password must be at least 8 characters.');
      return;
    }
    if (pass !== confirm) {
      window.KayeModal.setAlert('register-error', 'register-error-text', 'Passwords do not match.');
      return;
    }
    if (!terms) {
      window.KayeModal.setAlert('register-error', 'register-error-text', 'Please agree to the Terms of Service & Privacy Policy.');
      return;
    }

    window.KayeModal.setAlert('register-error', 'register-error-text', '');

    registerBtn.disabled = true;
    var originalLabel = registerBtn.textContent;
    registerBtn.textContent = 'Creating account...';

    fetch('/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        first_name: firstName,
        last_name: lastName,
        email: email,
        phone: phone,
        password: pass,
        confirm_password: confirm,
        terms: terms ? '1' : ''
      })
    })
      .then(function (res) {
        return res.json().then(function (body) {
          return { ok: res.ok, body: body };
        });
      })
      .then(function (result) {
        if (result.ok && result.body && result.body.success) {
          document.getElementById('register-form-el').reset();
          window.KayeModal.close('register-modal');
          window.KayeModal.open('login-modal');
          window.KayeModal.setAlert('login-error', 'login-error-text', 'Account created! You can now log in.');
        } else {
          var message = (result.body && result.body.message) || 'Something went wrong. Please try again.';
          window.KayeModal.setAlert('register-error', 'register-error-text', message);
        }
      })
      .catch(function () {
        window.KayeModal.setAlert('register-error', 'register-error-text', 'Network error. Please try again.');
      })
      .finally(function () {
        registerBtn.disabled = false;
        registerBtn.textContent = originalLabel;
      });
  });
})();