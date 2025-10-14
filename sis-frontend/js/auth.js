(function initThemeLocal() {
  const themeLink = document.getElementById('theme-stylesheet');
  const toggleBtn = document.getElementById('theme-toggle');
  const saved = localStorage.getItem('sis-theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const initial = saved || (prefersDark ? 'dark' : 'light');
  const applyTheme = (mode) => {
    const href = mode === 'dark' ? '../../css/theme-dark.css' : '../../css/theme-light.css';
    if (themeLink.getAttribute('href') !== href) themeLink.setAttribute('href', href);
    if (toggleBtn) toggleBtn.querySelector('.theme-icon').textContent = mode === 'dark' ? '☀️' : '🌙';
    localStorage.setItem('sis-theme', mode);
  };
  applyTheme(initial);
  if (toggleBtn) toggleBtn.addEventListener('click', () => {
    const current = themeLink.getAttribute('href').includes('dark') ? 'dark' : 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });
})();

function showFormErrors(form, errors) {
  alert(errors.join('\n'));
}

// Login validation
(function loginValidation(){
  const form = document.getElementById('login-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = form.email.value.trim();
    const password = form.password.value;
    const errors = [];
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Enter a valid email.');
    if (!password || password.length < 8) errors.push('Password must be at least 8 characters.');
    if (errors.length) return showFormErrors(form, errors);
    // Simulate success UI
    form.querySelector('button[type="submit"]').disabled = true;
    form.querySelector('button[type="submit"]').textContent = 'Signing in…';
    setTimeout(() => { window.location.href = '../../index.html'; }, 800);
  });
})();

// Register validation
(function registerValidation(){
  const form = document.getElementById('register-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const fullName = form.fullName.value.trim();
    const email = form.email.value.trim();
    const role = form.role.value;
    const password = form.password.value;
    const confirm = form.confirm.value;
    const terms = document.getElementById('terms').checked;
    const errors = [];
    if (!fullName) errors.push('Full name is required.');
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Enter a valid email.');
    if (!role) errors.push('Please select a role.');
    if (!password || password.length < 8) errors.push('Password must be at least 8 characters.');
    if (password !== confirm) errors.push('Passwords do not match.');
    if (!terms) errors.push('You must accept the terms.');
    if (errors.length) return showFormErrors(form, errors);
    form.querySelector('button[type="submit"]').disabled = true;
    form.querySelector('button[type="submit"]').textContent = 'Creating…';
    setTimeout(() => { window.location.href = 'login.html'; }, 800);
  });
})();

// Forgot validation
(function forgotValidation(){
  const form = document.getElementById('forgot-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = form.email.value.trim();
    const errors = [];
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Enter a valid email.');
    if (errors.length) return showFormErrors(form, errors);
    form.querySelector('button[type="submit"]').disabled = true;
    form.querySelector('button[type="submit"]').textContent = 'Sending…';
    setTimeout(() => { alert('Reset link sent (demo).'); window.location.href = 'login.html'; }, 800);
  });
})();

// Reset validation
(function resetValidation(){
  const form = document.getElementById('reset-form');
  if (!form) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const password = form.password.value;
    const confirm = form.confirm.value;
    const errors = [];
    if (!password || password.length < 8) errors.push('Password must be at least 8 characters.');
    if (password !== confirm) errors.push('Passwords do not match.');
    if (errors.length) return showFormErrors(form, errors);
    form.querySelector('button[type="submit"]').disabled = true;
    form.querySelector('button[type="submit"]').textContent = 'Updating…';
    setTimeout(() => { alert('Password updated (demo).'); window.location.href = 'login.html'; }, 800);
  });
})();


