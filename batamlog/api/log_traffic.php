<?php
// ============================================================
//  BATAM LOG — API: Log Traffic Event (Fix v2)
//  - Arrival & Departure digabung 1 baris per sesi
//  - Fix format waktu dari ESP32
// ============================================================

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    jsonResponse(false, 'Invalid JSON payload');
}

$pdo = getDB();

$berthId     = (int) ($data['berth_id']    ?? 0);
$eventType   =        $data['event_type']  ?? '';
$eventTime   =        $data['event_time']  ?? '';
$arrivalTime =        $data['arrival_time'] ?? '';
$dockDuration=        $data['dock_duration'] ?? null;
$tideStatus  =        $data['tide_status']  ?? '';
$tideSafe    = (int) ($data['tide_safe']    ?? 1);

// Validasi
if (!in_array($eventType, ['ARRIVAL', 'DEPARTURE']) || $berthId < 1) {
    jsonResponse(false, 'Invalid event_type or berth_id');
}

// ── Fix parse waktu dari ESP32 ────────────────────────────────
// Format dari ESP32: "22/04/2026 08:36:36" (dd/mm/yyyy HH:ii:ss)
// PHP strtotime tidak bisa baca format ini langsung
function parseWaktuESP32(?string $waktu): ?string {
    if (!$waktu) return null;
    // Coba parse format dd/mm/yyyy HH:ii:ss
    $d = DateTime::createFromFormat('d/m/Y H:i:s', trim($waktu));
    if ($d) return $d->format('Y-m-d H:i:s');
    // Fallback strtotime
    $t = strtotime($waktu);
    if ($t && $t > 0) return date('Y-m-d H:i:s', $t);
    return null;
}

$eventTimeParsed   = parseWaktuESP32($eventTime)   ?? date('Y-m-d H:i:s');
$arrivalTimeParsed = parseWaktuESP32($arrivalTime);

// Kondisi
$condition = $tideSafe ? 'NORMAL' : 'SURUT_WARNING';

// Hitung dock_seconds
$dockSeconds = parseDockDuration($dockDuration);

// ── Logika 1 baris per sesi sandar ───────────────────────────
if ($eventType === 'ARRIVAL') {
    // Buat baris baru saat kapal tiba
    $stmt = $pdo->prepare("
        INSERT INTO traffic_log
            (berth_id, event_type, event_time, arrival_time,
             tide_status, tide_safe, `condition`)
        VALUES
            (:berth_id, 'ARRIVAL', :event_time, :arrival_time,
             :tide_status, :tide_safe, :condition)
    ");
    $stmt->execute([
        ':berth_id'    => $berthId,
        ':event_time'  => $eventTimeParsed,
        ':arrival_time'=> $eventTimeParsed,
        ':tide_status' => $tideStatus,
        ':tide_safe'   => $tideSafe,
        ':condition'   => $condition,
    ]);

    jsonResponse(true, 'Arrival logged', [
        'log_id'    => $pdo->lastInsertId(),
        'event_type'=> 'ARRIVAL',
        'berth_id'  => $berthId,
    ]);

} else {
    // Update baris ARRIVAL terakhir dengan data departure
    // Cari baris arrival terakhir yang belum ada departure
    $findStmt = $pdo->prepare("
        SELECT id FROM traffic_log
        WHERE berth_id = :berth_id
        AND event_type = 'ARRIVAL'
        AND departure_time IS NULL
        ORDER BY id DESC
        LIMIT 1
    ");
    $findStmt->execute([':berth_id' => $berthId]);
    $row = $findStmt->fetch();

    if ($row) {
        // Update baris existing dengan data departure
        $updateStmt = $pdo->prepare("
            UPDATE traffic_log SET
                event_type     = 'COMPLETE',
                departure_time = :departure_time,
                dock_duration  = :dock_duration,
                dock_seconds   = :dock_seconds,
                `condition`    = :condition
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':departure_time' => $eventTimeParsed,
            ':dock_duration'  => $dockDuration,
            ':dock_seconds'   => $dockSeconds,
            ':condition'      => $condition,
            ':id'             => $row['id'],
        ]);

        jsonResponse(true, 'Departure logged', [
            'log_id'    => $row['id'],
            'event_type'=> 'DEPARTURE',
            'berth_id'  => $berthId,
        ]);
    } else {
        // Tidak ada arrival sebelumnya, buat baris baru
        $stmt = $pdo->prepare("
            INSERT INTO traffic_log
                (berth_id, event_type, event_time, departure_time,
                 dock_duration, dock_seconds, tide_status, tide_safe, `condition`)
            VALUES
                (:berth_id, 'COMPLETE', :event_time, :departure_time,
                 :dock_duration, :dock_seconds, :tide_status, :tide_safe, :condition)
        ");
        $stmt->execute([
            ':berth_id'      => $berthId,
            ':event_time'    => $eventTimeParsed,
            ':departure_time'=> $eventTimeParsed,
            ':dock_duration' => $dockDuration,
            ':dock_seconds'  => $dockSeconds,
            ':tide_status'   => $tideStatus,
            ':tide_safe'     => $tideSafe,
            ':condition'     => $condition,
        ]);

        jsonResponse(true, 'Departure logged (no arrival)', [
            'log_id'    => $pdo->lastInsertId(),
            'event_type'=> 'DEPARTURE',
            'berth_id'  => $berthId,
        ]);
    }
}

// ── Helper: Parse durasi sandar ke detik ─────────────────────
function parseDockDuration(?string $dur): int {
    if (!$dur) return 0;
    $seconds = 0;
    if (preg_match('/(\d+)h/', $dur, $m)) $seconds += (int)$m[1] * 3600;
    if (preg_match('/(\d+)m/', $dur, $m)) $seconds += (int)$m[1] * 60;
    if (preg_match('/(\d+)s/', $dur, $m)) $seconds += (int)$m[1];
    return $seconds;
}