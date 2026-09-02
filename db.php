<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Koneksi database gagal. Pastikan config.php sudah diisi benar dan database sudah diimport lewat phpMyAdmin. Detail: ' . htmlspecialchars($e->getMessage()));
}

/**
 * Ambil semua site_settings sebagai array key => value, dengan fallback default.
 */
function get_settings(PDO $pdo): array {
    $defaults = [
        'hero_eyebrow'     => 'selamat datang di sudut kecil gw',
        'hero_tagline'     => 'Sering muter-muter di Amphoreus sama Teyvat, sambil masih ada kode yang belum kelar.',
        'hero_sub'         => 'IT enthusiast, wibu pemula, sesekali masih maen Genshin Impact (HSR-nya udah pensi). Ini sudut kecil gw, isinya satu — bonus satu lagi — karakter yang paling nempel di hati.',
        'hero_bg_image'    => 'assets/castorice-hero.jpg',
        'about_greeting'   => 'Halo, gw meroo__',
        'about_text'       => "Hari-hari gw kebagi antara baca error log sama baca alur cerita game/anime. Entah kenapa gw suka hal yang rapi — code architecture atau desain karakter yang detailnya kebangetan, dua-duanya bikin betah.\n\nAnime sama game jadi pelarian gw pas capek. Kadang maraton semalaman, kadang cuma liatin loading screen Genshin sambil mikirin bug yang belum kelar.",
        'about_image'      => 'assets/castorice-chibi.jpg',
        'footer_quote'     => '"Nggak semua yang disayang harus ramai, cukup dikunjungi tiap malam pulang kerja."',
        'footer_credit'    => 'dibuat dengan sedikit ngengat & banyak Castorice — meroo__, 2026',
        'social_instagram' => 'https://www.instagram.com/kyuu_tsu?igsh=bGl6a2duOWRrNXFp',
        'social_github'    => 'https://github.com/neechko',
        'social_mal'       => 'https://myanimelist.net/profile/meroo__',
        'social_spotify'   => '',
        'social_steam'     => '',
        'social_x'         => '',
    ];
    try {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // tabel belum ada / belum diimport, pakai default saja
    }
    return $defaults;
}
