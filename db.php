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
    die('Database connection failed. Make sure config.php is filled in correctly and the database has been imported via phpMyAdmin. Details: ' . htmlspecialchars($e->getMessage()));
}

/**
 * Get all site_settings as a key => value array, with sensible defaults as fallback.
 */
function get_settings(PDO $pdo): array {
    $defaults = [
        'hero_eyebrow'     => 'welcome to my little corner',
        'hero_tagline'     => 'Often wandering around Amphoreus and Teyvat, while there is still code left unfinished.',
        'hero_sub'         => "IT enthusiast, beginner otaku, still playing Genshin Impact once in a while (retired from HSR by now). This is my little corner, home to one — plus a bonus one — character who stuck closest to my heart.",
        'hero_bg_image'    => 'assets/castorice-hero.jpg',
        'about_greeting'   => "Hi, I'm meroo__",
        'about_text'       => "My days are split between reading error logs and following game or anime storylines. For some reason I love things that are well put together — clean code architecture or a character design with insane amounts of detail, both keep me hooked.\n\nAnime and games are my escape when I'm worn out. Sometimes it's an all-night marathon, sometimes it's just staring at a Genshin loading screen while thinking about a bug I haven't fixed yet.",
        'about_image'      => 'assets/castorice-chibi.jpg',
        'footer_quote'     => '"Not everything you love has to be loud — it just needs a visit every night after work."',
        'footer_credit'    => 'made with a few moths & a lot of Castorice — meroo__, 2026',
        'social_instagram' => 'https://www.instagram.com/kyuu_tsu?igsh=bGl6a2duOWRrNXFp',
        'social_instagram_handle' => '@kyuu_tsu',
        'social_github'    => 'https://github.com/neechko',
        'social_github_handle' => 'neechko',
        'social_mal'       => 'https://myanimelist.net/profile/meroo__',
        'social_mal_handle' => 'meroo__',
        'social_spotify'   => '',
        'social_spotify_handle' => '',
        'social_steam'     => '',
        'social_steam_handle' => '',
        'social_x'         => '',
        'social_x_handle'  => '',
    ];
    try {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        foreach ($rows as $row) {
            $defaults[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // table not created yet / not imported, fall back to defaults
    }
    return $defaults;
}

/**
 * Get all poke messages, grouped by tag.
 * There is always a 'default' key for tags without their own custom messages.
 */
function get_poke_messages(PDO $pdo): array {
    $grouped = [];
    try {
        $rows = $pdo->query('SELECT * FROM poke_messages ORDER BY tag ASC, sort_order ASC, id ASC')->fetchAll();
        foreach ($rows as $row) {
            $grouped[$row['tag']][] = $row['message'];
        }
    } catch (Exception $e) {
        // table not created yet / not imported
    }
    if (empty($grouped['default'])) {
        $grouped['default'] = [
            "hm? ...what is it?",
            "don't do that too often, or I might get used to it.",
            "heh. not bad, you may continue.",
            "...don't make me laugh in front of people.",
            "one more time is fine too, I guess.",
            "you're actually kind of funny, you know that?",
        ];
    }
    return $grouped;
}

/**
 * Get the background music playlist, ordered for playback.
 * Falls back to the 3 bundled tracks in /musik if the table is empty/missing.
 */
function get_music_tracks(PDO $pdo): array {
    try {
        $rows = $pdo->query('SELECT * FROM music_tracks ORDER BY sort_order ASC, id ASC')->fetchAll();
        if ($rows) return $rows;
    } catch (Exception $e) {
        // table not created yet / not imported
    }
    return [
        ['title' => 'Track 1', 'artist' => null, 'file_path' => 'musik/lagu1.m4a'],
        ['title' => 'Track 2', 'artist' => null, 'file_path' => 'musik/lagu2.mp3'],
        ['title' => 'Track 3', 'artist' => null, 'file_path' => 'musik/lagu3.mp3'],
    ];
}