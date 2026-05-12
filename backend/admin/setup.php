<?php
/**
 * ONE-TIME SETUP SCRIPT
 * -------------------------------------------------------
 * 1. Open this in your browser: yoursite.com/backend/admin/setup.php
 * 2. It creates the default admin user.
 * 3. DELETE this file immediately after running it!
 * -------------------------------------------------------
 * Default credentials (change before running if needed):
 *   Username: admin
 *   Password: YA@Admin2024!
 */

require_once '../config.php';

$username = 'admin';
$password = 'YA@Admin2024!';   // ← CHANGE THIS before running

$pdo  = getDB();
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO admin_users (username, password_hash)
     VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = ?'
);
$stmt->execute([$username, $hash, $hash]);
?><!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Setup</title>
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4">
<div class="card p-4 shadow" style="max-width:500px">
  <h4 class="text-success">Setup complete!</h4>
  <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
  <p><strong>Password:</strong> <?= htmlspecialchars($password) ?></p>
  <a href="login.php" class="btn btn-primary">Go to Login</a>
  <hr>
  <p class="text-danger fw-bold">Delete this file (setup.php) now!</p>
</div>
</body></html>
