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
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const username = emailInput.value.trim();
      const password = passwordInput.value;
      const remember = rememberCheckbox ? rememberCheckbox.checked : false;

      if (!username || !password) {
        alert('Mohon isi email/username dan kata sandi kasir.');
        return;
      }

      // Aktifkan State Loading pada tombol
      const originalHtml = btnSubmit.innerHTML;
      btnSubmit.disabled = true;
      btnSubmit.style.opacity = '0.85';
      btnSubmit.innerHTML = `
        <svg class="btn-icon" style="animation: spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
          <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
        </svg>
        <span>Memverifikasi akun...</span>
      `;

      // Keyframes untuk spinner jika belum ada
      if (!document.getElementById('spinner-keyframes')) {
        const style = document.createElement('style');
        style.id = 'spinner-keyframes';
        style.textContent = `@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
      }

      // Simulasi panggilan API (atau hubungkan langsung ke Backend jika siap)
      const API_ENDPOINT = '/api/auth/login'; // Endpoint standar Toko Amanah

      try {
        /*
        // CONTOH KONEKSI BACKEND AKTIF:
        const response = await fetch(API_ENDPOINT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ username, password, remember })
        });
        const result = await response.json();
        if (response.ok && result.status === 'success') {
          window.location.href = '../dashboard/index.html';
          return;
        }
        */

        // Simulasi delay respon jaringan
        await new Promise(resolve => setTimeout(resolve, 1000));

        console.log('Login attempt Desktop:', { username, remember });
        alert(`Login Berhasil!\nSelamat datang kembali di Amanah Store, ${username}.\nMengalihkan ke Dashboard Toko Amanah...`);

      } catch (err) {
        console.error('Error saat login:', err);
        alert('Terjadi kesalahan koneksi ke server.');
      } finally {
        btnSubmit.disabled = false;
        btnSubmit.style.opacity = '1';
        btnSubmit.innerHTML = originalHtml;
      }
    });
  }
});
