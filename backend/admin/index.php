<?php
require_once '../config.php';
requireAdmin();
$pdo = getDB();

$totalActive      = $pdo->query("SELECT COUNT(*) FROM programs WHERE status='active'")->fetchColumn();
$totalDraft       = $pdo->query("SELECT COUNT(*) FROM programs WHERE status='draft'")->fetchColumn();
$totalSubscribers = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE active=1")->fetchColumn();
$recentSubs       = $pdo->query(
    "SELECT name, phone, subscribed_at FROM newsletter_subscribers
     WHERE active=1 ORDER BY subscribed_at DESC LIMIT 5"
)->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | YA Admin</title>
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<style>
  .sidebar { width:220px; min-height:100vh; background:#4c00b0; position:fixed; top:0; left:0; z-index:100; }
  .sidebar .brand { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.2); }
  .sidebar a { color:rgba(255,255,255,.8); text-decoration:none; display:block; padding:11px 20px; transition:.2s; }
  .sidebar a:hover, .sidebar a.active { color:#fff; background:rgba(255,255,255,.18); }
  .sidebar .logout { position:absolute; bottom:0; width:100%; border-top:1px solid rgba(255,255,255,.2); }
  .main { margin-left:220px; padding:30px; background:#f4f6fb; min-height:100vh; }
  .stat-card { border:none; border-radius:12px; }
</style>
</head>
<body>
<div class="sidebar">
  <div class="brand">
    <img src="../../assets/img/blog/ya-logo.png" alt="YA" style="height:38px"><br>
    <small class="text-white-50 d-block mt-1">Admin Panel</small>
  </div>
  <a href="index.php" class="active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
  <a href="programs.php"><i class="bi bi-grid-3x3-gap me-2"></i>Programs</a>
  <a href="subscribers.php"><i class="bi bi-people me-2"></i>Subscribers</a>
  <a href="../../index.html" target="_blank"><i class="bi bi-globe me-2"></i>View Site</a>
  <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<div class="main">
  <h4 class="mb-4 fw-bold">Dashboard</h4>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card stat-card shadow-sm p-4 text-center">
        <h2 class="fw-bold" style="color:#4c00b0"><?= (int)$totalActive ?></h2>
        <p class="text-muted mb-0">Active Programs</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card shadow-sm p-4 text-center">
        <h2 class="fw-bold" style="color:#e61e65"><?= (int)$totalDraft ?></h2>
        <p class="text-muted mb-0">Draft Programs</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card stat-card shadow-sm p-4 text-center">
        <h2 class="fw-bold" style="color:#FF7600"><?= (int)$totalSubscribers ?></h2>
        <p class="text-muted mb-0">Newsletter Subscribers</p>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Recent Subscribers</h5>
          <a href="subscribers.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <?php if ($recentSubs): ?>
        <table class="table table-sm mb-0">
          <thead><tr><th>Name</th><th>Phone</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($recentSubs as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name'] ?: '—') ?></td>
              <td><?= htmlspecialchars($s['phone']) ?></td>
              <td><?= htmlspecialchars(date('d M Y', strtotime($s['subscribed_at']))) ?></td>
            </tr>
          <?php endforeach ?>
          </tbody>
        </table>
        <?php else: ?>
          <p class="text-muted mb-0">No subscribers yet.</p>
        <?php endif ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card shadow-sm p-4">
        <h5 class="mb-3">Quick Actions</h5>
        <div class="d-grid gap-2">
          <a href="programs.php?add=1" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Program
          </a>
          <a href="subscribers.php?export=1" class="btn btn-success">
            <i class="bi bi-download me-2"></i>Export Subscribers CSV
          </a>
          <a href="../../programs.html" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-eye me-2"></i>View Programs Page
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body></html>
