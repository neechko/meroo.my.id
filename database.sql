-- ============================================================
--  meroo.my.id — database schema & seed data
--  Cara pakai: buka phpMyAdmin -> pilih database kamu -> tab "Import"
--  -> pilih file ini -> klik "Go" / "Kirim".
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- Tabel admin (akun login panel admin)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun default: username = admin | password = admin123
-- !! WAJIB ganti password ini lewat halaman "Ganti Password" begitu berhasil login pertama kali !!
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('admin', '$2y$12$aCOAq7rk1h83Y54MDkfV4ejiQ/Hq3cT3vHrMo.K2MaEGJRfOz2fNm');

-- ---------------------------------------------
-- Tabel galeri (gambar karakter / koleksi)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `tag` VARCHAR(50) NOT NULL DEFAULT 'lainnya',
  `quote` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `featured_subtitle` VARCHAR(150) DEFAULT NULL,
  `featured_desc` TEXT DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`),
  KEY `is_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3 gambar "karakter favorit" (section Tercinta)
INSERT INTO `gallery` (`image_path`,`name`,`tag`,`quote`,`is_featured`,`featured_subtitle`,`featured_desc`,`sort_order`) VALUES
('assets/castorice-hero.jpg','Castorice','castorice',NULL,1,'✦ yang paling tercinta','"i only wish that Death... may protect us." Tenang tapi sedih, lembut tapi nyimpen luka — entah kenapa dia yang paling nempel di ingatan dari semua waktu main HSR (walau sekarang udah pensi).',1),
('assets/odette-hero.jpg','Odette','odette',NULL,1,'favorit Genshin',NULL,2),
('assets/castorice-side.jpg','Castorice','castorice',NULL,1,'sisi lain',NULL,3);

-- 12 gambar galeri / gacha (5 castorice + 7 odette)
INSERT INTO `gallery` (`image_path`,`name`,`tag`,`quote`,`sort_order`) VALUES
('assets/castorice1.jpg','Castorice','castorice','Splash art yang paling sering gw jadiin wallpaper.',10),
('assets/castorice2.jpg','Castorice','castorice','Detail kupu-kupunya nggak pernah gw skip pas liat.',11),
('assets/castorice3.jpg','Castorice','castorice','Pose ini yang bikin gw stop scroll lama-lama.',12),
('assets/castorice4.jpg','Castorice','castorice','Salah satu crop favorit dari cutscene-nya.',13),
('assets/castorice5.jpg','Castorice','castorice','Ekspresi tenangnya tuh entah kenapa nenangin juga.',14),
('assets/odette1.jpg','Odette','odette','Splash art rilis yang langsung gw save duluan.',20),
('assets/odette2.jpg','Odette','odette','Warna birunya related banget sama vibe elemen dia.',21),
('assets/odette3.jpg','Odette','odette','Detail aksesoris kepalanya nggak ada obat.',22),
('assets/odette4.jpg','Odette','odette','Close-up ini yang bikin gw makin yakin sama pilihan.',23),
('assets/odette5.jpg','Odette','odette','Salah satu momen dari cutscene yang paling gw suka.',24),
('assets/odette6.jpg','Odette','odette','Splash art versi lain, tetep top tier menurut gw.',25),
('assets/odette7.jpg','Odette','odette','Splash art rilis yang langsung gw save duluan.',26);

-- ---------------------------------------------
-- Tabel site_settings (teks & gambar yang bisa diedit dari panel admin)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_eyebrow', 'selamat datang di sudut kecil gw'),
('hero_tagline', 'Sering muter-muter di Amphoreus sama Teyvat, sambil masih ada kode yang belum kelar.'),
('hero_sub', 'IT enthusiast, wibu pemula, sesekali masih maen Genshin Impact (HSR-nya udah pensi). Ini sudut kecil gw, isinya satu — bonus satu lagi — karakter yang paling nempel di hati.'),
('hero_bg_image', 'assets/castorice-hero.jpg'),
('about_greeting', 'Halo, gw meroo__'),
('about_text', 'Hari-hari gw kebagi antara baca error log sama baca alur cerita game/anime. Entah kenapa gw suka hal yang rapi — code architecture atau desain karakter yang detailnya kebangetan, dua-duanya bikin betah.\n\nAnime sama game jadi pelarian gw pas capek. Kadang maraton semalaman, kadang cuma liatin loading screen Genshin sambil mikirin bug yang belum kelar.'),
('about_image', 'assets/castorice-chibi.jpg'),
('footer_quote', '"Nggak semua yang disayang harus ramai, cukup dikunjungi tiap malam pulang kerja."'),
('footer_credit', 'dibuat dengan sedikit ngengat & banyak Castorice — meroo__, 2026'),
('social_instagram', 'https://www.instagram.com/kyuu_tsu?igsh=bGl6a2duOWRrNXFp'),
('social_github', 'https://github.com/neechko'),
('social_mal', 'https://myanimelist.net/profile/meroo__'),
('social_spotify', ''),
('social_steam', ''),
('social_x', '');

SET FOREIGN_KEY_CHECKS = 1;
