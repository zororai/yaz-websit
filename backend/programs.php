<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();

switch ($method) {

    case 'GET':
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $sql  = "SELECT id, title, description, year, image_path, detail_url
                 FROM programs WHERE status = 'active'";
        $params = [];
        if ($year) {
            $sql    .= ' AND year = ?';
            $params[] = $year;
        }
        $sql .= ' ORDER BY year DESC, id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'POST':
        if (!isAdminLoggedIn()) jsonError(401, 'Unauthorized');
        $d     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = trim($d['title'] ?? '');
        if (!$title) jsonError(400, 'Title is required');

        $imagePath = trim($d['image_path'] ?? '');
        if (!empty($_FILES['image']['tmp_name'])) {
            $imagePath = saveUploadedImage($_FILES['image']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO programs (title, description, year, image_path, detail_url, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $title,
            trim($d['description'] ?? ''),
            (int)($d['year'] ?? date('Y')),
            $imagePath,
            trim($d['detail_url'] ?? ''),
            in_array($d['status'] ?? 'active', ['active', 'draft']) ? $d['status'] : 'active',
        ]);
        http_response_code(201);
        echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Program created']);
        break;

    case 'PUT':
        if (!isAdminLoggedIn()) jsonError(401, 'Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError(400, 'ID required');

        $d     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = trim($d['title'] ?? '');
        if (!$title) jsonError(400, 'Title is required');

        $imagePath = trim($d['image_path'] ?? '');
        if (!empty($_FILES['image']['tmp_name'])) {
            $imagePath = saveUploadedImage($_FILES['image']);
        }

        $sql    = 'UPDATE programs SET title=?, description=?, year=?, detail_url=?, status=?';
        $params = [
            $title,
            trim($d['description'] ?? ''),
            (int)($d['year'] ?? date('Y')),
            trim($d['detail_url'] ?? ''),
            in_array($d['status'] ?? 'active', ['active', 'draft']) ? $d['status'] : 'active',
        ];
        if ($imagePath) {
            $sql    .= ', image_path=?';
            $params[] = $imagePath;
        }
        $sql     .= ', updated_at=NOW() WHERE id=?';
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        echo json_encode(['message' => 'Updated']);
        break;

    case 'DELETE':
        if (!isAdminLoggedIn()) jsonError(401, 'Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonError(400, 'ID required');
        $pdo->prepare('DELETE FROM programs WHERE id=?')->execute([$id]);
        echo json_encode(['message' => 'Deleted']);
        break;

    default:
        jsonError(405, 'Method not allowed');
}

function saveUploadedImage(array $file): string {
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        jsonError(400, 'Only JPG, PNG, WEBP images are allowed');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        jsonError(400, 'Image must be under 5 MB');
    }
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = 'prog_' . uniqid('', true) . '.' . $ext;
    $dir  = __DIR__ . '/../assets/img/programs/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    move_uploaded_file($file['tmp_name'], $dir . $name);
    return 'assets/img/programs/' . $name;
}
