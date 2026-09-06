<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?tab=poke');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM poke_messages WHERE id = ?');
    $stmt->execute([$id]);
}

header('Location: dashboard.php?tab=poke&msg=poke_deleted');
exit;
