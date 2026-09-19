<?php
// ============================================================
//  BATAM LOG — Halaman Manajemen Antrean (Waiting List)
//  Letakkan di folder yang sama dengan dashboard.php
//  Memanggil API: ../api/queue_get.php, queue_add.php,
//                 queue_call.php, queue_update.php
// ============================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Batam Log — Antrean</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f0f2f5;color:#1a1a2e;min-height:100vh}

/* ── SIDEBAR ── */
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

/* ── MAIN ── */
.main{margin-left:230px;padding:24px 28px}
.page-head{margin-bottom:20px}
.page-title{font-size:22px;font-weight:700;color:#1a1a2e}
.page-sub{font-size:13px;color:#6b7280;margin-top:2px}

.row{display:flex;gap:18px;flex-wrap:wrap;margin-bottom:18px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px 20px;box-shadow:0 1px 2px rgba(0,0,0,.03)}

/* status dermaga */
.status-box{flex:1;min-width:280px;display:flex;align-items:center;gap:16px}
.status-badge{padding:10px 20px;border-radius:10px;font-size:22px;font-weight:700}
.st-open{background:#f0fdf4;color:#16a34a}
.st-full{background:#fef2f2;color:#dc2626}
.status-detail{font-size:13px;color:#6b7280}
.status-detail b{color:#1a1a2e}

.count-box{min-width:150px;text-align:center}
.count-num{font-size:34px;font-weight:700;color:#1e3a5f}
.count-lbl{font-size:12px;color:#6b7280}

/* panel antrean */
.panel{flex:1;min-width:100%}
.panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.panel-title{font-size:16px;font-weight:700;color:#1a1a2e}
.btn{border:none;border-radius:8px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .15s}
.btn-primary{background:#1e3a5f;color:#fff}
.btn-primary:hover{background:#16304d}
.btn-call{background:#2563eb;color:#fff}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:7px}
.btn-ok{background:#16a34a;color:#fff}
.btn-warn{background:#d97706;color:#fff}
.btn-danger{background:#ef4444;color:#fff}
.btn-ghost{background:#f3f4f6;color:#374151}
.btn:hover{opacity:.9}

table{width:100%;border-collapse:collapse}
th{text-align:left;font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;padding:10px 12px;border-bottom:1px solid #e5e7eb}
td{padding:12px;font-size:13px;color:#1a1a2e;border-bottom:1px solid #f3f4f6;vertical-align:middle}
tr:last-child td{border-bottom:none}
.no-badge{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#eff6ff;color:#1e3a5f;border-radius:8px;font-weight:700;font-size:14px}
.badge{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
.bg-wait{background:#f3f4f6;color:#6b7280}
.bg-call{background:#dbeafe;color:#1d4ed8}
.bg-hold{background:#fef3c7;color:#b45309}
.bg-dock{background:#dcfce7;color:#15803d}
.empty{text-align:center;color:#9ca3af;padding:30px;font-size:13px}
.actions{display:flex;gap:6px;flex-wrap:wrap}

/* section title */
.sec-title{font-size:14px;font-weight:700;color:#1a1a2e;margin:6px 0 12px}
/* timer cards */
.timer-card{flex:1;min-width:300px}
.tc-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.tc-name{font-size:16px;font-weight:700;color:#1a1a2e}
.tc-badge{font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px}
.tc-off{background:#f3f4f6;color:#6b7280}
.tc-on{background:#dbeafe;color:#1d4ed8}
.tc-timerbox{border-radius:12px;padding:18px;text-align:center;margin-bottom:12px}
.tc-time{font-size:44px;font-weight:700;font-variant-numeric:tabular-nums;letter-spacing:1px}
.z-normal{background:#f0fdf4}.z-normal .tc-time{color:#16a34a}
.z-segera{background:#fffbeb}.z-segera .tc-time{color:#d97706}
.z-habis{background:#fef2f2}.z-habis .tc-time{color:#dc2626}
.z-off{background:#f9fafb}.z-off .tc-time{color:#cbd5e1;font-size:26px}
.tc-info{font-size:12px;color:#6b7280;margin-bottom:12px;min-height:16px}
.tc-warn{font-size:12.5px;font-weight:600;text-align:center;padding:8px;border-radius:8px;margin-bottom:12px}
.chip-row{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.chip{border:1px solid #e5e7eb;background:#fff;color:#1e3a5f;border-radius:20px;padding:7px 14px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit}
.chip:hover{background:#eff6ff;border-color:#2563eb}

/* modal */
.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:300}
.modal-overlay.show{display:flex}
.modal{background:#fff;width:400px;max-width:92vw;border-radius:18px;padding:26px;position:relative;box-shadow:0 20px 50px rgba(0,0,0,.25);animation:pop .2s ease}
@keyframes pop{from{transform:scale(.94);opacity:0}to{transform:scale(1);opacity:1}}
.modal-close{position:absolute;top:16px;right:18px;background:none;border:none;font-size:24px;color:#9ca3af;cursor:pointer;line-height:1}
.modal-close:hover{color:#1a1a2e}
.modal-head{display:flex;gap:14px;align-items:flex-start;margin-bottom:20px}
.modal-icon{width:50px;height:50px;background:#1e3a5f;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.modal-icon svg{width:24px;height:24px}
.modal-title{font-size:18px;font-weight:700;color:#1a1a2e}
.modal-sub{font-size:12.5px;color:#6b7280;margin-top:2px}
.modal-no{display:flex;justify-content:space-between;align-items:center;background:#eff6ff;border-radius:12px;padding:12px 16px;margin-bottom:20px}
.modal-no-lbl{font-size:10px;font-weight:600;color:#2563eb;letter-spacing:1px}
.modal-no-val{font-size:22px;font-weight:700;color:#1e3a5f;margin-top:2px}
.modal-no-note{font-size:12px;color:#6b7280}
.modal-flabel{display:block;font-size:13px;font-weight:600;color:#1a1a2e;margin-bottom:8px}
.modal-flabel span{color:#9ca3af;font-weight:400}
.modal-input{width:100%;padding:13px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;font-family:inherit;color:#1a1a2e;outline:none;transition:border .15s}
.modal-input:focus{border-color:#2563eb}
.modal-hint{font-size:11.5px;color:#9ca3af;margin-top:8px}
.modal-actions{display:flex;gap:12px;margin-top:22px}
.modal-actions .btn{flex:1;padding:13px}

/* toast */
.toast{position:fixed;bottom:24px;right:24px;background:#1a1a2e;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;opacity:0;transform:translateY(10px);transition:all .3s;z-index:400}
.toast.show{opacity:1;transform:translateY(0)}

@media(max-width:820px){.sidebar{display:none}.main{margin-left:0;padding:16px}}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-icon"><svg viewBox="0 0 24 24"><path d="M3 18L7 6l4 8 3-5 4 9H3z"/></svg></div>
    <div><div class="sb-logo-name">Batam Log</div><div class="sb-logo-sub">Smart Berth System</div></div>
  </div>
  <nav class="sb-nav">
    <div class="nav-sec">Monitoring</div>
    <a class="nav-item" href="dashboard.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>
    <a class="nav-item active" href="antrean.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      Antrean
    </a>
    <div class="nav-sec">System</div>
    <a class="nav-item danger" href="dashboard.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar">A</div>
      <div><div class="sb-uname">Operator</div><div class="sb-urole">Dermaga</div></div>
    </div>
  </div>
</aside>

<main class="main">
  <div class="page-head">
    <div class="page-title">Manajemen Antrean</div>
    <div class="page-sub">Waiting list kapal saat dermaga penuh · sistem FIFO (first in, first out)</div>
  </div>

  <!-- status dermaga + jumlah antre -->
  <div class="row">
    <div class="card status-box">
      <div id="statusBadge" class="status-badge st-open">TERSEDIA</div>
      <div class="status-detail">
        Status Dermaga<br>
        <b id="berthInfo">0 / 2 terisi</b>
      </div>
    </div>
    <div class="card count-box">
      <div class="count-num" id="countAntre">0</div>
      <div class="count-lbl">kapal menunggu</div>
    </div>
    <div class="card count-box" style="flex:1;min-width:260px;text-align:left">
      <div class="count-lbl" style="margin-bottom:8px">Panggil antrean berikutnya ke:</div>
      <div class="actions">
        <button class="btn btn-call" onclick="panggil('D1')">Panggil ke D1</button>
        <button class="btn btn-call" onclick="panggil('D2')">Panggil ke D2</button>
      </div>
    </div>
  </div>

  <!-- durasi sandar (countdown) -->
  <div class="sec-title">Durasi Sandar Dermaga</div>
  <div class="row" id="timerRow">
    <!-- kartu timer diisi oleh JS -->
  </div>

  <!-- panel antrean -->
  <div class="row">
    <div class="card panel">
      <div class="panel-head">
        <div class="panel-title">Daftar Antrean</div>
        <button class="btn btn-primary" onclick="tambah()">+ Tambah ke Antrean</button>
      </div>
      <table>
        <thead>
          <tr><th>No</th><th>Nama Kapal</th><th>Waktu Masuk</th><th>Status</th><th>Tujuan</th><th>Aksi</th></tr>
        </thead>
        <tbody id="queueBody">
          <tr><td colspan="6" class="empty">Belum ada kapal dalam antrean.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ── MODAL TAMBAH ANTREAN ── -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <button class="modal-close" onclick="tutupModal()">&times;</button>
    <div class="modal-head">
      <div class="modal-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
      </div>
      <div>
        <div class="modal-title">Tambah Kapal ke Antrean</div>
        <div class="modal-sub">Kapal akan mendapat nomor antrean otomatis</div>
      </div>
    </div>

    <div class="modal-no">
      <div>
        <div class="modal-no-lbl">NOMOR ANTREAN</div>
        <div class="modal-no-val" id="previewNo">#—</div>
      </div>
      <div class="modal-no-note">otomatis diberikan</div>
    </div>

    <label class="modal-flabel">Nama / Kode Kapal <span>(opsional)</span></label>
    <input type="text" id="inputNama" class="modal-input" placeholder="mis. MV Sinar Jaya" autocomplete="off">
    <div class="modal-hint">Boleh dikosongkan bila identitas kapal belum diketahui.</div>

    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="tutupModal()">Batal</button>
      <button class="btn btn-primary" onclick="submitTambah()">Tambahkan</button>
    </div>
  </div>
</div>

<!-- ── MODAL SET TIMER ── -->
<div class="modal-overlay" id="modalTimer">
  <div class="modal">
    <button class="modal-close" onclick="tutupTimer()">&times;</button>
    <div class="modal-head">
      <div class="modal-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M9 2h6"/></svg>
      </div>
      <div>
        <div class="modal-title">Set Alokasi Waktu Sandar</div>
        <div class="modal-sub" id="timerBerthLbl">Dermaga —</div>
      </div>
    </div>

    <label class="modal-flabel">Alokasi waktu (menit)</label>
    <input type="number" id="inputMenit" class="modal-input" value="30" min="1" max="240">
    <div class="chip-row">
      <button class="chip" onclick="setMenit(15)">15 mnt</button>
      <button class="chip" onclick="setMenit(30)">30 mnt</button>
      <button class="chip" onclick="setMenit(45)">45 mnt</button>
      <button class="chip" onclick="setMenit(60)">60 mnt</button>
    </div>
    <!-- <div class="modal-hint">Timer akan menghitung mundur; saat habis, kartu berubah merah sebagai peringatan.</div> -->

    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="tutupTimer()">Batal</button>
      <button class="btn btn-primary" onclick="submitTimer()">Mulai Hitung Mundur</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '../api/';

function toast(msg){
  const t=document.getElementById('toast');
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2600);
}

const BADGE={menunggu:['bg-wait','Menunggu'],dipanggil:['bg-call','Dipanggil'],ditunda:['bg-hold','Ditunda'],sandar:['bg-dock','Sandar']};

function fmtWaktu(dt){
  if(!dt) return '-';
  const d=new Date(dt.replace(' ','T'));
  if(isNaN(d)) return dt;
  return d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
}

async function load(){
  try{
    const r=await fetch(API+'queue_get.php'); const d=await r.json();
    if(!d.success) return;

    // status dermaga
    const badge=document.getElementById('statusBadge');
    if(d.penuh){ badge.textContent='PENUH'; badge.className='status-badge st-full'; }
    else{ badge.textContent='TERSEDIA'; badge.className='status-badge st-open'; }
    document.getElementById('berthInfo').textContent=d.berth_occupied+' / '+d.berth_total+' terisi';
    document.getElementById('countAntre').textContent=d.jumlah_antre;
    if(d.next_no) nextNo=d.next_no;

    // tabel antrean
    const body=document.getElementById('queueBody');
    if(!d.antrean.length){
      body.innerHTML='<tr><td colspan="6" class="empty">Belum ada kapal dalam antrean.</td></tr>';
      return;
    }
    body.innerHTML=d.antrean.map(a=>{
      const b=BADGE[a.status]||['bg-wait',a.status];
      let aksi='';
      if(a.status==='dipanggil'){
        aksi=`<button class="btn btn-sm btn-ok" onclick="aksi(${a.id},'konfirmasi')">Konfirmasi Sandar</button>
              <button class="btn btn-sm btn-warn" onclick="aksi(${a.id},'tunda')">Tunda</button>`;
      }else if(a.status==='sandar'){
        aksi=`<button class="btn btn-sm btn-ghost" onclick="aksi(${a.id},'selesai')">Selesai</button>`;
      }else{
        aksi=`<button class="btn btn-sm btn-danger" onclick="aksi(${a.id},'batal')">Batal</button>`;
      }
      return `<tr>
        <td><span class="no-badge">${a.no_antrean}</span></td>
        <td>${a.nama_kapal||'<span style="color:#9ca3af">— tanpa nama —</span>'}</td>
        <td>${fmtWaktu(a.waktu_masuk)}</td>
        <td><span class="badge ${b[0]}">${b[1]}</span></td>
        <td>${a.berth_tujuan||'-'}</td>
        <td><div class="actions">${aksi}</div></td>
      </tr>`;
    }).join('');
  }catch(e){ console.error(e); }
}

let nextNo = 1; // nomor antrean berikutnya (untuk preview di modal)

function tambah(){
  document.getElementById('previewNo').textContent = '#' + String(nextNo).padStart(3,'0');
  document.getElementById('inputNama').value = '';
  document.getElementById('modalTambah').classList.add('show');
  setTimeout(()=>document.getElementById('inputNama').focus(),100);
}
function tutupModal(){
  document.getElementById('modalTambah').classList.remove('show');
}
async function submitTambah(){
  const nama=document.getElementById('inputNama').value.trim();
  const r=await fetch(API+'queue_add.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({nama_kapal:nama})});
  const d=await r.json();
  toast(d.success?('Ditambahkan sebagai antrean #'+d.no_antrean):('Gagal: '+d.message));
  tutupModal();
  load();
}
// tutup modal saat klik area gelap atau tekan Escape / Enter untuk submit
document.getElementById('modalTambah').addEventListener('click',e=>{ if(e.target.id==='modalTambah') tutupModal(); });
document.addEventListener('keydown',e=>{
  const open=document.getElementById('modalTambah').classList.contains('show');
  if(!open) return;
  if(e.key==='Escape') tutupModal();
  if(e.key==='Enter')  submitTambah();
});

async function panggil(berth){
  const r=await fetch(API+'queue_call.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({berth_tujuan:berth})});
  const d=await r.json();
  toast(d.success?(d.message+' → '+berth):d.message);
  load();
}

async function aksi(id,aksi){
  const r=await fetch(API+'queue_update.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,aksi})});
  const d=await r.json();
  toast(d.success?d.message:('Gagal: '+d.message));
  load();
}

// ── TIMER COUNTDOWN ──────────────────────────────────────
let timers = {};
function fmtMMSS(det){ if(det<0) det=0; const m=Math.floor(det/60), s=det%60; return m+':'+String(s).padStart(2,'0'); }

async function loadTimer(){
  try{
    const r=await fetch(API+'timer_get.php'); const d=await r.json();
    if(!d.success) return;
    d.timers.forEach(t=>{ timers[t.berth_id]=t; });
    renderTimer();
  }catch(e){ console.error(e); }
}
function renderTimer(){
  const row=document.getElementById('timerRow');
  const ids=Object.keys(timers).sort();
  if(!ids.length){ row.innerHTML='<div class="card" style="flex:1">Belum ada data timer. Jalankan berth_timer.sql dulu.</div>'; return; }
  row.innerHTML=ids.map(id=>{
    const t=timers[id]; const aktif=t.aktif==1; const zona=t.zona||'off';
    const timeTxt = aktif ? fmtMMSS(t.sisa_detik) : '--:--';
    let warn='';
    if(aktif && zona==='habis') warn='<div class="tc-warn z-habis" style="color:#dc2626">WAKTU HABIS — kosongkan berth</div>';
    else if(aktif && zona==='segera') warn='<div class="tc-warn z-segera" style="color:#d97706">Segera selesai (\u22645 menit)</div>';
    const info = aktif ? `Alokasi: <b>${t.alokasi_menit} menit</b>${t.nama_kapal?' \u00b7 '+t.nama_kapal:''}` : 'Belum ada alokasi waktu';
    const btn = aktif
      ? `<button class="btn btn-danger" style="width:100%" onclick="stopTimer('${id}')">Stop / Selesai</button>`
      : `<button class="btn btn-primary" style="width:100%" onclick="bukaTimer('${id}')">+ Set Alokasi Waktu</button>`;
    return `<div class="card timer-card">
      <div class="tc-head"><div class="tc-name">Dermaga ${id}</div>
      <span class="tc-badge ${aktif?'tc-on':'tc-off'}">${aktif?'SANDAR':'KOSONG'}</span></div>
      <div class="tc-timerbox z-${aktif?zona:'off'}"><div class="tc-time" id="time-${id}">${timeTxt}</div></div>
      <div class="tc-info">${info}</div>${warn}${btn}
    </div>`;
  }).join('');
}
setInterval(()=>{
  let re=false;
  Object.keys(timers).forEach(id=>{
    const t=timers[id];
    if(t.aktif==1 && t.sisa_detik!==null){
      t.sisa_detik--;
      const el=document.getElementById('time-'+id);
      if(el) el.textContent=fmtMMSS(t.sisa_detik);
      const nz=t.sisa_detik<=0?'habis':(t.sisa_detik<=300?'segera':'normal');
      if(nz!==t.zona){ t.zona=nz; re=true; }
    }
  });
  if(re) renderTimer();
},1000);
let timerBerthAktif='';
function bukaTimer(id){ timerBerthAktif=id; document.getElementById('timerBerthLbl').textContent='Dermaga '+id; document.getElementById('inputMenit').value=30; document.getElementById('modalTimer').classList.add('show'); }
function tutupTimer(){ document.getElementById('modalTimer').classList.remove('show'); }
function setMenit(m){ document.getElementById('inputMenit').value=m; }
async function submitTimer(){
  const menit=parseInt(document.getElementById('inputMenit').value)||0;
  if(menit<=0){ toast('Masukkan alokasi menit yang valid'); return; }
  const r=await fetch(API+'timer_set.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({aksi:'mulai',berth_id:timerBerthAktif,alokasi_menit:menit})});
  const d=await r.json(); toast(d.message); tutupTimer(); loadTimer();
}
async function stopTimer(id){
  const r=await fetch(API+'timer_set.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({aksi:'stop',berth_id:id})});
  const d=await r.json(); toast(d.message); loadTimer();
}
document.getElementById('modalTimer').addEventListener('click',e=>{ if(e.target.id==='modalTimer') tutupTimer(); });
loadTimer();
setInterval(loadTimer,10000);

load();
setInterval(load,3000); // refresh tiap 3 detik
</script>
</body>
</html>
