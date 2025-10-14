function initShellActiveNav() {
  const here = window.location.pathname.split('/').pop();
  document.querySelectorAll('[data-href]').forEach(btn => {
    const href = btn.getAttribute('data-href');
    const match = href && href.endsWith(here);
    btn.classList.toggle('is-active', !!match);
  });
}

function initShellTheme() {
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
}

function initShellMenuToggle() {
  const toggle = document.querySelector('.navbar-toggle');
  const menu = document.getElementById('primary-menu');
  if (!toggle || !menu) return;
  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
}

function initNavButtons() {
  document.querySelectorAll('[data-href]').forEach(btn => {
    btn.addEventListener('click', () => {
      const href = btn.getAttribute('data-href');
      if (href) window.location.href = href;
    });
  });
  const manageToggle = document.getElementById('manage-toggle');
  const manage = document.getElementById('manage-dropdown');
  if (manageToggle && manage) {
    manageToggle.addEventListener('click', () => {
      manage.classList.toggle('is-open');
    });
    document.addEventListener('click', (e) => {
      if (!manage.contains(e.target) && e.target !== manageToggle) manage.classList.remove('is-open');
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initShellTheme();
  initShellActiveNav();
  initShellMenuToggle();
  initNavButtons();
  const y = document.getElementById('year');
  if (y) y.textContent = new Date().getFullYear();
});


