<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create an Account · Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/password_toggle.css')?>">
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a href="<?=site_url('/login')?>" class="brand">
      <svg class="brand-mark" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 3C13 10 10 15 10 21c0 6 4.5 11 10 11s10-5 10-11c0-2.6-1-5-2.5-7 0 3-1.5 5-3 5.5.8-3 .3-7-2-10-1 3-3 5.5-5.5 7.5-1.7 1.3-2.5 3-2.5 5 0 3 2.2 5.5 5.5 5.5" stroke="#D9A441" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="brand-text">Kaye's<br>Barbeque<span>Smokehouse &amp; Grill</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="<?=site_url('/')?>">Home</a></li>
      <li><a href="<?=site_url('/menu')?>">Barbeques</a></li>
    </ul>
    <div class="nav-actions">
      <span id="nav-auth-slot"><a href="#" class="login-link" data-open-modal="login-modal">Log In</a></span>
    </div>
  </nav>
</header>

<div class="modal-overlay" id="login-modal" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
  <div class="modal-box login-modal-box">
    <button type="button" class="modal-close login-modal-close" data-close-modal="login-modal" aria-label="Close">&times;</button>
    <div class="login-modal-accent"></div>

    <div class="login-modal-layout">

      <div class="login-modal-content">
        <h2 id="login-modal-title" class="login-modal-title">Welcome back</h2>
        <p class="login-modal-sub">Log in to order and check out faster.</p>

        <form id="login-form-el">
          <div class="field">
            <label for="login-email">Email address</label>
            <input type="email" id="login-email" name="login-email" placeholder="you@email.com" autocomplete="email">
          </div>

          <div class="field">
            <div class="field-meta">
              <label for="login-password">Password</label>
              <a href="#" data-close-modal="login-modal" data-open-modal="forgot-modal">Forgot password?</a>
            </div>
            <input type="password" id="login-password" name="login-password" placeholder="••••••••">
          </div>

          <div class="check-row">
            <input type="checkbox" id="remember-me" name="remember-me">
            <label for="remember-me" style="margin:0;">Keep me signed in</label>
          </div>

          <div class="auth-alert" id="login-error">
            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M10 6v5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="10" cy="13.5" r="0.9" fill="currentColor"/>
            </svg>
            <span id="login-error-text"></span>
          </div>

          <button type="button" class="btn btn-primary btn-block" id="login-submit-btn">Log In</button>
        </form>

        <p class="auth-switch">New to Kaye's? <a href="#" data-close-modal="login-modal" data-open-modal="register-modal">Create an account</a></p>
      </div>

    </div>
  </div>
</div>

<div class="modal-overlay" id="register-modal" role="dialog" aria-modal="true" aria-labelledby="register-modal-title">
  <div class="modal-box login-modal-box">
    <button type="button" class="modal-close login-modal-close" data-close-modal="register-modal" aria-label="Close">&times;</button>
    <div class="login-modal-accent"></div>

    <div class="login-modal-layout">

      <div class="login-modal-content">
        <h2 id="register-modal-title" class="login-modal-title">Create Account</h2>
        <p class="login-modal-sub">Sign up to start ordering in seconds.</p>

        <form id="register-form-el">
          <div class="field-row">
            <div class="field">
              <label for="register-first-name">First name</label>
              <input type="text" id="register-first-name" name="register-first-name" placeholder="Juan">
            </div>
            <div class="field">
              <label for="register-last-name">Last name</label>
              <input type="text" id="register-last-name" name="register-last-name" placeholder="Dela Cruz">
            </div>
          </div>

          <div class="field">
            <label for="register-email">Email address</label>
            <input type="email" id="register-email" name="register-email" placeholder="you@email.com">
          </div>

          <div class="field">
            <label for="register-phone">Phone number</label>
            <input type="tel" id="register-phone" name="register-phone" placeholder="09XX XXX XXXX" inputmode="numeric" maxlength="11" pattern="[0-9]{11}">
          </div>

          <div class="field-row">
            <div class="field">
              <label for="register-password">Password</label>
              <input type="password" id="register-password" name="register-password" placeholder="••••••••">
            </div>
            <div class="field">
              <label for="register-confirm-password">Confirm password</label>
              <input type="password" id="register-confirm-password" name="register-confirm-password" placeholder="••••••••">
            </div>
          </div>

          <div class="check-row">
            <input type="checkbox" id="register-terms" name="register-terms">
            <label for="register-terms" style="margin:0;">I agree to the Terms of Service &amp; Privacy Policy</label>
          </div>

          <div class="auth-alert" id="register-error">
            <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.4"/>
              <path d="M10 6v5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
              <circle cx="10" cy="13.5" r="0.9" fill="currentColor"/>
            </svg>
            <span id="register-error-text"></span>
          </div>

          <button type="button" class="btn btn-primary btn-block" id="register-submit-btn">Create Account</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="#" data-close-modal="register-modal" data-open-modal="login-modal">Log in</a></p>
      </div>

    </div>
  </div>
