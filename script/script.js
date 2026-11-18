/**
 * AMANAH STORE - LOGIN DESKTOP VERSION SCRIPT
 * Dilengkapi dengan penanganan API Backend, toggle password, dan UI Loading state
 */

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const togglePasswordBtn = document.getElementById('togglePassword');
  const eyeIcon = document.getElementById('eyeIcon');
  const eyeOffIcon = document.getElementById('eyeOffIcon');
  const btnSubmit = document.getElementById('btnSubmit');
  const rememberCheckbox = document.getElementById('rememberMe');

  // 1. Toggle Password Visibility
  if (togglePasswordBtn && passwordInput && eyeIcon && eyeOffIcon) {
    togglePasswordBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const isPassword = passwordInput.getAttribute('type') === 'password';
      
      if (isPassword) {
        passwordInput.setAttribute('type', 'text');
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
        togglePasswordBtn.setAttribute('aria-label', 'Sembunyikan kata sandi');
      } else {
        passwordInput.setAttribute('type', 'password');
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
        togglePasswordBtn.setAttribute('aria-label', 'Lihat kata sandi');
      }
    });
  }

  // 2. Form Submission Handler
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      const email = emailInput ? emailInput.value.trim() : '';
      const password = passwordInput ? passwordInput.value : '';

      if (!email || !password) {
        e.preventDefault();
        alert('Mohon isi email dan kata sandi kasir.');
        return;
      }

      // Keyframes untuk spinner jika belum ada
      if (!document.getElementById('spinner-keyframes')) {
        const style = document.createElement('style');
        style.id = 'spinner-keyframes';
        style.textContent = `@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
      }

      // Aktifkan State Loading pada tombol saat form disubmit
      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.style.opacity = '0.85';
        btnSubmit.innerHTML = `
          <svg class="btn-icon" style="animation: spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
          </svg>
          <span>Memverifikasi akun...</span>
        `;
      }

      // Lakukan submit form secara alami ke backend PHP
      loginForm.submit();
    });
  }
});
