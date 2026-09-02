<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $del = $pdo->prepare('DELETE FROM gallery WHERE id = ?');
        $del->execute([$id]);
        // hapus file fisik hanya jika berada di folder uploads (jangan hapus file di /assets bawaan tema)
        if (strpos($item['image_path'], 'uploads/') === 0) {
            @unlink(__DIR__ . '/../' . $item['image_path']);
        }
    }
}

header('Location: dashboard.php?tab=gallery&msg=deleted');
exit;
