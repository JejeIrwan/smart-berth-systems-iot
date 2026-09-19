<?php
// ============================================================
//  BATAM LOG — API: Ambil Daftar Antrean + Status Dermaga
//  Method : GET
//  Dipakai: dashboard (polling untuk menampilkan panel antrean)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

try {
    // Daftar antrean aktif (belum selesai), urut FIFO
    $antrean = $pdo->query(
        "SELECT id, no_antrean, nama_kapal, waktu_masuk, status, berth_tujuan
         FROM waiting_list
         WHERE status IN ('menunggu','dipanggil','ditunda')
         ORDER BY waktu_masuk ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Cek status dermaga: berapa yang OCCUPIED (dari tabel berth_status)
    $occupied = (int) $pdo->query(
        "SELECT COUNT(*) FROM berth_status WHERE status = 'OCCUPIED'"
    )->fetchColumn();
    $total    = (int) $pdo->query("SELECT COUNT(*) FROM berth_status")->fetchColumn();
    if ($total === 0) $total = 2; // fallback 2 dermaga

    $penuh = ($occupied >= $total);

    // nomor antrean berikutnya (untuk preview di form)
    $nextNo = (int) $pdo->query("SELECT COALESCE(MAX(no_antrean),0)+1 FROM waiting_list")->fetchColumn();

    echo json_encode([
        'success'       => true,
        'penuh'         => $penuh,
        'next_no'       => $nextNo,
        'berth_occupied'=> $occupied,
        'berth_total'   => $total,
        'jumlah_antre'  => count($antrean),
        'antrean'       => $antrean,
        'server_time'   => date('d/m/Y H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
