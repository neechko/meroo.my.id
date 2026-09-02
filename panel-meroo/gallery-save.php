<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : null;
$name = trim($_POST['name'] ?? '');
$tag = strtolower(trim($_POST['tag'] ?? 'lainnya'));
$tag = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', $tag)) ?: 'lainnya';
$quote = trim($_POST['quote'] ?? '') ?: null;
$isFeatured = isset($_POST['is_featured']) ? 1 : 0;
$featuredSubtitle = trim($_POST['featured_subtitle'] ?? '') ?: null;
$featuredDesc = trim($_POST['featured_desc'] ?? '') ?: null;
$sortOrder = (int)($_POST['sort_order'] ?? 0);

if ($name === '') {
    header('Location: dashboard.php?msg=error&detail=' . urlencode('Nama tidak boleh kosong.'));
    exit;
}

$upload = handle_image_upload($_FILES['image'] ?? []);
if ($upload['error']) {
    header('Location: dashboard.php?msg=error&detail=' . urlencode($upload['error']));
    exit;
}

if ($id) {
    // update
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: dashboard.php?msg=error&detail=' . urlencode('Item tidak ditemukan.'));
        exit;
    }
    $imagePath = $upload['ok'] ? $upload['path'] : $existing['image_path'];

    $stmt = $pdo->prepare('UPDATE gallery SET image_path=?, name=?, tag=?, quote=?, is_featured=?, featured_subtitle=?, featured_desc=?, sort_order=? WHERE id=?');
    $stmt->execute([$imagePath, $name, $tag, $quote, $isFeatured, $featuredSubtitle, $featuredDesc, $sortOrder, $id]);

    // hapus file lama kalau diganti gambar baru dan file lama ada di folder uploads (bukan asset bawaan)
    if ($upload['ok'] && strpos($existing['image_path'], 'uploads/') === 0) {
        @unlink(__DIR__ . '/../' . $existing['image_path']);
    }
} else {
    // insert baru, wajib ada gambar
    if (!$upload['ok']) {
        header('Location: dashboard.php?msg=error&detail=' . urlencode('Gambar wajib diupload untuk item baru.'));
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO gallery (image_path, name, tag, quote, is_featured, featured_subtitle, featured_desc, sort_order) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$upload['path'], $name, $tag, $quote, $isFeatured, $featuredSubtitle, $featuredDesc, $sortOrder]);
}

header('Location: dashboard.php?tab=gallery&msg=saved');
exit;
