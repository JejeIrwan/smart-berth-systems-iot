<?php
// ============================================================
//  BATAM LOG — Database Configuration
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'batamlog');
define('DB_USER',     'root');        // sesuaikan username MySQL XAMPP
define('DB_PASS',     '');            // default XAMPP kosong
define('DB_CHARSET',  'utf8mb4');

// API Key sederhana untuk autentikasi ESP32
define('ESP32_API_KEY', 'BATAMLOG_ESP32_SECRET_2024');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// ── Koneksi PDO ──────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]));
        }
    }
    return $pdo;
}

// ── Helper: JSON Response ─────────────────────────────────────
function jsonResponse(bool $success, string $message, array $data = []): void {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode(array_merge(
        ['success' => $success, 'message' => $message],
        $data
    ));
    exit;
}

// ── Helper: Validasi API Key dari ESP32 ──────────────────────
function validateApiKey(): void {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');
    if ($key !== ESP32_API_KEY) {
        http_response_code(401);
        jsonResponse(false, 'Unauthorized: Invalid API Key');
    }
}
