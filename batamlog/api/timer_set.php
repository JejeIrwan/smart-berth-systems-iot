<?php
// ============================================================
//  BATAM LOG — API: Set / Mulai / Stop Timer Durasi Sandar
//  Method : POST (JSON)
//    aksi 'mulai'  : { berth_id, alokasi_menit, nama_kapal? }
//    aksi 'stop'   : { berth_id }
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo  = getDB();
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$aksi  = trim($data['aksi'] ?? '');
$berth = trim($data['berth_id'] ?? '');

if ($berth === '') { echo json_encode(['success'=>false,'message'=>'berth_id kosong']); exit; }

try {
    if ($aksi === 'mulai') {
        $menit = (int)($data['alokasi_menit'] ?? 0);
        $nama  = trim($data['nama_kapal'] ?? '');
        if ($menit <= 0) { echo json_encode(['success'=>false,'message'=>'Alokasi menit tidak valid']); exit; }

        $stmt = $pdo->prepare(
            "UPDATE berth_timer
             SET alokasi_menit=?, waktu_mulai=NOW(),
                 waktu_habis=DATE_ADD(NOW(), INTERVAL ? MINUTE),
                 nama_kapal=?, aktif=1
             WHERE berth_id=?"
        );
        $stmt->execute([$menit, $menit, ($nama ?: null), $berth]);
        echo json_encode(['success'=>true,'message'=>"Timer $berth dimulai ($menit menit)."]);

    } elseif ($aksi === 'stop') {
        $stmt = $pdo->prepare(
            "UPDATE berth_timer
             SET aktif=0, alokasi_menit=NULL, waktu_mulai=NULL, waktu_habis=NULL, nama_kapal=NULL
             WHERE berth_id=?"
        );
        $stmt->execute([$berth]);
        echo json_encode(['success'=>true,'message'=>"Timer $berth dihentikan."]);

    } else {
        echo json_encode(['success'=>false,'message'=>'Aksi tidak dikenal']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
