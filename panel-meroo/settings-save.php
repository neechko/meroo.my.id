<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
csrf_verify();

$textFields = [
    'hero_eyebrow', 'hero_tagline', 'hero_sub',
    'about_greeting', 'about_text',
    'footer_quote', 'footer_credit',
    'social_instagram', 'social_github', 'social_mal', 'social_spotify', 'social_steam', 'social_x',
];

$stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');

foreach ($textFields as $field) {
    $value = trim($_POST[$field] ?? '');
    $stmt->execute([$field, $value]);
}

// upload gambar opsional (hero background & about portrait)
foreach (['hero_bg_image', 'about_image'] as $imgField) {
    if (!empty($_FILES[$imgField]['name'])) {
        $upload = handle_image_upload($_FILES[$imgField]);
        if ($upload['ok']) {
            $stmt->execute([$imgField, $upload['path']]);
        } elseif ($upload['error']) {
            header('Location: dashboard.php?tab=settings&msg=error&detail=' . urlencode($upload['error']));
            exit;
        }
    }
}

header('Location: dashboard.php?tab=settings&msg=settings');
exit;
