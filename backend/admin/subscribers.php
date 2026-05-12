<?php
require_once '../config.php';
requireAdmin();
$pdo = getDB();

// Handle remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['deactivate'])) {
    $pdo->prepare('UPDATE newsletter_subscribers SET active=0 WHERE id=?')
        ->execute([(int)$_POST['id']]);
}

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
    $rows = $pdo->query(
        "SELECT name, phone, email, subscribed_at
         FROM newsletter_subscribers WHERE active=1
         ORDER BY subscribed_at DESC"
    )->fetchAll();
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Phone', 'Email', 'Subscribed At']);
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
    exit;
}

$total       = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE active=1")->fetchColumn();
$subscribers = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscribers | YA Admin</title>
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<style>
  .sidebar { width:220px; min-height:100vh; background:#4c00b0; position:fixed; top:0; left:0; z-index:100; }
  .sidebar .brand { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.2); }
  .sidebar a { color:rgba(255,255,255,.8); text-decoration:none; display:block; padding:11px 20px; transition:.2s; }
  .sidebar a:hover, .sidebar a.active { color:#fff; background:rgba(255,255,255,.18); }
  .sidebar .logout { position:absolute; bottom:0; width:100%; border-top:1px solid rgba(255,255,255,.2); }
  .main { margin-left:220px; padding:30px; background:#f4f6fb; min-height:100vh; }
</style>
</head>
<body>
<div class="sidebar">
  <div class="brand">
    <img src="../../assets/img/blog/ya-logo.png" alt="YA" style="height:38px"><br>
    <small class="text-white-50 d-block mt-1">Admin Panel</small>
  </div>
  <a href="index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
  <a href="programs.php"><i class="bi bi-grid-3x3-gap me-2"></i>Programs</a>
  <a href="subscribers.php" class="active"><i class="bi bi-people me-2"></i>Subscribers</a>
  <a href="../../index.html" target="_blank"><i class="bi bi-globe me-2"></i>View Site</a>
  <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<div class="main">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
      Newsletter Subscribers
      <span class="badge bg-primary ms-2" style="font-size:.75rem"><?= $total ?> active</span>
    </h4>
    <a href="?export=1" class="btn btn-success">
      <i class="bi bi-download me-1"></i>Export CSV
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#f8f9fa">
          <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($subscribers as $s): ?>
          <tr class="<?= $s['active'] ? '' : 'text-muted' ?>">
            <td><?= htmlspecialchars($s['name'] ?: '—') ?></td>
            <td><?= htmlspecialchars($s['phone']) ?></td>
            <td><?= htmlspecialchars($s['email'] ?: '—') ?></td>
            <td><?= htmlspecialchars(date('d M Y', strtotime($s['subscribed_at']))) ?></td>
            <td>
              <span class="badge <?= $s['active'] ? 'bg-success' : 'bg-secondary' ?>">
                <?= $s['active'] ? 'Active' : 'Removed' ?>
              </span>
            </td>
            <td class="text-end">
              <?php if ($s['active']): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <button name="deactivate" value="1"
                          class="btn btn-sm btn-outline-warning"
                          onclick="return confirm('Remove this subscriber?')">
                    Remove
                  </button>
                </form>
              <?php endif ?>
            </td>
          </tr>
        <?php endforeach ?>
        <?php if (!$subscribers): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No subscribers yet.</td></tr>
        <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body></html>
