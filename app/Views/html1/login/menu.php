<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
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
      <li><a href="<?=site_url('/menu')?>" class="active">Menu</a></li>
    </ul>
    <div class="nav-actions">
      <span id="nav-auth-slot"><a href="#" class="login-link active" data-open-modal="login-modal">Log In</a></span>
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
            <input type="tel" id="register-phone" name="register-phone" placeholder="09XX XXX XXXX">
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

<div class="modal-overlay" id="cart-modal" role="dialog" aria-modal="true" aria-labelledby="cart-modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="cart-modal-title">Your Cart</h3>
      <button class="modal-close" id="cart-modal-close" aria-label="Close cart">&times;</button>
    </div>
    <div class="modal-body" id="cart-modal-body">
      <div class="modal-empty">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 4h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="10" cy="21" r="1.4" fill="currentColor"/>
          <circle cx="17" cy="21" r="1.4" fill="currentColor"/>
        </svg>
        <p>Your cart is empty</p>
        <p class="modal-empty-sub">Items you add on the Barbeques page will show up here.</p>
      </div>
    </div>
    <div class="modal-footer" id="cart-modal-footer">
      <a href="<?=site_url('/barbeques')?>" class="btn btn-primary" style="width:100%;text-align:center;">Browse Barbeques</a>
    </div>
  </div>
</div>

<div class="modal-overlay" id="item-modal" role="dialog" aria-modal="true" aria-labelledby="item-modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="item-modal-title">Item Name</h3>
      <button class="modal-close" id="item-modal-close" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body">
      <div class="item-modal-thumb" id="item-modal-thumb"></div>
      <p class="item-modal-desc" id="item-modal-desc"></p>
      <div class="item-modal-price" id="item-modal-price"></div>
      <div class="qty-stepper">
        <button type="button" class="stepper-btn" id="item-qty-minus" aria-label="Decrease quantity">&minus;</button>
        <input type="text" class="stepper-input" id="item-qty-input" value="1" inputmode="numeric">
        <button type="button" class="stepper-btn" id="item-qty-plus" aria-label="Increase quantity">+</button>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-primary" id="item-add-to-cart" style="width:100%;">Add to Cart</button>
    </div>
  </div>
</div>

<section class="section-dark" style="padding:60px 0 48px;">
  <div class="container menu-hero-inner">
    <div>
      <div class="eyebrow">The Menu</div>
      <h1 style="color:#F1E6D3;font-size:42px;margin-bottom:14px;">Pinoy Barbeque, Off the Grill</h1>
      <p class="menu-intro" style="color:#cbb9a1;">Charcoal-grilled the street-side way — sweet, smoky, and basted till it's sticky. Skewers are sold per stick, inasal by the plate, and everything's better with extra rice.</p>
    </div>
    <div class="menu-banner">
      <svg viewBox="0 0 260 200" width="100%" xmlns="http://www.w3.org/2000/svg">
        <!-- grill bed -->
        <rect x="20" y="150" width="220" height="14" rx="3" fill="#2A211B"/>
        <line x1="30" y1="150" x2="30" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="60" y1="150" x2="60" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="90" y1="150" x2="90" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="120" y1="150" x2="120" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="150" y1="150" x2="150" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="180" y1="150" x2="180" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <line x1="210" y1="150" x2="210" y2="164" stroke="#6E2A1C" stroke-width="3"/>
        <!-- coals glow -->
        <ellipse cx="130" cy="168" rx="100" ry="10" fill="#C64B23" opacity="0.35"/>
        <!-- skewer 1 -->
        <line x1="18" y1="128" x2="200" y2="94" stroke="#D9A441" stroke-width="2.5" stroke-linecap="round"/>
        <rect x="38" y="112" width="24" height="18" rx="6" fill="#C64B23" transform="rotate(-10 50 121)"/>
        <rect x="72" y="105" width="24" height="18" rx="6" fill="#D9A441" transform="rotate(-10 84 114)"/>
        <rect x="106" y="98" width="24" height="18" rx="6" fill="#C64B23" transform="rotate(-10 118 107)"/>
        <!-- skewer 2 -->
        <line x1="45" y1="140" x2="230" y2="108" stroke="#D9A441" stroke-width="2.5" stroke-linecap="round"/>
        <rect x="70" y="124" width="22" height="16" rx="6" fill="#E0602F" transform="rotate(-9 81 132)"/>
        <rect x="102" y="118" width="22" height="16" rx="6" fill="#C64B23" transform="rotate(-9 113 126)"/>
        <rect x="134" y="112" width="22" height="16" rx="6" fill="#D9A441" transform="rotate(-9 145 120)"/>
        <rect x="166" y="106" width="22" height="16" rx="6" fill="#E0602F" transform="rotate(-9 177 114)"/>
        <!-- smoke -->
        <circle cx="60" cy="70" r="10" fill="none" stroke="#F1E6D3" stroke-width="1.5" opacity="0.3"/>
        <circle cx="150" cy="55" r="14" fill="none" stroke="#F1E6D3" stroke-width="1.5" opacity="0.25"/>
        <circle cx="200" cy="75" r="8" fill="none" stroke="#F1E6D3" stroke-width="1.5" opacity="0.3"/>
      </svg>
      <div class="smoke-ring" style="width:36px;height:36px;left:22%;top:30%;animation-delay:0.5s;"></div>
      <div class="smoke-ring" style="width:26px;height:26px;left:62%;top:20%;animation-delay:3s;"></div>
    </div>
  </div>
