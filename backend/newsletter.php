<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

require_once __DIR__ . '/config.php';

$data  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$phone = trim($data['phone'] ?? '');
$name  = trim($data['name']  ?? '');
$email = trim($data['email'] ?? '');

if (!$phone) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Phone number is required.']));
}

// Accept formats like +263771234567, 0771234567, 263771234567
if (!preg_match('/^[\+\d\s\-()]{7,20}$/', $phone)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']));
}

if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Please enter a valid email address.']));
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'INSERT INTO newsletter_subscribers (name, phone, email) VALUES (?, ?, ?)'
    );
    $stmt->execute([$name ?: null, $phone, $email ?: null]);
    echo json_encode(['success' => true, 'message' => 'Thank you! You are now subscribed.']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        // Duplicate phone number
        echo json_encode(['success' => false, 'message' => 'This number is already subscribed.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not subscribe. Please try again.']);
    }
}
