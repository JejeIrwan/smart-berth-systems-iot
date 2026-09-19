<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Batam Log — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f2f5;color:#1a1a2e;min-height:100vh}

/* ── SIDEBAR (Desktop) ── */
.sidebar{width:230px;background:#fff;border-right:1px solid #e5e7eb;display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
.sb-logo{padding:18px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px}
.sb-logo-icon{width:34px;height:34px;background:#1e3a5f;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sb-logo-icon svg{width:18px;height:18px;fill:white}
.sb-logo-name{font-size:14px;font-weight:700;color:#1a1a2e}
.sb-logo-sub{font-size:10px;color:#9ca3af;margin-top:1px}
.sb-nav{flex:1;padding:12px 10px}
.nav-sec{font-size:10px;font-weight:600;color:#9ca3af;letter-spacing:1px;text-transform:uppercase;padding:0 8px;margin-bottom:5px;margin-top:12px}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#6b7280;text-decoration:none;transition:all .15s;margin-bottom:2px}
.nav-item:hover{background:#f3f4f6;color:#1a1a2e}
.nav-item.active{background:#eff6ff;color:#1e3a5f;font-weight:600}
.nav-item svg{width:15px;height:15px;flex-shrink:0}
.nav-item.danger{color:#ef4444}
.nav-item.danger:hover{background:#fef2f2}
.sb-footer{padding:14px;border-top:1px solid #e5e7eb}
.sb-user{display:flex;align-items:center;gap:9px}
.sb-avatar{width:30px;height:30px;border-radius:8px;background:#1e3a5f;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0}
.sb-uname{font-size:13px;font-weight:600;color:#1a1a2e}
.sb-urole{font-size:10px;color:#9ca3af}
.sb-logout{display:block;margin-top:10px;text-align:center;font-size:11px;color:#9ca3af;text-decoration:none;padding:6px;border-radius:6px;border:1px solid #e5e7eb;cursor:pointer;transition:all .15s;background:none;width:100%;font-family:'Inter',sans-serif}
.sb-logout:hover{background:#f9fafb}

/* ── MAIN ── */
.main{margin-left:230px;min-height:100vh;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.page-title h1{font-size:19px;font-weight:700;color:#1a1a2e;letter-spacing:-0.3px}
.page-title p{font-size:12px;color:#9ca3af;margin-top:2px}
.topbar-right{display:flex;align-items:center;gap:16px}
.clock{font-size:17px;font-weight:700;color:#1e3a5f;font-variant-numeric:tabular-nums;letter-spacing:1px}
.clock-date{font-size:11px;color:#9ca3af;margin-top:1px;text-align:right}
.live-badge{display:flex;align-items:center;gap:5px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;padding:4px 10px;font-size:11px;font-weight:500;color:#16a34a;white-space:nowrap}
.live-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:blink 2s ease infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

.content{padding:16px 24px 24px;flex:1}

/* ── CARDS ── */
.top-row{display:grid;grid-template-columns:1fr 1.8fr;gap:14px;margin-bottom:14px}
.info-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px}
.lbl{font-size:10px;font-weight:600;color:#9ca3af;letter-spacing:1px;text-transform:uppercase;margin-bottom:7px}
.tide-val{font-size:20px;font-weight:700;color:#1a1a2e}
.tide-readout{display:flex;gap:20px;margin-top:9px}
.tide-metric{display:flex;flex-direction:column}
.tm-lbl{font-size:9px;font-weight:600;color:#9ca3af;letter-spacing:.5px;text-transform:uppercase}
.tm-val{font-size:15px;font-weight:700;color:#1a1a2e}
.tide-scale{margin-top:11px}
.tide-bar-bg{position:relative;height:8px;background:#e5e7eb;border-radius:4px;margin-top:0}
.tide-bar-fill{position:absolute;left:0;top:0;height:100%;border-radius:4px;transition:width 1s ease,background .3s}
.tide-zone-tick{position:absolute;top:-2px;width:1px;height:12px;background:#cbd5e1;z-index:2}
.tide-marker{position:absolute;top:-3px;width:3px;height:14px;background:#1a1a2e;border-radius:2px;transition:left 1s ease;z-index:3}
.tide-scale-lbls{position:relative;height:12px;margin-top:7px}
.tide-scale-lbls span{position:absolute;transform:translateX(-50%);font-size:9px;color:#9ca3af}
.tide-hint{font-size:11px;color:#9ca3af;margin-top:8px}
.conn-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;display:flex;align-items:center;gap:10px}
.cdot{width:9px;height:9px;border-radius:50%;background:#22c55e;flex-shrink:0;animation:blink 2s ease infinite}
.conn-text{font-size:14px;font-weight:600;color:#15803d}
.conn-sub{font-size:11px;color:#9ca3af;margin-top:2px}

.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px}
.stat-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px}
.stat-val{font-size:26px;font-weight:700;color:#1a1a2e;letter-spacing:-1px;margin-bottom:3px}
.stat-sub{font-size:11px;color:#9ca3af}
.sv-blue{color:#1e3a5f}.sv-green{color:#15803d}.sv-amber{color:#d97706}

.berth-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.berth-card{background:#fff;border:1px solid #e5e7eb;border-top:3px solid #e5e7eb;border-radius:12px;padding:18px;transition:border-color .3s}
.berth-card.occupied{border-top-color:#ef4444;border-color:#fca5a5}
.berth-card.available{border-top-color:#22c55e;border-color:#86efac}
.berth-card.warning{border-top-color:#f59e0b;border-color:#fcd34d}
.berth-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.berth-name{font-size:13px;font-weight:600;color:#374151}
.badge{font-size:10px;font-weight:600;padding:3px 8px;border-radius:5px;letter-spacing:.4px}
.bb-avail{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.bb-occ{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.bb-warn{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.bb-idle{background:#f3f4f6;color:#9ca3af;border:1px solid #e5e7eb}
.berth-big{font-size:26px;font-weight:700;margin-bottom:14px;letter-spacing:-0.5px}
.bc-green{color:#15803d}.bc-red{color:#dc2626}.bc-amber{color:#d97706}.bc-gray{color:#9ca3af}
.berth-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.bm-lbl{font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
.bm-val{font-size:13px;font-weight:600;color:#374151}

.sec-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px}
.sec-title{font-size:15px;font-weight:700;color:#1a1a2e}
.btn-clear{display:flex;align-items:center;gap:5px;background:#fff;border:1px solid #fca5a5;color:#dc2626;border-radius:7px;padding:6px 12px;font-size:12px;font-weight:500;cursor:pointer;font-family:'Inter',sans-serif;transition:all .15s}
.btn-clear:hover{background:#fef2f2}
.btn-clear svg{width:12px;height:12px}
.tbl-wrap{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.tbl-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse;min-width:500px}
thead th{padding:10px 15px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;letter-spacing:.5px;text-transform:uppercase;background:#f9fafb;border-bottom:1px solid #e5e7eb;white-space:nowrap}
tbody tr{border-bottom:1px solid #f3f4f6;transition:background .1s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#f9fafb}
tbody td{padding:11px 15px;font-size:13px;color:#374151;white-space:nowrap}
.empty td{text-align:center;padding:32px;color:#9ca3af;white-space:normal}
.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600;letter-spacing:.3px}
.t-arr{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.t-dep{background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb}
.t-warn{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.t-norm{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}

/* ── BOTTOM NAV (Mobile only) ── */
.bottom-nav{display:none}

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
  .sidebar { display: none; }

  .main { margin-left: 0; padding-bottom: 70px; }

  .topbar { padding: 12px 16px; }
  .page-title h1 { font-size: 16px; }
  .page-title p  { display: none; }
  .clock { font-size: 15px; }
  .clock-date { font-size: 10px; }

  .content { padding: 12px 14px 16px; }

  .top-row { grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px; }
  .conn-card { padding: 14px 16px; }

  .stats-row { grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
  .stat-card { padding: 14px 16px; }
  .stat-val  { font-size: 22px; }

  .berth-row { grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px; }
  .berth-card { padding: 16px; }
  .berth-big  { font-size: 22px; }

  .bottom-nav {
    display: flex;
    position: fixed; bottom: 0; left: 0; right: 0;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    z-index: 200;
    padding: 8px 0 12px;
  }

  .bn-item {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; gap: 4px;
    font-size: 10px; font-weight: 500; color: #9ca3af;
    text-decoration: none; cursor: pointer;
    background: none; border: none; font-family: 'Inter', sans-serif;
    padding: 4px 0;
  }

  .bn-item.active { color: #1e3a5f; }
  .bn-item svg    { width: 20px; height: 20px; }
  .bn-item.danger { color: #ef4444; }
}

@media (max-width: 420px) {
  .stats-row { grid-template-columns: 1fr 1fr; }
  .berth-meta { grid-template-columns: 1fr 1fr; }
  .topbar-right { gap: 8px; }
  .live-badge span { display: none; }
}
</style>
</head>
<body>

<!-- SIDEBAR (Desktop) -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon">
      <svg viewBox="0 0 24 24"><path d="M3 18L7 6l4 8 3-5 4 9H3z"/></svg>
    </div>
    <div>
      <div class="sb-logo-name">Batam Log</div>
      <div class="sb-logo-sub">Smart Berth System</div>
    </div>
  </div>
  <nav class="sb-nav">
    <div class="nav-sec">Monitoring</div>
    <a class="nav-item active" href="dashboard.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a class="nav-item" href="antrean.php">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
  Antrean
</a>
    <a class="nav-item" href="#traffic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Activity Log
    </a>
    <div class="nav-sec">System</div>
    <a class="nav-item danger" href="#" onclick="clearLogs();return false;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
      Reset Database
    </a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar" id="sbAvatar">A</div>
      <div>
        <div class="sb-uname" id="sbName">Admin</div>
        <div class="sb-urole" id="sbRole">OPERATOR</div>
      </div>
    </div>
    <button class="sb-logout" onclick="doLogout()">Keluar</button>
  </div>
</aside>

<!-- BOTTOM NAV (Mobile) -->
<nav class="bottom-nav">
  <a class="bn-item active" href="dashboard.php">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
    Dashboard
  </a>

  <a class="bn-item" href="#traffic" onclick="document.getElementById('traffic').scrollIntoView({behavior:'smooth'})">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    Activity
  </a>
  <button class="bn-item" id="mobileUser" onclick="showUserInfo()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    <span id="bnUserName">Admin</span>
  </button>
  <button class="bn-item danger" onclick="doLogout()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Keluar
  </button>
</nav>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div class="page-title">
      <h1>Port Overview</h1>
      <p>Real-time terminal monitoring</p>
    </div>
    <div class="topbar-right">
      <div class="live-badge"><div class="live-dot"></div><span>Live</span></div>
      <div>
        <div class="clock" id="clock">--:--:--</div>
        <div class="clock-date" id="clockDate"></div>
      </div>
    </div>
  </div>

  <div class="content">

    <!-- Top Row -->
    <div class="top-row">
      <div class="info-card">
        <div class="lbl">Current Tide</div>
        <div class="tide-val" id="tideVal">--</div>
        <div class="tide-readout">
          <div class="tide-metric"><span class="tm-lbl">ADC</span><span class="tm-val" id="tideAdc">--</span></div>
          <div class="tide-metric"><span class="tm-lbl">Kedalaman</span><span class="tm-val" id="tideDepth">-- cm</span></div>
        </div>
        <div class="tide-scale">
          <div class="tide-bar-bg">
            <div class="tide-zone-tick" style="left:37.5%"></div>
            <div class="tide-zone-tick" style="left:75%"></div>
            <div class="tide-bar-fill" id="tideFill" style="width:0%;background:#22c55e"></div>
            <div class="tide-marker" id="tideMarker" style="left:0%"></div>
          </div>
          <div class="tide-scale-lbls">
            <span style="left:0%;transform:translateX(0)">0</span>
            <span style="left:37.5%">1.5</span>
            <span style="left:75%">3.0</span>
            <span style="left:100%;transform:translateX(-100%)">4 cm</span>
          </div>
        </div>
        <div class="tide-hint" id="tideHint">Menunggu data sensor...</div>
      </div>
      <div class="conn-card">
        <div class="cdot"></div>
        <div>
          <div class="conn-text">Server Active</div>
          <div class="conn-sub">Auto-refresh 5 detik</div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="lbl">Kapal Hari Ini</div>
        <div class="stat-val sv-blue" id="statToday">--</div>
        <div class="stat-sub">kedatangan</div>
      </div>
      <div class="stat-card">
        <div class="lbl">Bulan Ini</div>
        <div class="stat-val" id="statMonth">--</div>
        <div class="stat-sub">total kapal</div>
      </div>
      <div class="stat-card">
        <div class="lbl">Rata-rata Sandar</div>
        <div class="stat-val sv-green" id="statAvg">--</div>
        <div class="stat-sub">durasi</div>
      </div>
      <div class="stat-card">
        <div class="lbl">Surut Warning</div>
        <div class="stat-val sv-amber" id="statSurut">--</div>
        <div class="stat-sub">hari ini</div>
      </div>
    </div>

    <!-- Berth Cards -->
    <div class="berth-row">
      <div class="berth-card" id="b1card">
        <div class="berth-head">
          <span class="berth-name">Terminal Berth 01</span>
          <span class="badge bb-idle" id="b1badge">LOADING</span>
        </div>
        <div class="berth-big bc-gray" id="b1status">Checking...</div>
        <div class="berth-meta">
          <div><div class="bm-lbl">Jarak Sensor</div><div class="bm-val" id="b1dist">-- cm</div></div>
          <div><div class="bm-lbl">Waktu Tiba</div><div class="bm-val" id="b1arr">--</div></div>
          <div><div class="bm-lbl">Durasi Sandar</div><div class="bm-val" id="b1dur">--</div></div>
          <div><div class="bm-lbl">Kondisi Air</div><div class="bm-val" id="b1tide">--</div></div>
        </div>
      </div>
      <div class="berth-card" id="b2card">
        <div class="berth-head">
          <span class="berth-name">Terminal Berth 02</span>
          <span class="badge bb-idle" id="b2badge">LOADING</span>
        </div>
        <div class="berth-big bc-gray" id="b2status">Checking...</div>
        <div class="berth-meta">
          <div><div class="bm-lbl">Jarak Sensor</div><div class="bm-val" id="b2dist">-- cm</div></div>
          <div><div class="bm-lbl">Waktu Tiba</div><div class="bm-val" id="b2arr">--</div></div>
          <div><div class="bm-lbl">Durasi Sandar</div><div class="bm-val" id="b2dur">--</div></div>
          <div><div class="bm-lbl">Kondisi Air</div><div class="bm-val" id="b2tide">--</div></div>
        </div>
      </div>
    </div>

    <!-- Traffic Table -->
    <div id="traffic">
      <div class="sec-head">
  <span class="sec-title">Traffic History</span>
  <div style="display:flex;gap:8px;align-items:center;">
    <a href="../api/export_traffic.php" target="_blank"
       style="display:flex;align-items:center;gap:5px;background:#fff;border:1px solid #86efac;color:#15803d;border-radius:7px;padding:6px 12px;font-size:12px;font-weight:500;text-decoration:none;">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/>
        <line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Export Excel
    </a>
    <button class="btn-clear" onclick="clearLogs()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M9 6V4h6v2"/></svg>
      Clear All Logs
    </button>
  </div>
</div>
      <div class="tbl-wrap">
        <div class="tbl-scroll">
          <table>
            <thead>
              <tr><th>Berth</th><th>Arrival</th><th>Departure</th><th>Stay Duration</th><th>Condition</th><th>Status</th></tr>
            </thead>
            <tbody id="trafBody">
              <tr class="empty"><td colspan="6">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
// Auth
const blUser = JSON.parse(localStorage.getItem('bl_user') || 'null');
if (!blUser || !localStorage.getItem('bl_token')) {
  window.location.href = 'index.php';
}

if (blUser) {
  const initial = blUser.full_name.charAt(0).toUpperCase();
  document.getElementById('sbAvatar').textContent  = initial;
  document.getElementById('sbName').textContent    = blUser.full_name;
  document.getElementById('sbRole').textContent    = blUser.role.toUpperCase();
  document.getElementById('bnUserName').textContent = blUser.full_name.split(' ')[0];
}

function doLogout() {
  if (!confirm('Yakin ingin keluar?')) return;
  localStorage.removeItem('bl_token');
  localStorage.removeItem('bl_user');
  localStorage.removeItem('bl_session');
  fetch('../api/auth.php?action=logout').finally(() => {
    window.location.href = 'index.php';
  });
}

function showUserInfo() {
  alert(`${blUser.full_name}\nRole: ${blUser.role.toUpperCase()}`);
}

// Clock
function tick() {
  const n = new Date(), p = v => String(v).padStart(2,'0');
  document.getElementById('clock').textContent = `${p(n.getHours())}.${p(n.getMinutes())}.${p(n.getSeconds())}`;
  const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const mons = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
  document.getElementById('clockDate').textContent = `${days[n.getDay()]}, ${n.getDate()} ${mons[n.getMonth()]} ${n.getFullYear()}`;
}
setInterval(tick, 1000); tick();

// Fetch Overview
async function fetchOverview() {
  try {
    const d = await (await fetch('../api/get_dashboard.php?action=overview')).json();
    if (!d.success) return;

   if (d.tide) {
  const status = d.tide.tide_status || '--';
  const adc    = parseInt(d.tide.tide_adc) || 0;
  const tv = document.getElementById('tideVal');
  tv.textContent = status;

  // Warna sesuai status
  if (status === 'PASANG') {
    tv.style.color = '#15803d';      // hijau
  } else if (status === 'NORMAL') {
    tv.style.color = '#1d4ed8';      // biru
  } else {
    tv.style.color = '#d97706';      // kuning/amber = SURUT
  }

  // Readout ADC & kedalaman
  const depth  = d.tide.tide_depth;
  const depthM = d.tide.tide_depth_m;
  const kapal  = d.tide.tide_kapal || '--';
  const depthNum = (depth !== null && depth !== undefined && depth !== '--') ? Number(depth) : 0;
  document.getElementById('tideAdc').textContent = adc || '--';
  document.getElementById('tideDepth').textContent =
      (depth !== null && depth !== undefined && depth !== '--')
        ? `${depthNum.toFixed(2)} cm` : '-- cm';

  // Skala: fill & marker berdasarkan kedalaman (0-4 cm)
  const pct  = Math.min(100, Math.max(0, Math.round((depthNum / 4) * 100)));
  const fill = document.getElementById('tideFill');
  fill.style.width = pct + '%';
  document.getElementById('tideMarker').style.left = pct + '%';

  if (status === 'PASANG')       fill.style.background = '#22c55e'; // hijau
  else if (status === 'NORMAL')  fill.style.background = '#3b82f6'; // biru
  else                           fill.style.background = '#f59e0b'; // amber

  // Hint: kedalaman meter + rekomendasi kapal
  const hint = document.getElementById('tideHint');
  const dm   = (depthM !== null && depthM !== undefined && depthM !== '--')
                 ? `${Number(depthM).toFixed(2)} m` : '-- m';
  const icon = status === 'PASANG' ? '✅' : (status === 'NORMAL' ? '⚠️' : '❌');
  hint.innerHTML = `Kedalaman nyata: <b>${dm}</b> &nbsp;|&nbsp; ${icon} ${kapal}`;
}

    d.berths.forEach(b => {
      const n    = b.berth_id;
      const occ  = b.status === 'OCCUPIED';
      const safe = b.tide_safe == 1;
      const card  = document.getElementById(`b${n}card`);
      const badge = document.getElementById(`b${n}badge`);
      const st    = document.getElementById(`b${n}status`);

      card.className  = 'berth-card';
      badge.className = 'badge';

      if (occ) {
        card.classList.add('occupied');
        badge.classList.add('bb-occ'); badge.textContent = 'OCCUPIED';
        st.className = 'berth-big bc-red'; st.textContent = 'TERISI';
      } else if (!safe && b.tide_status) {
        card.classList.add('warning');
        badge.classList.add('bb-warn'); badge.textContent = 'SURUT WARNING';
        st.className = 'berth-big bc-amber'; st.textContent = 'JANGAN SANDAR';
      } else if (b.tide_status) {
        card.classList.add('available');
        badge.classList.add('bb-avail'); badge.textContent = 'AVAILABLE';
        st.className = 'berth-big bc-green'; st.textContent = 'TERSEDIA';
      } else {
        badge.classList.add('bb-idle'); badge.textContent = 'STANDBY';
        st.className = 'berth-big bc-gray'; st.textContent = 'Menunggu...';
      }

      const dist = parseFloat(b.distance_cm);
      document.getElementById(`b${n}dist`).textContent = (!b.distance_cm || dist >= 999) ? '-- cm' : dist.toFixed(1) + ' cm';
      document.getElementById(`b${n}arr`).textContent  = b.arrival_time  ? b.arrival_time.split(' ')[1]  : '--';
      document.getElementById(`b${n}dur`).textContent  = b.dock_duration || '--';
      document.getElementById(`b${n}tide`).textContent = b.tide_status   || '--';
    });
  } catch(e) {}
}

// Fetch Stats
async function fetchStats() {
  try {
    const d = await (await fetch('../api/get_dashboard.php?action=stats')).json();
    if (!d.success) return;
    document.getElementById('statToday').textContent = d.today_arrivals    ?? '--';
    document.getElementById('statMonth').textContent = d.month_arrivals    ?? '--';
    document.getElementById('statAvg').textContent   = d.avg_dock_duration || '--';
    document.getElementById('statSurut').textContent = d.surut_warnings    ?? '--';
  } catch(e) {}
}

// Fetch Traffic
async function fetchTraffic() {
  try {
    const d  = await (await fetch('../api/get_dashboard.php?action=traffic&limit=30')).json();
    const tb = document.getElementById('trafBody');
    if (!d.success || !d.logs.length) {
      tb.innerHTML = '<tr class="empty"><td colspan="6">Belum ada data traffic</td></tr>'; return;
    }
    tb.innerHTML = d.logs.map(l => `<tr>
      <td><strong>${l.berth_code}</strong></td>
      <td>${l.arrival_time || '--'}</td>
      <td>${l.departure_time || '--'}</td>
      <td>${l.dock_duration || '--'}</td>
      <td>${l.condition === 'SURUT_WARNING' ? '<span class="tag t-warn">SURUT WARNING</span>' : '<span class="tag t-norm">NORMAL</span>'}</td>
      <td>${l.event_type === 'ARRIVAL' ? '<span class="tag t-arr">ARRIVAL</span>' : l.event_type === 'COMPLETE' ? '<span class="tag t-dep">COMPLETE</span>' : '<span class="tag t-dep">DEPARTURE</span>'}</td>
    </tr>`).join('');
  } catch(e) {}
}

// Clear Logs
async function clearLogs() {
  if (!confirm('Hapus semua log? Data tidak bisa dikembalikan.')) return;
  try {
    const d = await (await fetch('../api/get_dashboard.php?action=clear_logs', {method:'POST'})).json();
    if (d.success) { fetchTraffic(); fetchStats(); }
  } catch(e) {}
}

function refreshAll() { fetchOverview(); fetchStats(); fetchTraffic(); }
refreshAll();
setInterval(refreshAll, 5000);
</script>
</body>
</html>
