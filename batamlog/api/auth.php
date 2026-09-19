<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$action = $_GET['action'] ?? 'check';

switch ($action) {

    case 'login':
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true);
        $username = trim($body['username'] ?? '');
        $password =      $body['password'] ?? '';

        if (!$username || !$password) {
            jsonResponse(false, 'Username dan password wajib diisi');
        }

        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role FROM admin WHERE username = :u AND is_active = 1 LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        $inputHash = hash('sha256', $password . ':batamlog_salt');

        if (!$admin || $inputHash !== $admin['password_hash']) {
            jsonResponse(false, 'Username atau password salah');
        }

        $_SESSION['admin_id']  = $admin['id'];
        $_SESSION['username']  = $admin['username'];
        $_SESSION['full_name'] = $admin['full_name'];
        $_SESSION['role']      = $admin['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['token']     = md5($admin['id'] . $admin['username'] . date('Ymd') . 'batamlog_secret');

        $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = :id")->execute([':id' => $admin['id']]);

        jsonResponse(true, 'Login berhasil', [
            'user'       => [
                'id'        => $admin['id'],
                'username'  => $admin['username'],
                'full_name' => $admin['full_name'],
                'role'      => $admin['role'],
            ],
            'token'      => $_SESSION['token'],
            'session_id' => session_id(),
        ]);
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
        exit;

    case 'check':
        if (!empty($_SESSION['logged_in'])) {
            jsonResponse(true, 'Authenticated', ['user' => [
                'username'  => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role'      => $_SESSION['role'],
            ]]);
        } else {
            http_response_code(401);
            jsonResponse(false, 'Not authenticated');
        }
        break;

    default:
        jsonResponse(false, 'Unknown action');
}
