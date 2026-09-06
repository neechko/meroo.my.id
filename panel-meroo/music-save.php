<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?tab=music');
    exit;
}
csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$title = trim($_POST['title'] ?? '');
$artist = trim($_POST['artist'] ?? '') ?: null;
$sortOrder = (int)($_POST['sort_order'] ?? 0);

if ($title === '') {
    header('Location: dashboard.php?tab=music&msg=error&detail=' . urlencode('Title cannot be empty.'));
    exit;
}

$upload = handle_audio_upload($_FILES['audio'] ?? []);
if ($upload['error']) {
    header('Location: dashboard.php?tab=music&msg=error&detail=' . urlencode($upload['error']));
    exit;
}

if ($id) {
    // update
    $stmt = $pdo->prepare('SELECT * FROM music_tracks WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: dashboard.php?tab=music&msg=error&detail=' . urlencode('Track not found.'));
        exit;
    }
    $filePath = $upload['ok'] ? $upload['path'] : $existing['file_path'];

    $stmt = $pdo->prepare('UPDATE music_tracks SET title=?, artist=?, file_path=?, sort_order=? WHERE id=?');
    $stmt->execute([$title, $artist, $filePath, $sortOrder, $id]);

    // delete the old file if it was replaced and the old file lived in the uploads folder
    if ($upload['ok'] && strpos($existing['file_path'], 'uploads/') === 0) {
        @unlink(__DIR__ . '/../' . $existing['file_path']);
    }
} else {
    // new track, an audio file is required
    if (!$upload['ok']) {
        header('Location: dashboard.php?tab=music&msg=error&detail=' . urlencode('An audio file is required for a new track.'));
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO music_tracks (title, artist, file_path, sort_order) VALUES (?,?,?,?)');
    $stmt->execute([$title, $artist, $upload['path'], $sortOrder]);
}

header('Location: dashboard.php?tab=music&msg=music_saved');
exit;
