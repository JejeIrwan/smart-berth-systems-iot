<?php
// ============================================================
//  BATAM LOG — API: Get Dashboard Data
//  Endpoint  : GET /batamlog/api/get_dashboard.php
//  Dipanggil : Web Dashboard (polling setiap 5 detik)
// ============================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

$pdo = getDB();
$action = $_GET['action'] ?? 'overview';

switch ($action) {

    // ── Status realtime semua dermaga ────────────────────────
    case 'overview':
        $stmt = $pdo->query("SELECT * FROM v_berth_overview ORDER BY berth_id");
        $berths = $stmt->fetchAll();

        // Ambil tide terbaru
       $tideStmt = $pdo->query("
    SELECT tide_adc, tide_status, tide_safe,
           tide_depth, tide_depth_m, tide_kapal,
           recorded_at
    FROM tide_log
    ORDER BY id DESC
    LIMIT 1
");
        $tide = $tideStmt->fetch();

        jsonResponse(true, 'OK', [
            'berths'       => $berths,
            'tide'         => $tide,
            'server_time'  => date('d/m/Y H:i:s'),
        ]);
        break;

    // ── History traffic (untuk tabel di dashboard) ───────────
  case 'traffic':
    $limit  = min((int)($_GET['limit']  ?? 50), 200);
    $offset = (int)($_GET['offset'] ?? 0);
    $berthFilter = $_GET['berth'] ?? '';

    $where = $berthFilter
        ? "WHERE b.berth_code = :berth"
        : "";

    $stmt = $pdo->prepare("
        SELECT
            tl.id,
            b.berth_code,
            DATE_FORMAT(tl.arrival_time,   '%d/%m/%Y %H:%i:%s') AS arrival_time,
            DATE_FORMAT(tl.departure_time, '%d/%m/%Y %H:%i:%s') AS departure_time,
            tl.dock_duration,
            tl.tide_status,
            tl.`condition`,
            tl.event_type
        FROM traffic_log tl
        JOIN berth b ON b.id = tl.berth_id
        $where
        ORDER BY tl.id DESC
        LIMIT :limit OFFSET :offset
    ");
    if ($berthFilter) $stmt->bindValue(':berth', $berthFilter);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();

    $countStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM traffic_log" .
        ($berthFilter ? " tl JOIN berth b ON b.id = tl.berth_id WHERE b.berth_code = :berth" : "")
    );
    if ($berthFilter) $countStmt->bindValue(':berth', $berthFilter);
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();

    jsonResponse(true, 'OK', [
        'logs'   => $logs,
        'total'  => $total,
        'limit'  => $limit,
        'offset' => $offset,
    ]);
    break;

    // ── Statistik ringkas untuk card di dashboard ────────────
    case 'stats':
    // Total kapal hari ini

    
    // TEST LANGSUNG
    $test = $pdo->query("SELECT COUNT(*) FROM traffic_log WHERE arrival_time IS NOT NULL AND DATE(arrival_time) = CURDATE()");
    $testCount = (int)$test->fetchColumn();
    error_log("DEBUG STATS today_count: " . $testCount);
    $todayStmt = $pdo->query("
        SELECT COUNT(*) FROM traffic_log
        WHERE DATE(arrival_time) = CURDATE()
        AND arrival_time IS NOT NULL
    ");
    $todayCount = (int)$todayStmt->fetchColumn();

    // Total kapal bulan ini
    $monthStmt = $pdo->query("
        SELECT COUNT(*) FROM traffic_log
        WHERE MONTH(arrival_time) = MONTH(NOW())
        AND YEAR(arrival_time)    = YEAR(NOW())
        AND arrival_time IS NOT NULL
    ");
    $monthCount = (int)$monthStmt->fetchColumn();

    // Rata-rata durasi sandar
    $avgStmt = $pdo->query("
        SELECT AVG(dock_seconds) FROM traffic_log
        WHERE dock_seconds > 0
        AND DATE(arrival_time) = CURDATE()
    ");
    $avgSeconds  = (int)($avgStmt->fetchColumn() ?? 0);
    $avgDuration = secondsToHuman($avgSeconds);

    // Surut warning hari ini
    $surutStmt = $pdo->query("
        SELECT COUNT(*) FROM traffic_log
        WHERE `condition` = 'SURUT_WARNING'
        AND DATE(event_time) = CURDATE()
    ");
    $surutCount = (int)$surutStmt->fetchColumn();

    jsonResponse(true, 'OK', [
        'today_arrivals'    => $todayCount,
        'month_arrivals'    => $monthCount,
        'avg_dock_duration' => $avgDuration,
        'surut_warnings'    => $surutCount,
    ]);
    break;

    // ── Clear semua traffic log ──────────────────────────────
    case 'clear_logs':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, 'POST required for clear_logs');
        }
        $pdo->exec("DELETE FROM traffic_log");
        $pdo->exec("DELETE FROM tide_log");
        jsonResponse(true, 'Semua log berhasil dihapus');
        break;

    default:
        jsonResponse(false, 'Unknown action');
}

// ── Helper: detik → string manusiawi ────────────────────────
function secondsToHuman(int $sec): string {
    if ($sec <= 0) return '0m';
    $h = intdiv($sec, 3600);
    $m = intdiv($sec % 3600, 60);
    return ($h > 0 ? "{$h}j " : '') . "{$m}m";
}