</div>

<section class="hero">
  <div class="container hero-inner">
    <div>
      <div class="eyebrow">Join the Cookout</div>
      <h1>Grab a plate,<br>pull up a <em>chair</em>.</h1>
      <p class="hero-sub">Create an account to browse the full menu, place orders, and track them from grill to table.</p>
      <div class="hero-actions">
        <a href="#" class="btn btn-primary" data-open-modal="register-modal">Create Account</a>
        <a href="#" class="btn btn-outline" data-open-modal="login-modal">Log In</a>
      </div>
    </div>
    <div class="hero-figure">
      <svg viewBox="0 0 200 200" width="70%" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="70" stroke="#6E2A1C" stroke-width="2" opacity="0.5"/>
        <path d="M60 130c8-30 4-45-6-60 14 4 22 14 26 26 4-16 14-26 28-32-6 16-4 28 2 40 6-14 18-22 30-24-10 14-12 26-6 40-6 14-20 22-36 22-16 0-32-4-38-12z" fill="#C64B23"/>
        <path d="M60 130c8-30 4-45-6-60 14 4 22 14 26 26 4-16 14-26 28-32-6 16-4 28 2 40 6-14 18-22 30-24-10 14-12 26-6 40-6 14-20 22-36 22-16 0-32-4-38-12z" fill="#D9A441" opacity="0.35"/>
      </svg>
      <div class="smoke-ring" style="width:60px;height:60px;left:35%;top:60%;animation-delay:0s;"></div>
      <div class="smoke-ring" style="width:40px;height:40px;left:55%;top:70%;animation-delay:2.3s;"></div>
      <div class="smoke-ring" style="width:80px;height:80px;left:25%;top:50%;animation-delay:4.6s;"></div>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4>Kaye's Barbeque</h4>
        <p>Balongis, Balulang</p><br>Cagayan de Oro City, Philippines</p>
        <p>Open Mon–Sun · 11am – 9pm</p>
      </div>

      <div>
        <h4>Contact</h4>
        <p>kaye@gmail.com</p>
        <p>(082) 555-0143</p>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>

<div class="modal-overlay" id="forgot-modal" role="dialog" aria-modal="true" aria-labelledby="forgot-modal-title">
  <div class="modal-box login-modal-box">
    <button type="button" class="modal-close login-modal-close" data-close-modal="forgot-modal" aria-label="Close">&times;</button>
    <div class="login-modal-accent"></div>

    <div class="login-modal-layout">

      <div class="login-modal-content">
        <h2 id="forgot-modal-title" class="login-modal-title">Forgot password?</h2>
        <p class="login-modal-sub" id="forgot-sub">Enter the email address of your account.</p>

        <div class="auth-alert" id="forgot-error" role="status">
          <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.4"/>
            <path d="M10 6v5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            <circle cx="10" cy="13.5" r="0.9" fill="currentColor"/>
          </svg>
          <span id="forgot-error-text"></span>
        </div>

        <form id="forgot-form-el" data-action="<?=site_url('/forgot-password')?>" novalidate>
          <?= csrf_field() ?>
          <div class="field">
            <label for="forgot-email">Email address</label>
            <input type="email" id="forgot-email" name="email" placeholder="you@email.com" autocomplete="email">
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="forgot-submit-btn">Continue</button>
        </form>

        <form id="forgot-reset-form-el" data-action="<?=site_url('/forgot-password/reset')?>" style="display:none;" novalidate>
          <?= csrf_field() ?>
          <p id="forgot-email-shown" style="font-weight:600;margin:0 0 14px;overflow-wrap:anywhere;"></p>
          <div class="field">
            <label for="forgot-new-password">New password</label>
            <input type="password" id="forgot-new-password" name="password" placeholder="••••••••" autocomplete="new-password">
          </div>
          <div class="field">
            <label for="forgot-confirm-password">Confirm new password</label>
            <input type="password" id="forgot-confirm-password" name="confirm_password" placeholder="••••••••" autocomplete="new-password">
          </div>
          <button type="submit" class="btn btn-primary btn-block" id="forgot-reset-btn">Change Password</button>
        </form>

        <p class="auth-switch">Remembered it? <a href="#" id="forgot-back-login" data-close-modal="forgot-modal" data-open-modal="login-modal">Back to log in</a></p>
      </div>

    </div>
  </div>
</div>

<script src="<?=base_url('js/login/modal.js')?>"></script>
<script src="<?=base_url('js/login/login.js')?>"></script>
<script src="<?=base_url('js/login/register.js')?>"></script>
<script src="<?=base_url('js/customer/password_toggle.js')?>"></script>
<script src="<?=base_url('js/login/forgot_password.js')?>"></script>
<script>
  // This page is dedicated to registration, so open the modal automatically.
  document.addEventListener('DOMContentLoaded', function () {
    window.KayeModal.open('register-modal');
  });
</script>

</body>
</html>