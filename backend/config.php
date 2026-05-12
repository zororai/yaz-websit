<?php
// ============================================================
// Database configuration — update these values for your server
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'ya_website');
define('DB_USER', 'root');     // your MySQL username
define('DB_PASS', '1997Doncaley.');         // your MySQL password

define('ADMIN_SESSION_KEY', 'ya_admin_auth');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed. Check backend/config.php.']));
        }
    }
    return $pdo;
}

function isAdminLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function jsonError(int $code, string $msg): never {
    http_response_code($code);
    die(json_encode(['error' => $msg]));
}
