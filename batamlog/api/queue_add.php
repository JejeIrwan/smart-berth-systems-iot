<?php
// ============================================================
//  BATAM LOG — API: Tambah Kapal ke Antrean
//  Method : POST  (JSON: { nama_kapal? })
//  Dipakai: tombol "+ Tambah ke Antrean" di dashboard
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$pdo  = getDB();
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$nama = trim($data['nama_kapal'] ?? '');
$nama = ($nama === '') ? null : $nama;

try {
    // Nomor antrean berikutnya = maksimum saat ini + 1
    $max = (int) $pdo->query("SELECT COALESCE(MAX(no_antrean),0) FROM waiting_list")->fetchColumn();
    $no  = $max + 1;

    $stmt = $pdo->prepare(
        "INSERT INTO waiting_list (no_antrean, nama_kapal, status)
         VALUES (?, ?, 'menunggu')"
    );
    $stmt->execute([$no, $nama]);

    echo json_encode([
        'success'    => true,
        'message'    => 'Kapal ditambahkan ke antrean.',
        'no_antrean' => $no,
        'id'         => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
