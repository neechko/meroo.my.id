<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?tab=poke');
    exit;
}
csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$tag = strtolower(trim($_POST['tag'] ?? 'default'));
$tag = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $tag)) ?: 'default';
$message = trim($_POST['message'] ?? '');
$sortOrder = (int)($_POST['sort_order'] ?? 0);

if ($message === '') {
    header('Location: dashboard.php?tab=poke&msg=error&detail=' . urlencode('Message cannot be empty.'));
    exit;
}
if (mb_strlen($message) > 255) {
    header('Location: dashboard.php?tab=poke&msg=error&detail=' . urlencode('Message is too long (255 characters max).'));
    exit;
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE poke_messages SET tag=?, message=?, sort_order=? WHERE id=?');
    $stmt->execute([$tag, $message, $sortOrder, $id]);
} else {
    $stmt = $pdo->prepare('INSERT INTO poke_messages (tag, message, sort_order) VALUES (?,?,?)');
    $stmt->execute([$tag, $message, $sortOrder]);
}

header('Location: dashboard.php?tab=poke&msg=poke_saved');
exit;
