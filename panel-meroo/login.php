<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Simple session-based rate limiting
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['login_last_try'] = $_SESSION['login_last_try'] ?? 0;

    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_last_try']) < 60) {
        $error = 'Too many failed attempts. Try again in 1 minute.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['login_attempts'] = 0;
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['login_last_try'] = time();
            $error = 'Incorrect username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In — Admin</title>
<meta name="robots" content="noindex, nofollow">
<?php require __DIR__ . '/includes/admin-style.php'; ?>
</head>
<body>
  <div class="login-box">
    <h1>Admin Panel</h1>
    <p class="sub">meroo__ portfolio</p>
    <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required autofocus>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      <button class="btn" type="submit" style="width:100%; margin-top:20px;">Log In</button>
    </form>
  </div>
</body>
</html>