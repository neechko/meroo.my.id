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
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New password confirmation does not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $upd->execute([$newHash, $admin['id']]);
        $success = 'Password changed successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — Admin</title>
<meta name="robots" content="noindex, nofollow">
<?php require __DIR__ . '/includes/admin-style.php'; ?>
</head>
<body>
<div class="topbar">
  <div class="brand">mer<span>oo__</span> · admin</div>
  <nav>
    <a href="dashboard.php?tab=gallery">Gallery</a>
    <a href="dashboard.php?tab=poke">Poke Messages</a>
    <a href="dashboard.php?tab=music">Music</a>
    <a href="dashboard.php?tab=settings">Site Settings</a>
    <a href="change-password.php" class="active">Change Password</a>
    <a href="../index.php" target="_blank">View Site ↗</a>
    <a href="logout.php">Log Out</a>
  </nav>
</div>
<div class="wrap" style="max-width:480px;">
  <div class="card">
    <h2>Change Password</h2>
    <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="msg ok"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label>Current Password</label>
      <input type="password" name="current_password" required>
      <label>New Password</label>
      <input type="password" name="new_password" required>
      <label>Confirm New Password</label>
      <input type="password" name="confirm_password" required>
      <button class="btn" type="submit" style="margin-top:20px;">Save New Password</button>
    </form>
  </div>
</div>
</body>
</html>