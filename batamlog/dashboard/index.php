<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Batam Log — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}

.card{width:100%;max-width:420px;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.10);overflow:hidden}

.card-top{background:#1e3a5f;padding:32px 28px 28px;text-align:center}
.logo-icon{width:52px;height:52px;background:rgba(255,255,255,0.15);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:1px solid rgba(255,255,255,0.2)}
.logo-icon svg{width:28px;height:28px;fill:white}
.card-top h1{font-size:22px;font-weight:700;color:#fff;margin-bottom:4px}
.card-top p{font-size:13px;color:rgba(255,255,255,0.6);line-height:1.5}

.card-body{padding:28px}

.form-group{margin-bottom:16px}
label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px}
input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:13px 14px;font-size:15px;font-family:'Inter',sans-serif;color:#1a1a2e;outline:none;transition:border-color .2s,box-shadow .2s;background:#fafafa;-webkit-appearance:none}
input:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);background:#fff}
input::placeholder{color:#9ca3af}

.btn{width:100%;background:#1e3a5f;color:#fff;border:none;border-radius:10px;padding:14px;font-size:15px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;margin-top:4px;transition:background .2s;-webkit-appearance:none}
.btn:hover{background:#164272}
.btn:active{background:#0f2d50}
.btn:disabled{background:#9ca3af;cursor:not-allowed}

.err{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 13px;font-size:13px;color:#dc2626;margin-bottom:14px;display:none;line-height:1.5}
.err.show{display:block}

.status-bar{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px;padding-top:18px;border-top:1px solid #f3f4f6}
.sdot{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:blink 2s ease infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.status-bar span{font-size:12px;color:#6b7280}

.info-list{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px}
.info-item{display:flex;align-items:flex-start;gap:7px;background:#f8fafc;border-radius:8px;padding:10px}
.info-dot{width:6px;height:6px;border-radius:50%;background:#1e3a5f;flex-shrink:0;margin-top:4px}
.info-item span{font-size:11px;color:#6b7280;line-height:1.4}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24"><path d="M3 18L7 6l4 8 3-5 4 9H3z"/></svg>
    </div>
    <h1>Batam Log</h1>
    <p>Smart Berth Monitoring System<br>Pelabuhan Batam — IoT Based</p>
  </div>

  <div class="card-body">
    <div class="info-list">
      <div class="info-item"><div class="info-dot"></div><span>Monitoring dermaga real-time</span></div>
      <div class="info-item"><div class="info-dot"></div><span>Deteksi pasang surut</span></div>
      <div class="info-item"><div class="info-dot"></div><span>Log kapal otomatis</span></div>
      <div class="info-item"><div class="info-dot"></div><span>Sensor HC-SR04 & Water Level</span></div>
    </div>

    <div class="err" id="errMsg"></div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" id="username" placeholder="Masukkan username" autocomplete="off" autocapitalize="none">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" id="password" placeholder="Masukkan password" autocomplete="current-password">
    </div>
    <button class="btn" id="loginBtn" onclick="doLogin()">Masuk</button>

    <div class="status-bar">
      <div class="sdot"></div>
      <span>Server Localhost (XAMPP) Active</span>
    </div>
  </div>
</div>

<script>
if (localStorage.getItem('bl_token') && localStorage.getItem('bl_user')) {
  window.location.href = 'dashboard.php';
}

document.getElementById('password').addEventListener('keydown', e => {
  if (e.key === 'Enter') doLogin();
});

async function doLogin() {
  const btn      = document.getElementById('loginBtn');
  const err      = document.getElementById('errMsg');
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;

  if (!username || !password) { showErr('Username dan password wajib diisi.'); return; }

  btn.textContent = 'Memverifikasi...';
  btn.disabled    = true;
  err.classList.remove('show');

  try {
    const res  = await fetch('../api/auth.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    const data = await res.json();
    if (data.success) {
      localStorage.setItem('bl_token',   data.token);
      localStorage.setItem('bl_session', data.session_id);
      localStorage.setItem('bl_user',    JSON.stringify(data.user));
      btn.textContent = 'Berhasil...';
      setTimeout(() => { window.location.href = 'dashboard.php'; }, 400);
    } else {
      showErr(data.message || 'Login gagal.');
      btn.textContent = 'Masuk';
      btn.disabled    = false;
    }
  } catch (e) {
    showErr('Tidak dapat terhubung ke server.');
    btn.textContent = 'Masuk';
    btn.disabled    = false;
  }
}

function showErr(msg) {
  const err = document.getElementById('errMsg');
  err.textContent = msg;
  err.classList.add('show');
}
</script>
</body>
</html>
