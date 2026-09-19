<?php
// ============================================================
//  BATAM LOG — API: Ambil Status Timer Semua Dermaga
//  Method : GET
//  Return : sisa detik tiap berth (dihitung dari waktu_habis)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

try {
    $rows = $pdo->query(
        "SELECT berth_id, alokasi_menit, nama_kapal, aktif,
                waktu_mulai, waktu_habis,
                TIMESTAMPDIFF(SECOND, NOW(), waktu_habis) AS sisa_detik
         FROM berth_timer
         ORDER BY berth_id"
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
        $r['aktif']       = (int)$r['aktif'];
        $r['sisa_detik']  = $r['sisa_detik'] === null ? null : (int)$r['sisa_detik'];
        // zona warna: hijau >5mnt, kuning <=5mnt, merah habis
        if (!$r['aktif']) {
            $r['zona'] = 'off';
        } elseif ($r['sisa_detik'] <= 0) {
            $r['zona'] = 'habis';
        } elseif ($r['sisa_detik'] <= 300) {
            $r['zona'] = 'segera';
        } else {
            $r['zona'] = 'normal';
        }
    }

    echo json_encode(['success'=>true,'timers'=>$rows,'server_time'=>date('Y-m-d H:i:s')]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
