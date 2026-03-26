<!-- header.php -->
<!-- Font Awesome (for icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
  /* Global theme variables */
  :root {
    --primary: #1565d8;
    --accent: #00c2ff;
    --bg: #f6f8fb;
    --card: #ffffff;
    --muted: #6b7280;
    --success: #16a34a;
    --danger: #ef4444;
    --glass: rgba(255,255,255,0.6);
  }
  body.dark {
    --bg: #0f1724;
    --card: #0b1220;
    --muted: #9aa6bf;
    --glass: rgba(255,255,255,0.03);
  }

  /* Header-specific styles */
  header {
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 18px;
    background: linear-gradient(90deg, var(--primary), #0b5bc6);
    color: white;
    box-shadow: 0 4px 18px rgba(2,6,23,0.12);
    position: sticky;
    top: 0;
    z-index: 50;
  }

  .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    cursor: pointer;
  }
  .logo i {
    font-size: 20px;
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .icon-btn {
    background: transparent;
    border: 0;
    color: white;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
  }
  .icon-btn:hover {
    background: rgba(255,255,255,0.06);
  }

  .searchbar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.12);
    padding: 8px 12px;
    border-radius: 12px;
    min-width: 320px;
    max-width: 600px;
    width: 40%;
  }
  .searchbar input {
    flex: 1;
    background: transparent;
    border: 0;
    outline: none;
    color: white;
  }
  .searchbar .placeholder {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
  }

  .dropdown {
    position: relative;
  }
  .dropdown-menu {
    position: absolute;
    right: 0;
    top: 48px;
    background: var(--card);
    min-width: 220px;
    border-radius: 10px;
    padding: 8px;
    box-shadow: 0 8px 30px rgba(4,12,32,0.12);
    display: none;
    z-index: 50;
  }
  .dropdown-menu.show {
    display: block;
  }
  .dropdown-menu a {
    display: block;
    padding: 8px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
  }

  .toasts {
    position: fixed;
    right: 20px;
    bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 100;
  }
  .toast {
    min-width: 220px;
    padding: 12px 14px;
    border-radius: 10px;
    color: white;
    background: rgba(0,0,0,0.8);
    box-shadow: 0 8px 20px rgba(6,10,30,0.25);
  }

  /* Responsive */
  @media (max-width: 1000px) {
    .searchbar {
      display: none;
    }
  }
</style>

<header>
  <div style="display:flex;align-items:center;gap:12px">
    <div id="btnToggleSidebar" class="icon-btn" title="Toggle sidebar">
      <i class="fas fa-bars"></i>
    </div>
    <div class="logo" onclick="scrollToTop()">
      <i class="fas fa-briefcase"></i>
      <div>
        <div style="font-size:14px">Employee Portal</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.9)">Dashboard & HR Tools</div>
      </div>
    </div>
  </div>

  <div class="searchbar" role="search" title="Search employees, seminars, etc">
    <i class="fas fa-search"></i>
    <input id="globalSearch" placeholder="Search employees, seminars, documents..." aria-label="Search" />
    <div style="display:flex;gap:8px">
      <button class="icon-btn" id="btnClearSearch" title="Clear search"><i class="fas fa-xmark"></i></button>
    </div>
  </div>

  <div class="header-right">
    <div class="icon-btn" id="btnWeatherToggle" title="Weather">
      <i class="fas fa-cloud-sun"></i>
    </div>

    <div class="icon-btn dropdown" style="position:relative">
      <button id="notifBell" class="icon-btn" aria-label="Notifications" title="Notifications">
        <i class="fas fa-bell"></i>
        <span id="notifBadge" style="background:#ef4444;color:white;padding:2px 6px;border-radius:12px;font-size:12px;margin-left:6px">3</span>
      </button>
      <div id="notifMenu" class="dropdown-menu" aria-hidden="true">
        <div style="font-weight:700;padding:6px 6px">Notifications</div>
        <hr style="border:none;height:1px;background:rgba(0,0,0,0.04)" />
        <a href="#">Leave request approved — John</a>
        <a href="#">New Seminar: Leadership 101 — Nov 12</a>
        <a href="#">13th Month Bonus posted — Payroll</a>
        <div style="padding:6px;text-align:center">
          <button class="small btn ghost" onclick="markAllNotificationsRead()">Mark all read</button>
        </div>
      </div>
    </div>

    <div class="dropdown" style="position:relative">
      <div style="display:flex;align-items:center;gap:10px;cursor:pointer" onclick="toggleDropdown('profileMenu')">
        <div style="text-align:right">
          <div id="greetingLine" style="font-weight:700">Welcome, John</div>
          <div class="subtle" id="greetingSub">Good morning</div>
        </div>
        <img src="https://via.placeholder.com/40" alt="avatar" style="width:40px;height:40px;border-radius:50%;border:2px solid rgba(255,255,255,0.12)" />
      </div>
      <div id="profileMenu" class="dropdown-menu" aria-hidden="true">
        <a href="#"><i class="fas fa-user"></i> Profile</a>
        <a href="#"><i class="fas fa-cog"></i> Settings</a>
        <a href="#"><i class="fas fa-lock"></i> Change password</a>
        <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <button id="btnTheme" class="icon-btn" title="Toggle theme" aria-pressed="false">
      <i id="themeIcon" class="fas fa-moon"></i>
    </button>
  </div>
