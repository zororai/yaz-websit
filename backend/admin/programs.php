<?php
require_once '../config.php';
requireAdmin();
$pdo = getDB();

$msg  = '';
$edit = null;

// ── Handle POST actions ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['add', 'edit'])) {
        $title      = trim($_POST['title']       ?? '');
        $desc       = trim($_POST['description'] ?? '');
        $year       = (int)($_POST['year']       ?? date('Y'));
        $detailUrl  = trim($_POST['detail_url']  ?? '');
        $status     = in_array($_POST['status'] ?? '', ['active','draft']) ? $_POST['status'] : 'active';
        $imagePath  = trim($_POST['image_path']  ?? '');

        // Image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
            if (in_array($_FILES['image']['type'], $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
                $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $fname = 'prog_' . uniqid() . '.' . $ext;
                $dir   = __DIR__ . '/../../assets/img/programs/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname);
                $imagePath = 'assets/img/programs/' . $fname;
            } else {
                $msg = '<div class="alert alert-danger">Image must be JPG/PNG/WEBP and under 5 MB.</div>';
            }
        }

        if (!$title) {
            $msg = '<div class="alert alert-danger">Title is required.</div>';
        } elseif (!$msg) {
            if ($action === 'add') {
                $pdo->prepare(
                    'INSERT INTO programs (title, description, year, image_path, detail_url, status)
                     VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([$title, $desc, $year, $imagePath, $detailUrl, $status]);
                $msg = '<div class="alert alert-success">Program added successfully.</div>';
            } else {
                $id  = (int)($_POST['id'] ?? 0);
                $sql = 'UPDATE programs SET title=?, description=?, year=?, detail_url=?, status=?';
                $p   = [$title, $desc, $year, $detailUrl, $status];
                if ($imagePath) { $sql .= ', image_path=?'; $p[] = $imagePath; }
                $sql .= ', updated_at=NOW() WHERE id=?';
                $p[]  = $id;
                $pdo->prepare($sql)->execute($p);
                $msg = '<div class="alert alert-success">Program updated successfully.</div>';
            }
        }

    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare('DELETE FROM programs WHERE id=?')->execute([$id]);
            $msg = '<div class="alert alert-warning">Program deleted.</div>';
        }
    }
}

// Load edit record
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM programs WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$showForm = isset($_GET['add']) || $edit;
$programs = $pdo->query('SELECT * FROM programs ORDER BY year DESC, id DESC')->fetchAll();
$years    = range(2019, (int)date('Y') + 1);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Programs | YA Admin</title>
<link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<style>
  .sidebar { width:220px; min-height:100vh; background:#4c00b0; position:fixed; top:0; left:0; z-index:100; }
  .sidebar .brand { padding:16px 20px; border-bottom:1px solid rgba(255,255,255,.2); }
  .sidebar a { color:rgba(255,255,255,.8); text-decoration:none; display:block; padding:11px 20px; transition:.2s; }
  .sidebar a:hover, .sidebar a.active { color:#fff; background:rgba(255,255,255,.18); }
  .sidebar .logout { position:absolute; bottom:0; width:100%; border-top:1px solid rgba(255,255,255,.2); }
  .main { margin-left:220px; padding:30px; background:#f4f6fb; min-height:100vh; }
  .prog-thumb { width:64px; height:46px; object-fit:cover; border-radius:5px; }
</style>
</head>
<body>
<div class="sidebar">
  <div class="brand">
    <img src="../../assets/img/blog/ya-logo.png" alt="YA" style="height:38px"><br>
    <small class="text-white-50 d-block mt-1">Admin Panel</small>
  </div>
  <a href="index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
  <a href="programs.php" class="active"><i class="bi bi-grid-3x3-gap me-2"></i>Programs</a>
  <a href="subscribers.php"><i class="bi bi-people me-2"></i>Subscribers</a>
  <a href="../../index.html" target="_blank"><i class="bi bi-globe me-2"></i>View Site</a>
  <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
</div>

<div class="main">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><?= $edit ? 'Edit Program' : 'Programs' ?></h4>
    <?php if (!$showForm): ?>
      <a href="programs.php?add=1" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Program
      </a>
    <?php endif ?>
  </div>

  <?= $msg ?>

  <?php if ($showForm): ?>
  <!-- ── Add / Edit Form ─────────────────────────────────────────── -->
  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3"><?= $edit ? 'Editing: ' . htmlspecialchars($edit['title']) : 'New Program' ?></h5>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
      <?php if ($edit): ?>
        <input type="hidden" name="id"         value="<?= (int)$edit['id'] ?>">
        <input type="hidden" name="image_path" value="<?= htmlspecialchars($edit['image_path'] ?? '') ?>">
      <?php endif ?>

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold">Program Title *</label>
          <input type="text" name="title" class="form-control"
                 value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="4"
          ><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Year</label>
          <select name="year" class="form-select">
            <?php foreach ($years as $y): ?>
              <option value="<?= $y ?>"
                <?= ($edit['year'] ?? (int)date('Y')) == $y ? 'selected' : '' ?>>
                <?= $y ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= ($edit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (visible)</option>
            <option value="draft"  <?= ($edit['status'] ?? 'active') === 'draft'  ? 'selected' : '' ?>>Draft (hidden)</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Detail Page Link</label>
          <input type="text" name="detail_url" class="form-control"
                 value="<?= htmlspecialchars($edit['detail_url'] ?? '') ?>"
                 placeholder="e.g. all programs.html#name">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Program Image</label>
          <?php if ($edit && $edit['image_path']): ?>
            <div class="mb-2">
              <img src="../../<?= htmlspecialchars($edit['image_path']) ?>"
                   style="height:60px;border-radius:6px" alt="current image">
              <small class="text-muted ms-2">Upload a new image to replace this one.</small>
            </div>
          <?php endif ?>
          <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
          <small class="text-muted">JPG, PNG or WEBP · max 5 MB</small>
        </div>

        <div class="col-12 d-flex gap-2 pt-1">
          <button type="submit" class="btn btn-primary px-4">
            <?= $edit ? 'Save Changes' : 'Add Program' ?>
          </button>
          <a href="programs.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </form>
  </div>
  <?php endif ?>

  <?php if (!$edit): ?>
  <!-- ── Programs Table ──────────────────────────────────────────── -->
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#f8f9fa">
          <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Year</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($programs as $p): ?>
          <tr>
            <td>
              <img src="../../<?= htmlspecialchars($p['image_path']) ?>"
                   class="prog-thumb"
                   onerror="this.src='../../assets/img/blog/ya-logo.png'"
                   alt="">
            </td>
            <td><?= htmlspecialchars($p['title']) ?></td>
            <td><?= (int)$p['year'] ?></td>
            <td>
              <span class="badge <?= $p['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                <?= htmlspecialchars($p['status']) ?>
              </span>
            </td>
            <td class="text-end">
              <a href="programs.php?edit=<?= (int)$p['id'] ?>"
                 class="btn btn-sm btn-outline-primary me-1">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="POST" style="display:inline"
                    onsubmit="return confirm('Delete this program? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id"     value="<?= (int)$p['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach ?>
        <?php if (!$programs): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No programs yet. Add one above.</td></tr>
        <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif ?>
</div>
<script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body></html>
