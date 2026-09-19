<?php
// ============================================================
//  BATAM LOG — API: Export Traffic History ke Excel
// ============================================================
require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

// Header agar browser mengunduh sebagai file Excel
$filename = "traffic_history_" . date('Ymd_His') . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Ambil data traffic (terbaru di atas)
$stmt = $pdo->query("SELECT * FROM traffic_log ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ubah detik -> format jam:menit:detik
function fmtDurasi($detik) {
    $detik = (int)$detik;
    if ($detik <= 0) return "-";
    $j = floor($detik / 3600);
    $m = floor(($detik % 3600) / 60);
    $s = $detik % 60;
    if ($j > 0) return sprintf("%dj %dm %dd", $j, $m, $s);
    return sprintf("%dm %dd", $m, $s);
}

// Baris judul kolom (rapi)
echo "No\tBerth\tStatus\tKedatangan\tKeberangkatan\tDurasi Sandar\tKondisi Air\tAman\n";

$no = 1;
foreach ($rows as $r) {
    $berth   = "D" . ($r['berth_id'] ?? '-');
    $aman    = ($r['tide_safe'] ?? 0) ? "Ya" : "Tidak";
    $durasi  = fmtDurasi($r['dock_seconds'] ?? 0);

    echo $no++ . "\t"
       . $berth . "\t"
       . ($r['event_type']     ?? '-') . "\t"
       . ($r['arrival_time']   ?? '-') . "\t"
       . ($r['departure_time'] ?? '-') . "\t"
       . $durasi . "\t"
       . ($r['tide_status']    ?? '-') . "\t"
       . $aman . "\n";
}

if (count($rows) === 0) {
    echo "1\tBelum ada data traffic.\n";
}