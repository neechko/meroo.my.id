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
    'social_instagram', 'social_instagram_handle',
    'social_github', 'social_github_handle',
    'social_mal', 'social_mal_handle',
    'social_spotify', 'social_spotify_handle',
    'social_steam', 'social_steam_handle',
    'social_x', 'social_x_handle',
];

$stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');

foreach ($textFields as $field) {
    $value = trim($_POST[$field] ?? '');
    $stmt->execute([$field, $value]);
}

// optional image upload (hero background & about portrait)
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