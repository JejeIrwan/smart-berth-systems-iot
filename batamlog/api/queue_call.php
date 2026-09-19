<?php
// ============================================================
//  BATAM LOG — API: Panggil Antrean Terdepan (FIFO)
//  Method : POST  (JSON: { berth_tujuan })  mis. "D1"
//  Logika : ambil antrean paling lama menunggu (menunggu/ditunda),
//           urut berdasarkan waktu_masuk ASC  -> ANTI-SEROBOT.
//           Kapal yang sempat 'ditunda' TETAP di depan jika
//           waktu_masuk-nya lebih awal.
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo  = getDB();
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$berth = trim($data['berth_tujuan'] ?? '');

try {
    // Ambil antrean terdepan: prioritas berdasarkan WAKTU MASUK (bukan kesiapan)
    $next = $pdo->query(
        "SELECT id, no_antrean, nama_kapal
         FROM waiting_list
         WHERE status IN ('menunggu','ditunda')
         ORDER BY waktu_masuk ASC
         LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);

    if (!$next) {
        echo json_encode(['success' => false, 'message' => 'Antrean kosong.']);
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE waiting_list
         SET status = 'dipanggil', call_time = NOW(), berth_tujuan = ?
         WHERE id = ?"
    );
    $stmt->execute([$berth ?: null, $next['id']]);

    echo json_encode([
        'success'      => true,
        'message'      => 'Antrean #' . $next['no_antrean'] . ' dipanggil.',
        'id'           => $next['id'],
        'no_antrean'   => $next['no_antrean'],
        'nama_kapal'   => $next['nama_kapal'],
        'berth_tujuan' => $berth
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
