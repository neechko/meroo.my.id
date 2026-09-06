<?php
/**
 * Validate & move an uploaded image file into the /uploads folder.
 * Returns ['ok' => bool, 'path' => string|null, 'error' => string|null]
 */
function handle_image_upload(array $file): array {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'path' => null, 'error' => null]; // no file uploaded, not an error
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Upload failed (error code: ' . $file['error'] . ').'];
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'path' => null, 'error' => 'File size must be 5MB or smaller.'];
    }

    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        return ['ok' => false, 'path' => null, 'error' => 'The uploaded file is not a valid image.'];
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = $imgInfo['mime'];
    if (!isset($allowedMime[$mime])) {
        return ['ok' => false, 'path' => null, 'error' => 'Image format must be JPG, PNG, GIF, or WEBP.'];
    }

    $ext = $allowedMime[$mime];
    $filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir = __DIR__ . '/../../uploads/';
    $destPath = $destDir . $filename;

    if (!is_dir($destDir)) {
        @mkdir($destDir, 0755, true);
    }
    if (!is_writable($destDir)) {
        return ['ok' => false, 'path' => null, 'error' => 'The /uploads folder is not writable. Check its permissions (usually 755) via File Manager.'];
    }

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Failed to move the file into the uploads folder.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . $filename, 'error' => null];
}

/**
 * Validate & move an uploaded audio file into the /uploads folder.
 * Returns ['ok' => bool, 'path' => string|null, 'error' => string|null]
 */
function handle_audio_upload(array $file): array {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'path' => null, 'error' => null]; // no file uploaded, not an error
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Upload failed (error code: ' . $file['error'] . ').'];
    }

    $maxSize = 15 * 1024 * 1024; // 15MB
    if ($file['size'] > $maxSize) {
        return ['ok' => false, 'path' => null, 'error' => 'File size must be 15MB or smaller.'];
    }

    $allowedExt = [
        'mp3' => true, 'm4a' => true, 'wav' => true, 'ogg' => true, 'aac' => true,
    ];
    $origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowedExt[$origExt])) {
        return ['ok' => false, 'path' => null, 'error' => 'Audio format must be MP3, M4A, WAV, OGG, or AAC.'];
    }

    // Best-effort MIME sniff. Some servers report generic types for audio, so we only
    // hard-block obviously wrong types instead of relying on this alone.
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime && strpos($mime, 'audio') === false && strpos($mime, 'video/mp4') === false && strpos($mime, 'application/octet-stream') === false) {
            return ['ok' => false, 'path' => null, 'error' => 'The uploaded file does not look like a valid audio file.'];
        }
    }

    $filename = 'audio_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $origExt;
    $destDir = __DIR__ . '/../../uploads/';
    $destPath = $destDir . $filename;

    if (!is_dir($destDir)) {
        @mkdir($destDir, 0755, true);
    }
    if (!is_writable($destDir)) {
        return ['ok' => false, 'path' => null, 'error' => 'The /uploads folder is not writable. Check its permissions (usually 755) via File Manager.'];
    }

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Failed to move the file into the uploads folder.'];
    }

    return ['ok' => true, 'path' => 'uploads/' . $filename, 'error' => null];
}