</section>

<div class="grate-divider"></div>

<section>
  <div class="container">

    <div class="notice" id="menu-notice">
      <strong>Note:</strong> Tap any item to choose a quantity and add it to your cart, then check out from the cart or the Order page.
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <line x1="6" y1="34" x2="34" y2="6" stroke="#C64B23" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="14" cy="26" r="5.5" fill="#C64B23"/>
            <circle cx="21.5" cy="18.5" r="5.5" fill="#D9A441"/>
            <circle cx="29" cy="11" r="5.5" fill="#E0602F"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Skewers &amp; Isaw</h3>
      </div>
      <div class="menu-grid">

                <button type="button" class="menu-card" data-id="isaw-manok" data-name="Isaw Manok" data-price="20" data-unit="stick" data-desc="Grilled chicken intestine, charred at the edges, brushed with basting sauce.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/isaw.jpg') ?>" alt="Pork Barbeque" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
          <div class="menu-card-body">
            <div class="menu-card-name">Classic Pork BBQ <span class="tag">Signature</span></div>
            <p class="menu-card-desc">Marinated pork skewers, charcoal-grilled and basted with sweet BBQ sauce.</p>
            <div class="menu-card-price">₱35 / stick</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="isaw-manok" data-name="Isaw Manok" data-price="20" data-unit="stick" data-desc="Grilled chicken intestine, charred at the edges, brushed with basting sauce.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/isaw.jpg') ?>" alt="Grilled isaw skewers" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Isaw Manok</div>
            <p class="menu-card-desc">Grilled chicken intestine, charred at the edges, brushed with basting sauce.</p>
            <div class="menu-card-price">₱20 / stick</div>
          </div>
        </button>


                <button type="button" class="menu-card" data-id="betamax" data-name="Betamax" data-price="25" data-unit="stick" data-desc="Grilled seasoned pork blood cubes, named for their tape-like shape.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/betamax.png') ?>" alt="Grilled betamax skewers" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Betamax</div>
            <p class="menu-card-desc">Grilled seasoned pork blood cubes, named for their tape-like shape.</p>
            <div class="menu-card-price">₱25 / stick</div>
          </div>
        </button>



                <button type="button" class="menu-card" data-id="chicken-batikulon" data-name="Chicken Batikulon" data-price="20" data-unit="stick" data-desc="Grilled chicken gizzards, marinated and skewered — chewy and full of char.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/chicken_baticolon.jpg') ?>" alt="Grilled chicken batikulon skewers" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Chicken Batikulon</div>
            <p class="menu-card-desc">Grilled chicken gizzards, marinated and skewered — chewy and full of char.</p>
            <div class="menu-card-price">₱20 / stick</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="chicken-head" data-name="Chicken Head" data-price="25" data-unit="piece" data-desc="Grilled chicken head, a street-side dare that's crispy skin and pure flavor.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/chicken_head.png') ?>" alt="Grilled chicken head skewers" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Chicken Head <span class="tag">Adidas</span></div>
            <p class="menu-card-desc">Grilled chicken head, a street-side dare that's crispy skin and pure flavor.</p>
            <div class="menu-card-price">₱25 / piece</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="chicken-neck" data-name="Chicken Neck" data-price="20" data-unit="piece" data-desc="Grilled chicken neck skewers, basted and charred, popular for the skin.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/chicken_neck.jpg') ?>" alt="Grilled chicken neck skewers" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Chicken Neck</div>
            <p class="menu-card-desc">Grilled chicken neck skewers, basted and charred, popular for the skin.</p>
            <div class="menu-card-price">₱20 / piece</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="grilled-hotdog" data-name="Grilled Hotdog" data-price="20" data-unit="stick" data-desc="Sweet-style hotdogs, skewered and grilled with a touch of char.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/hot_dog.jpg') ?>" alt="Grilled hotdogs on the grill" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Grilled Hotdog <span class="tag">Kids' Pick</span></div>
            <p class="menu-card-desc">Sweet-style hotdogs, skewered and grilled with a touch of char.</p>
            <div class="menu-card-price">₱20 / stick</div>
          </div>
        </button>

      </div>
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 26c4-10 12-18 20-16 4 10-4 20-14 22-3-1-5-3-6-6z" fill="#4a7fc4"/>
            <path d="M20 16c3 1 5 4 5 7" stroke="#F1E6D3" stroke-width="1.5" fill="none" stroke-linecap="round" opacity="0.6"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Grilled Seafood</h3>
      </div>
      <div class="menu-grid">

                <button type="button" class="menu-card" data-id="tuna-panga" data-name="Tuna Panga" data-price="280" data-unit="order" data-desc="Grilled tuna jaw, marinated and charcoal-cooked — juicy, collagen-rich, and great for sharing.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/tuna_panga.jpg') ?>" alt="Grilled tuna panga" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Tuna Panga <span class="tag">Serves 2-3</span></div>
            <p class="menu-card-desc">Grilled tuna jaw, marinated and charcoal-cooked — juicy, collagen-rich, and great for sharing.</p>
            <div class="menu-card-price">₱280 / order</div>
          </div>
        </button>

      </div>
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <path d="M13 7c6-3 15-1 17 7 1 5-2 10-7 12-1 4-4 7-9 7-5 0-8-4-6-9 1-3 4-5 5-8-2-3-3-6 0-9z" fill="#D9A441"/>
            <line x1="8" y1="35" x2="32" y2="35" stroke="#C64B23" stroke-width="2" stroke-linecap="round"/>
            <line x1="12" y1="31" x2="16" y2="35" stroke="#C64B23" stroke-width="2" stroke-linecap="round"/>
            <line x1="24" y1="31" x2="20" y2="35" stroke="#C64B23" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Inasal</h3>
      </div>
      <div class="menu-grid">

                <button type="button" class="menu-card" data-id="chicken-inasal" data-name="Chicken Inasal" data-price="165" data-unit="plate" data-desc="Annatto and lemongrass-marinated chicken, grilled and basted till golden.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/inasal.jpg') ?>" alt="Grilled tuna panga" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"><svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><ellipse cx="20" cy="24" rx="15" ry="6" fill="#D9A441" opacity="0.35"/><path d="M13 10c5-3 12-1 14 5 1 4-1 8-5 10-1 3-3 5-7 5-4 0-6-3-5-7 1-2 3-4 3-6-2-2-3-4 0-7z" fill="#D9A441"/><line x1="10" y1="27" x2="28" y2="27" stroke="#C64B23" stroke-width="1.5" stroke-linecap="round"/></svg></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Chicken Inasal <span class="tag">Bacolod-Style</span></div>
            <p class="menu-card-desc">Annatto and lemongrass-marinated chicken, grilled and basted till golden.</p>
            <div class="menu-card-price">₱165 / plate</div>
          </div>
        </button>




      </div>
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="14" width="15" height="13" rx="6.5" fill="#C64B23"/>
            <rect x="21" y="14" width="15" height="13" rx="6.5" fill="#D9A441"/>
            <line x1="19" y1="14" x2="19" y2="27" stroke="#1B1512" stroke-width="1.5"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Chorizo &amp; Longganisa</h3>
      </div>
      <div class="menu-grid"> 

                <button type="button" class="menu-card" data-id="grilled-chorizo-skewers" data-name="Grilled Chorizo Skewers" data-price="30" data-unit="stick" data-desc="Sweet Cebu-style chorizo, grilled whole and lightly charred.">
          <div class="menu-card-thumb" style ="overflow: hidden;"><img src="<?= base_url('babeques/chorizo.jpg') ?>" alt="Grilled tuna panga" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"><svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><line x1="6" y1="32" x2="34" y2="8" stroke="#6E2A1C" stroke-width="2" stroke-linecap="round"/><rect x="11" y="20" width="10" height="10" rx="5" fill="#C64B23" transform="rotate(-30 16 25)"/><rect x="19" y="12" width="10" height="10" rx="5" fill="#D9A441" transform="rotate(-30 24 17)"/></svg></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Grilled Chorizo Skewers</div>
            <p class="menu-card-desc">Sweet Cebu-style chorizo, grilled whole and lightly charred.</p>
            <div class="menu-card-price">₱30 / stick</div>
          </div>
        </button>





      </div>
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 20c0 8 6 13 14 13s14-5 14-13H6z" fill="#D9A441"/>
            <path d="M6 20c0-2 1.5-3.5 3.5-3.5h21c2 0 3.5 1.5 3.5 3.5" stroke="#C64B23" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M16 10c-1.5 2 1 3 -0.5 5M22 8c-1.5 2 1 3 -0.5 5" stroke="#C64B23" stroke-width="1.5" fill="none" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Sides</h3>
      </div>
      <div class="menu-grid">





                  <button type="button" class="menu-card" data-id="chicken-inasal" data-name="Chicken Inasal" data-price="165" data-unit="plate" data-desc="Annatto and lemongrass-marinated chicken, grilled and basted till golden.">
          <div class="menu-card-thumb" style="overflow:hidden;"><img src="<?= base_url('babeques/atsara.jpg') ?>" alt="atsara" style="width:100%;height:100%;object-fit:cover;border-radius:12px;"></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Atchara</div>
            <p class="menu-card-desc">Pickled shredded green papaya, sweet and tangy to cut the smoke.</p>
            <div class="menu-card-price">₱35</div>
          </div>
        </button>



      </div>
    </div>

    <div class="menu-category">
      <div class="category-head">
        <div class="icon-badge">
          <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 14h20l-2.2 16.5A4 4 0 0 1 23.9 34h-7.8a4 4 0 0 1-3.9-3.5z" fill="#C64B23"/>
            <rect x="8" y="9" width="24" height="6" rx="2.5" fill="#D9A441"/>
          </svg>
        </div>
        <h3 style="border:none;padding:0;">Sawsawan (Dips)</h3>
      </div>
      <div class="menu-grid">

                <button type="button" class="menu-card" data-id="suka-t-toyo" data-name="Suka't Toyo" data-price="15" data-unit="" data-desc="Spiced vinegar and soy dip with garlic and chili — the street-side standard.">
          <div class="menu-card-thumb"><svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M11 14h18l-2.4 17.5A4 4 0 0 1 22.6 35h-9.2a4 4 0 0 1-4-3.5z" fill="#6E2A1C"/><rect x="9" y="9" width="22" height="6" rx="2.5" fill="#D9A441"/></svg></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Suka't Toyo</div>
            <p class="menu-card-desc">Spiced vinegar and soy dip with garlic and chili — the street-side standard.</p>
            <div class="menu-card-price">₱15</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="spicy-vinegar" data-name="Spicy Vinegar" data-price="15" data-unit="" data-desc="Cane vinegar steeped with siling labuyo for extra heat.">
          <div class="menu-card-thumb"><svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M11 14h18l-2.4 17.5A4 4 0 0 1 22.6 35h-9.2a4 4 0 0 1-4-3.5z" fill="#C64B23"/><rect x="9" y="9" width="22" height="6" rx="2.5" fill="#E0602F"/><path d="M17 19c1 2-1 3 0 5s3 1 3 3" stroke="#F1E6D3" stroke-width="1.3" fill="none" stroke-linecap="round"/></svg></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Spicy Vinegar</div>
            <p class="menu-card-desc">Cane vinegar steeped with siling labuyo for extra heat.</p>
            <div class="menu-card-price">₱15</div>
          </div>
        </button>

                <button type="button" class="menu-card" data-id="banana-ketchup-glaze" data-name="Banana Ketchup Glaze" data-price="15" data-unit="" data-desc="Sweet and tangy, brushed on skewers fresh off the grill.">
          <div class="menu-card-thumb"><svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M11 14h18l-2.4 17.5A4 4 0 0 1 22.6 35h-9.2a4 4 0 0 1-4-3.5z" fill="#D9A441"/><rect x="9" y="9" width="22" height="6" rx="2.5" fill="#6E2A1C"/></svg></div>
          <div class="menu-card-body">
            <div class="menu-card-name">Banana Ketchup Glaze</div>
            <p class="menu-card-desc">Sweet and tangy, brushed on skewers fresh off the grill.</p>
            <div class="menu-card-price">₱15</div>
          </div>
        </button>

      </div>
    </div>

  </div>
</section>

<div class="grate-divider on-dark"></div>

<section class="section-dark">
  <div class="container" style="text-align:center;">
    <h2 style="font-size:28px;">Made your picks?</h2>
    <p style="color:#cbb9a1;max-width:50ch;margin:0 auto 24px;">Head to the Order page to fill in your details and check out.</p>
    <a href="#" class="btn btn-primary" data-open-modal="login-modal">Log in Now</a>
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

</body>
</html>