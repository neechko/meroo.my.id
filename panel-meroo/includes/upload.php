<?php
/**
 * Validasi & pindahkan file gambar yang diupload ke folder /uploads.
 * Return array ['ok' => bool, 'path' => string|null, 'error' => string|null]
 */
function handle_image_upload(array $file): array {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'path' => null, 'error' => null]; // tidak ada file diupload, bukan error
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Upload gagal (kode error: ' . $file['error'] . ').'];
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'path' => null, 'error' => 'Ukuran file maksimal 5MB.'];
    }

    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        return ['ok' => false, 'path' => null, 'error' => 'File yang diupload bukan gambar yang valid.'];
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = $imgInfo['mime'];
    if (!isset($allowedMime[$mime])) {
        return ['ok' => false, 'path' => null, 'error' => 'Format gambar harus JPG, PNG, GIF, atau WEBP.'];
    }

    $ext = $allowedMime[$mime];
    $filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir = __DIR__ . '/../../uploads/';
    $destPath = $destDir . $filename;

    if (!is_dir($destDir)) {
        @mkdir($destDir, 0755, true);
    }
    if (!is_writable($destDir)) {
        return ['ok' => false, 'path' => null, 'error' => 'Folder /uploads tidak bisa ditulis. Cek permission folder (biasanya 755) lewat File Manager.'];
    }

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Gagal memindahkan file ke folder uploads.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . $filename, 'error' => null];
}
