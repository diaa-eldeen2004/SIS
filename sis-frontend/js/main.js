// Theme handling
(function initTheme() {
  const themeLink = document.getElementById('theme-stylesheet');
  const toggleBtn = document.getElementById('theme-toggle');
  const selectEl = document.getElementById('theme-select');
  const saved = localStorage.getItem('sis-theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const initial = saved || (prefersDark ? 'dark' : 'light');

  const applyTheme = (mode) => {
    const href = mode === 'dark' ? './css/theme-dark.css' : './css/theme-light.css';
    if (themeLink.getAttribute('href') !== href) themeLink.setAttribute('href', href);
    if (toggleBtn) toggleBtn.querySelector('.theme-icon').textContent = mode === 'dark' ? '☀️' : '🌙';
    if (selectEl) selectEl.value = mode;
    localStorage.setItem('sis-theme', mode);
  };

  applyTheme(initial);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const current = themeLink.getAttribute('href').includes('dark') ? 'dark' : 'light';
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }
  if (selectEl) {
    selectEl.addEventListener('change', (e) => applyTheme(e.target.value));
  }
})();

// Tabs handling
(function initTabs() {
  const tabButtons = Array.from(document.querySelectorAll('.tab-button'));
  const panels = Array.from(document.querySelectorAll('.tab-panel'));

  function activate(id) {
    tabButtons.forEach(btn => {
      const active = btn.getAttribute('aria-controls') === id;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', String(active));
    });
    panels.forEach(panel => {
      const active = panel.id === id;
      panel.toggleAttribute('hidden', !active);
      panel.classList.toggle('is-active', active);
    });
  }

  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => activate(btn.getAttribute('aria-controls')));
    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        activate(btn.getAttribute('aria-controls'));
      }
    });
  });
})();

// Navbar responsive toggle
(function initNavbarToggle() {
  const toggle = document.querySelector('.navbar-toggle');
  const menu = document.getElementById('primary-menu');
  if (!toggle || !menu) return;
  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
})();

// Footer dynamic year
document.getElementById('year').textContent = new Date().getFullYear();


