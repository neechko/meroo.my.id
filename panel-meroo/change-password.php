<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($current, $admin['password_hash'])) {
        $error = 'Password saat ini salah.';
    } elseif (strlen($new) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($new !== $confirm) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $upd->execute([$newHash, $admin['id']]);
        $success = 'Password berhasil diubah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Password — Admin</title>
<meta name="robots" content="noindex, nofollow">
<?php require __DIR__ . '/includes/admin-style.php'; ?>
</head>
<body>
<div class="topbar">
  <div class="brand">mer<span>oo__</span> · admin</div>
  <nav>
    <a href="dashboard.php?tab=gallery">Galeri</a>
    <a href="dashboard.php?tab=settings">Pengaturan Situs</a>
    <a href="change-password.php" class="active">Ganti Password</a>
    <a href="logout.php">Keluar</a>
  </nav>
</div>
<div class="wrap" style="max-width:480px;">
  <div class="card">
    <h2>Ganti Password</h2>
    <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="msg ok"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label>Password Saat Ini</label>
      <input type="password" name="current_password" required>
      <label>Password Baru</label>
      <input type="password" name="new_password" required>
      <label>Konfirmasi Password Baru</label>
      <input type="password" name="confirm_password" required>
      <button class="btn" type="submit" style="margin-top:20px;">Simpan Password Baru</button>
    </form>
  </div>
</div>
</body>
</html>
