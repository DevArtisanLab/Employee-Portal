<!-- sidebar.php -->
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

  /* Sidebar-specific styles */
  aside.sidebar {
    width: 260px;
    padding: 18px;
    background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.8));
    transition: all .25s;
    border-right: 1px solid rgba(10,10,10,0.03);
  }
  body.dark aside.sidebar {
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  }
  aside.sidebar.collapsed {
    width: 72px;
    padding: 12px;
  }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 6px;
    color: var(--primary);
    text-decoration: none;
  }
  .menu-item i {
    width: 26px;
    text-align: center;
  }
  .menu-item:hover {
    background: rgba(0,0,0,0.05);
    color: white;
  }
  body.dark .menu-item {
    color: var(--muted);
  }
  body.dark .menu-item:hover {
    background: rgba(255,255,255,0.03);
  }
  aside.sidebar.collapsed .menu-label {
    display: none;
  }

  .btn {
    background: var(--primary);
    color: white;
    padding: 8px 12px;
    border-radius: 10px;
    border: 0;
    cursor: pointer;
  }
  .btn.ghost {
    background: transparent;
    color: var(--primary);
    box-shadow: none;
    border: 1px solid rgba(0,0,0,0.06);
  }
  .small {
    padding: 6px 10px;
    font-size: 14px;
    border-radius: 8px;
  }

  /* Modals */
  #feedbackModal, #recognitionModal {
    display: none;
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: rgba(2,6,23,0.5);
    align-items: center;
    justify-content: center;
    z-index: 120;
  }
  #feedbackModal > div, #recognitionModal > div {
    width: 480px;
    max-width: 94%;
    background: var(--card);
    padding: 20px;
    border-radius: 12px;
  }
  #recognitionModal > div {
    width: 420px;
  }

  /* Responsive */
  @media (max-width: 1000px) {
    aside.sidebar {
      display: none;
    }
  }
</style>

<aside class="sidebar" id="sidebar">
  <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
    <div style="width:44px;height:44px;border-radius:10px;background:linear-gradient(90deg,var(--accent),var(--primary));display:flex;align-items:center;justify-content:center;color:white;font-weight:700">JM</div>
    <div>
      <div style="font-weight:700">John Mercado</div>
      <div class="subtle" style="font-size:13px">HR Staff</div>
    </div>
  </div>

  <nav>
    <a class="menu-item" href="#"><i class="fas fa-tachometer-alt"></i><span class="menu-label">Dashboard</span></a>
    <a class="menu-item" href="#"><i class="fas fa-calendar-days"></i><span class="menu-label">Seminars</span></a>
    <a class="menu-item" href="#"><i class="fas fa-clipboard-list"></i><span class="menu-label">Exams</span></a>
    <a class="menu-item" href="#"><i class="fas fa-user"></i><span class="menu-label">Profile</span></a>
    <a class="menu-item" href="#"><i class="fas fa-envelope"></i><span class="menu-label">Messages</span></a>
    <a class="menu-item" href="#"><i class="fas fa-user-shield"></i><span class="menu-label">Admin Panel (placeholder)</span></a>
  </nav>

  <div style="margin-top:16px">
    <div style="display:flex;gap:8px;margin-bottom:8px">
      <button class="btn small" onclick="openFeedback()">Give Feedback</button>
      <button class="btn small ghost" onclick="openRecognition()">Recognize</button>
    </div>
  </div>
</aside>

<!-- Feedback modal -->
<div id="feedbackModal" style="display:none;position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(2,6,23,0.5);align-items:center;justify-content:center;z-index:120">
  <div style="width:480px;max-width:94%;background:var(--card);padding:20px;border-radius:12px">
    <h3>Send Feedback</h3>
    <textarea id="feedbackText" placeholder="Share your feedback..." style="width:100%;height:140px;padding:8px;border-radius:8px;border:1px solid rgba(0,0,0,0.06);resize:vertical"></textarea>