</header>

<!-- Toast container -->
<div class="toasts" id="toasts"></div>

<script>
  // Utilities
  function qs(sel) { return document.querySelector(sel); }
  function qsa(sel) { return document.querySelectorAll(sel); }

  // Theme toggle
  const themeBtn = qs('#btnTheme');
  const themeIcon = qs('#themeIcon');
  (function initTheme() {
    const stored = localStorage.getItem('ep-theme');
    if (stored === 'dark') document.body.classList.add('dark');
    updateThemeIcon();
  })();
  function updateThemeIcon() {
    if (document.body.classList.contains('dark')) {
      themeIcon.className = 'fas fa-sun';
      themeBtn.setAttribute('aria-pressed', 'true');
    } else {
      themeIcon.className = 'fas fa-moon';
      themeBtn.setAttribute('aria-pressed', 'false');
    }
  }
  themeBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    localStorage.setItem('ep-theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    updateThemeIcon();
  });

  // Dropdowns
  window.toggleDropdown = function(id) {
    qs('#' + id).classList.toggle('show');
  };
  document.addEventListener('click', (e) => {
    const el = e.target;
    if (!el.closest('.dropdown')) {
      qsa('.dropdown-menu').forEach(n => n.classList.remove('show'));
    }
  });
  qs('#notifBell').addEventListener('click', () => {
    qs('#notifMenu').classList.toggle('show');
    qs('#notifBadge').style.display = 'none';
  });
  function markAllNotificationsRead() { showToast('All notifications marked read'); qs('#notifBadge').style.display = 'none'; }

  // Search
  qs('#btnClearSearch').addEventListener('click', () => { qs('#globalSearch').value = ''; showToast('Search cleared'); });
  qs('#globalSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { performSearch(e.target.value); }
  });
  function performSearch(q) {
    if (!q) return showToast('Type to search');
    showToast('Search performed (integrate with main content)');
  }

  // Toasts
  function showToast(msg, duration = 3500) {
    const cont = qs('#toasts');
    const t = document.createElement('div'); t.className = 'toast'; t.innerText = msg;
    cont.appendChild(t);
    setTimeout(() => { t.style.opacity = 0; t.style.transform = 'translateY(6px)'; setTimeout(() => t.remove(), 400); }, duration);
  }

  // Live clock and greeting
  function updateClock() {
    const now = new Date();
    const h = now.getHours();
    const sub = h < 12 ? 'Good morning' : (h < 18 ? 'Good afternoon' : 'Good evening');
    qs('#greetingSub').innerText = sub;
  }
  setInterval(updateClock, 1000);
  updateClock();

  // Helpers
  function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }

  // Expose for cross-component use
  window.showToast = showToast;
</script>