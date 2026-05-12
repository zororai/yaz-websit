<?php
require_once '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter your username and password.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION[ADMIN_SESSION_KEY] = true;
            $_SESSION['admin_id']        = $user['id'];
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Youth Advocates</title>
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f0f2f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
  .login-card { max-width: 420px; width: 100%; border: none; border-radius: 14px; }
  .btn-ya { background: #4c00b0; border-color: #4c00b0; color: #fff; }
  .btn-ya:hover { background: #3a0085; border-color: #3a0085; color: #fff; }
  .brand-strip { background: linear-gradient(135deg, #4c00b0, #e61e65); border-radius: 14px 14px 0 0; }
</style>
</head>
<body>
<div class="login-card card shadow-lg">
  <div class="brand-strip text-white text-center py-4">
    <img src="../../assets/img/blog/ya-logo.png" alt="YA Logo" style="height:55px">
    <p class="mb-0 mt-2 fw-semibold">Admin Panel</p>
  </div>
  <div class="card-body p-4">
    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" required autofocus
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-ya w-100 py-2 fw-semibold">Login</button>
    </form>
  </div>
</div>
</body>
</html>
