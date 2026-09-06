<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?tab=music');
    exit;
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM music_tracks WHERE id = ?');
    $stmt->execute([$id]);
    $track = $stmt->fetch();
    if ($track) {
        $del = $pdo->prepare('DELETE FROM music_tracks WHERE id = ?');
        $del->execute([$id]);
        // only delete the physical file if it lives in the uploads folder (never delete bundled theme files in /musik)
        if (strpos($track['file_path'], 'uploads/') === 0) {
            @unlink(__DIR__ . '/../' . $track['file_path']);
        }
    }
}

header('Location: dashboard.php?tab=music&msg=music_deleted');
exit;
