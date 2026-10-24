/**
 * profile.js
 * Extracted from profile.php so the markup file holds no inline script.
 */
document.addEventListener('DOMContentLoaded', function () {
  var notice = document.getElementById('profile-notice');
  var formError = document.getElementById('profile-form-error');

  var nameInput = document.getElementById('profile-name-input');
  var emailInput = document.getElementById('profile-email-input');
  var currentPasswordInput = document.getElementById('profile-current-password');
  var newPasswordInput = document.getElementById('profile-new-password');
  var confirmPasswordInput = document.getElementById('profile-confirm-password');

  var initialValues = {
    name: nameInput.value,
    email: emailInput.value
  };

  function showNotice(message) {
    notice.textContent = message;
    notice.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(function () { notice.style.display = 'none'; }, 4000);
  }

  document.getElementById('profile-cancel-btn').addEventListener('click', function () {
    nameInput.value = initialValues.name;
    emailInput.value = initialValues.email;
    currentPasswordInput.value = '';
    newPasswordInput.value = '';
    confirmPasswordInput.value = '';
    formError.textContent = '';
  });

  document.getElementById('profile-save-btn').addEventListener('click', function () {
    formError.textContent = '';

    var name = nameInput.value.trim();
    var email = emailInput.value.trim();
    var currentPassword = currentPasswordInput.value;
    var newPassword = newPasswordInput.value;
    var confirmPassword = confirmPasswordInput.value;

    if (!name || !email) {
      formError.textContent = 'Name and email are required.';
      return;
    }

    var changingPassword = newPassword || confirmPassword || currentPassword;
    if (changingPassword) {
      if (!currentPassword) {
        formError.textContent = 'Enter your current password to set a new one.';
        return;
      }
      if (newPassword.length < 8) {
        formError.textContent = 'New password must be at least 8 characters.';
        return;
      }
      if (newPassword !== confirmPassword) {
        formError.textContent = 'New password and confirmation do not match.';
        return;
      }
    }

    document.getElementById('profile-name').textContent = name;
    document.getElementById('profile-email').textContent = email;
    document.querySelector('.nav-user-name').textContent = name;
    initialValues.name = name;
    initialValues.email = email;

    currentPasswordInput.value = '';
    newPasswordInput.value = '';
    confirmPasswordInput.value = '';

    showNotice(changingPassword ? 'Profile and password updated successfully.' : 'Profile updated successfully.');
  });
});