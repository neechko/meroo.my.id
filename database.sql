-- ============================================================
--  meroo.my.id — database schema & seed data
--  How to use: open phpMyAdmin -> select your database -> "Import" tab
--  -> choose this file -> click "Go".
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------
-- Admin users table (admin panel login accounts)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default account: username = admin | password = admin123
-- !! You MUST change this password from the "Change Password" page after your first successful login !!
INSERT INTO `admin_users` (`username`, `password_hash`) VALUES
('admin', '$2y$12$aCOAq7rk1h83Y54MDkfV4ejiQ/Hq3cT3vHrMo.K2MaEGJRfOz2fNm');

-- ---------------------------------------------
-- Gallery table (character images / collection)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `tag` VARCHAR(50) NOT NULL DEFAULT 'other',
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

-- 3 "favorite character" images (Favorites section) — can be extended up to 10 total
INSERT INTO `gallery` (`image_path`,`name`,`tag`,`quote`,`is_featured`,`featured_subtitle`,`featured_desc`,`sort_order`) VALUES
('assets/castorice-hero.jpg','Castorice','castorice',NULL,1,'✦ the most beloved','"i only wish that Death... may protect us." Calm but sorrowful, gentle yet quietly wounded — somehow she is the one who stuck with me the most out of everyone I met while playing HSR (even though I have since retired from it).',1),
('assets/odette-hero.jpg','Odette','odette',NULL,1,'Genshin favorite',NULL,2),
('assets/castorice-side.jpg','Castorice','castorice',NULL,1,'another side',NULL,3);

-- 12 gallery / gacha images (5 castorice + 7 odette)
INSERT INTO `gallery` (`image_path`,`name`,`tag`,`quote`,`sort_order`) VALUES
('assets/castorice1.jpg','Castorice','castorice','The splash art I use as my wallpaper more than any other.',10),
('assets/castorice2.jpg','Castorice','castorice','I never skip past the butterfly details on this one.',11),
('assets/castorice3.jpg','Castorice','castorice','This pose is the one that always stops my scroll.',12),
('assets/castorice4.jpg','Castorice','castorice','One of my favorite crops from her cutscene.',13),
('assets/castorice5.jpg','Castorice','castorice','That calm expression is somehow calming to look at too.',14),
('assets/odette1.jpg','Odette','odette','The release splash art I saved before anything else.',20),
('assets/odette2.jpg','Odette','odette',"The blue tones match her element's vibe perfectly.",21),
('assets/odette3.jpg','Odette','odette','The detail on her headpiece is unmatched.',22),
('assets/odette4.jpg','Odette','odette','This close-up is what sealed my choice.',23),
('assets/odette5.jpg','Odette','odette','One of my favorite moments from her cutscene.',24),
('assets/odette6.jpg','Odette','odette','A different splash art, still top tier in my book.',25),
('assets/odette7.jpg','Odette','odette','The release splash art I saved before anything else.',26);

-- ---------------------------------------------
-- site_settings table (text & images editable from the admin panel)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('hero_eyebrow', 'welcome to my little corner'),
('hero_tagline', 'Often wandering around Amphoreus and Teyvat, while there is still code left unfinished.'),
('hero_sub', 'IT enthusiast, beginner otaku, still playing Genshin Impact once in a while (retired from HSR by now). This is my little corner, home to one — plus a bonus one — character who stuck closest to my heart.'),
('hero_bg_image', 'assets/castorice-hero.jpg'),
('about_greeting', 'Hi, I''m meroo__'),
('about_text', 'My days are split between reading error logs and following game or anime storylines. For some reason I love things that are well put together — clean code architecture or a character design with insane amounts of detail, both keep me hooked.\n\nAnime and games are my escape when I''m worn out. Sometimes it''s an all-night marathon, sometimes it''s just staring at a Genshin loading screen while thinking about a bug I haven''t fixed yet.'),
('about_image', 'assets/castorice-chibi.jpg'),
('footer_quote', '"Not everything you love has to be loud — it just needs a visit every night after work."'),
('footer_credit', 'made with a few moths & a lot of Castorice — meroo__, 2026'),
('social_instagram', 'https://www.instagram.com/kyuu_tsu?igsh=bGl6a2duOWRrNXFp'),
('social_instagram_handle', '@kyuu_tsu'),
('social_github', 'https://github.com/neechko'),
('social_github_handle', 'neechko'),
('social_mal', 'https://myanimelist.net/profile/meroo__'),
('social_mal_handle', 'meroo__'),
('social_spotify', ''),
('social_spotify_handle', ''),
('social_steam', ''),
('social_steam_handle', ''),
('social_x', ''),
('social_x_handle', '');

-- ---------------------------------------------
-- poke_messages table (reaction lines shown when a character image is clicked/poked)
-- the 'default' tag is used as a fallback for any tag without its own messages
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `poke_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(50) NOT NULL DEFAULT 'default',
  `message` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `poke_messages` (`tag`, `message`, `sort_order`) VALUES
('castorice', 'hm? ...what is it?', 1),
('castorice', "don't touch me too often, or I might get used to it.", 2),
('castorice', 'hm. not bad, you may continue.', 3),
('castorice', "...don't make me laugh in front of people.", 4),
('castorice', 'one more time is fine too, I suppose.', 5),
('castorice', "you're... actually kind of funny, aren't you.", 6),
('odette', 'eh?! that startled me, you know!', 1),
('odette', 'hihi, that tickles~ but go ahead, again.', 2),
('odette', 'did you need something, hm?', 3),
('odette', "don't do that too often, or I'll get spoiled.", 4),
('odette', 'you always do this every time we meet.', 5),
('odette', "okay, I'll forgive you. but just this once~", 6),
('default', 'hm? ...what is it?', 1),
('default', "don't do that too often, or I might get used to it.", 2),
('default', 'heh. not bad, you may continue.', 3),
('default', "...don't make me laugh in front of people.", 4),
('default', 'one more time is fine too, I guess.', 5),
('default', "you're actually kind of funny, you know that?", 6);

-- ---------------------------------------------
-- music_tracks table (background music playlist, editable from the admin panel)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `music_tracks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `artist` VARCHAR(150) DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- the 3 tracks that were already bundled in /musik — rename them freely from the admin panel
INSERT INTO `music_tracks` (`title`, `artist`, `file_path`, `sort_order`) VALUES
('Track 1', NULL, 'musik/lagu1.m4a', 1),
('Track 2', NULL, 'musik/lagu2.mp3', 2),
('Track 3', NULL, 'musik/lagu3.mp3', 3);

SET FOREIGN_KEY_CHECKS = 1;