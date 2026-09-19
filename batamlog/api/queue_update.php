<?php
// ============================================================
//  BATAM LOG — API: Aksi Operator pada Antrean
//  Method : POST  (JSON: { id, aksi })
//  aksi   :
//    'konfirmasi' -> dipanggil menjadi 'sandar' (kapal masuk berth)
//    'tunda'      -> dipanggil menjadi 'ditunda' (belum siap, TETAP prioritas)
//    'selesai'    -> sandar menjadi 'selesai' (keluar antrean)
//    'batal'      -> hapus dari antrean
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo  = getDB();
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id   = (int)($data['id'] ?? 0);
$aksi = trim($data['aksi'] ?? '');

if ($id <= 0 || $aksi === '') {
    echo json_encode(['success' => false, 'message' => 'Parameter id/aksi tidak lengkap.']);
    exit;
}

try {
    switch ($aksi) {
        case 'konfirmasi':   // kapal siap -> masuk berth
            $sql = "UPDATE waiting_list SET status='sandar' WHERE id=? AND status='dipanggil'";
            $msg = 'Kapal dikonfirmasi sandar.';
            break;

        case 'tunda':        // dipanggil tapi belum siap -> ditunda, TETAP prioritas
            $sql = "UPDATE waiting_list SET status='ditunda', berth_tujuan=NULL WHERE id=? AND status='dipanggil'";
            $msg = 'Kapal ditunda (giliran tetap terjaga sesuai waktu masuk).';
            break;

        case 'selesai':      // kapal berangkat -> keluar antrean
            $sql = "UPDATE waiting_list SET status='selesai' WHERE id=?";
            $msg = 'Kapal selesai, keluar dari antrean.';
            break;

        case 'batal':        // dibatalkan / dihapus
            $sql = "UPDATE waiting_list SET status='selesai', keterangan='Dibatalkan' WHERE id=?";
            $msg = 'Antrean dibatalkan.';
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
            exit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo json_encode([
        'success'  => true,
        'message'  => $msg,
        'terubah'  => $stmt->rowCount()
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
