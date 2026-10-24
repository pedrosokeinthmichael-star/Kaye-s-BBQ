/**
 * forgot_password.js — Kaye's Barbeque
 * -----------------------------------------------------------------
 * Drives the "Forgot password?" modal (#forgot-modal) on login.php,
 * register.php and menu.php.
 *   Step 1: customer enters their email  -> POST /forgot-password
 *   Step 2: customer enters a new password -> POST /forgot-password/reset
 * Load after modal.js.
 */
(function () {
  var step1 = document.getElementById('forgot-form-el');
  var step2 = document.getElementById('forgot-reset-form-el');
  if (!step1 || !step2) return;

  var sub = document.getElementById('forgot-sub');
  var emailInput = document.getElementById('forgot-email');
  var emailShown = document.getElementById('forgot-email-shown');
  var btn1 = document.getElementById('forgot-submit-btn');
  var btn2 = document.getElementById('forgot-reset-btn');
  var box = document.getElementById('forgot-error');
  var text = document.getElementById('forgot-error-text');
  var busy = false;

  var SUB1 = "Enter the email address of your account.";

  function show(msg, ok) {
    text.textContent = msg;
    box.style.color = ok ? '#4a7a2e' : '';
    box.style.background = ok ? 'rgba(84,130,53,.12)' : '';
    box.style.borderColor = ok ? 'rgba(84,130,53,.28)' : '';
    box.classList.add('show');
  }
  function hide() { box.classList.remove('show'); }

  function goStep1() {
    step2.style.display = 'none';
    step1.style.display = '';
    sub.textContent = SUB1;
    step2.reset();
    hide();
  }
  function goStep2(email) {
    step1.style.display = 'none';
    step2.style.display = '';
    emailShown.textContent = email;
    sub.textContent = 'Choose a new password for:';
    hide();
    var first = document.getElementById('forgot-new-password');
    if (first) first.focus();
  }

  // The CSRF token changes after each POST; keep every form's copy current.
  function refreshCsrf(res) {
    if (!res || !res.csrfName) return;
    Array.prototype.forEach.call(
      document.querySelectorAll('#forgot-modal input[name="' + res.csrfName + '"]'),
      function (i) { i.value = res.csrfHash; }
    );
  }

  function post(form, button, label, onDone) {
    if (busy) return;
    busy = true;
    button.disabled = true;
    button.textContent = 'Please wait…';
    hide();

    fetch(form.getAttribute('data-action'), {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) {
        if (r.status === 403 && r.headers.get('content-type') && r.headers.get('content-type').indexOf('json') === -1) {
          throw new Error('csrf');
        }
        return r.json();
      })
      .then(function (res) { refreshCsrf(res); onDone(res); })
      .catch(function (err) {
        show(err && err.message === 'csrf'
          ? 'Your page session expired. Please refresh the page and try again.'
          : 'Something went wrong. Please try again.', false);
      })
      .then(function () {
        busy = false;
        button.disabled = false;
        button.textContent = label;
      });
  }

  step1.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!emailInput.value.trim()) { show('Please enter your email address.', false); emailInput.focus(); return; }
    post(step1, btn1, 'Continue', function (res) {
      if (res.success) goStep2(res.email); else show(res.message || 'Something went wrong.', false);
    });
  });

  step2.addEventListener('submit', function (e) {
    e.preventDefault();
    post(step2, btn2, 'Change Password', function (res) {
      if (res.success) {
        show(res.message, true);
        step2.reset();
        // Take them back to the login form shortly after.
        setTimeout(function () {
          var back = document.getElementById('forgot-back-login');
          if (back) back.click();
          goStep1();
          emailInput.value = '';
        }, 1800);
      } else if (res.expired) {
        goStep1();
        show(res.message, false);
      } else {
        show(res.message || 'Something went wrong.', false);
      }
    });
  });

  emailInput.addEventListener('input', hide);

  // Opening the modal always starts from step 1.
  document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('[data-open-modal="forgot-modal"]')) goStep1();
  });
})();