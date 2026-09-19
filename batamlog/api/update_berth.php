<?php
// ============================================================
//  BATAM LOG — API: Update Berth Status
//  Endpoint  : POST /batamlog/api/update_berth.php
//  Dipanggil : ESP32 setiap 5 detik
//  Payload   : JSON dari ESP32
// ============================================================

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

// Ambil body JSON dari ESP32
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON payload');
}

$pdo = getDB();

// ── Parse data dari ESP32 ────────────────────────────────────
$timestamp   = $data['timestamp']   ?? date('Y-m-d H:i:s');
$tideAdc     = (int)  ($data['tide_adc']    ?? 0);
$tideStatus  = $data['tide_status'] ?? 'UNKNOWN';
$tideSafe    = (int)  ($data['tide_safe']   ?? 0);
$tideDepth   = isset($data['tide_depth'])   ? (float) $data['tide_depth']   : null; // kedalaman cm
$tideDepthM  = isset($data['tide_depth_m']) ? (float) $data['tide_depth_m'] : null; // kedalaman meter
$tideKapal   =        $data['tide_kapal']   ?? '--';   // rekomendasi kapal

$berths = [
    1 => $data['berth1'] ?? null,
    2 => $data['berth2'] ?? null,
];

// ── Update setiap berth ──────────────────────────────────────
foreach ($berths as $berthNum => $berthData) {
    if (!$berthData) continue;

    $status      = $berthData['status']       ?? 'AVAILABLE';
    $distanceCm  = (float) ($berthData['distance_cm']  ?? 999);
    $arrivalTime = !empty($berthData['arrival_time'])
                   ? date('Y-m-d H:i:s', strtotime($berthData['arrival_time']))
                   : null;
    $dockDuration = $berthData['dock_duration'] ?? null;

    // Update berth_status (hanya 1 row per berth)
    $stmt = $pdo->prepare("
        UPDATE berth_status
        SET
            status        = :status,
            distance_cm   = :distance_cm,
            tide_status   = :tide_status,
            tide_adc      = :tide_adc,
            tide_safe     = :tide_safe,
            arrival_time  = :arrival_time,
            dock_duration = :dock_duration,
            updated_at    = NOW()
        WHERE berth_id = :berth_id
    ");
    $stmt->execute([
        ':status'        => $status,
        ':distance_cm'   => $distanceCm,
        ':tide_status'   => $tideStatus,
        ':tide_adc'      => $tideAdc,
        ':tide_safe'     => $tideSafe,
        ':arrival_time'  => $arrivalTime,
        ':dock_duration' => $dockDuration,
        ':berth_id'      => $berthNum,
    ]);
}

// ── Simpan log tide setiap update ────────────────────────────
$stmt = $pdo->prepare("
    INSERT INTO tide_log (tide_adc, tide_status, tide_safe, tide_depth, tide_depth_m, tide_kapal, recorded_at)
    VALUES (:adc, :status, :safe, :depth, :depth_m, :kapal, NOW())
");
$stmt->execute([
    ':adc'     => $tideAdc,
    ':status'  => $tideStatus,
    ':safe'    => $tideSafe,
    ':depth'   => $tideDepth,
    ':depth_m' => $tideDepthM,
    ':kapal'   => $tideKapal,
]);

jsonResponse(true, 'Berth status updated', [
    'server_time' => date('Y-m-d H:i:s'),
    'berths_updated' => count(array_filter($berths)),
]);
