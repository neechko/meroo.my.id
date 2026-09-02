<?php
/**
 * ============================================================
 *  KONFIGURASI DATABASE
 *  Edit 4 baris di bawah sesuai data database di cPanel/hosting
 *  kamu (biasanya ada di menu "MySQL Databases" atau sejenisnya).
 * ============================================================
 */
define('DB_HOST', 'localhost');           // biasanya 'localhost'
define('DB_NAME', 'namadatabase_kamu');   // nama database, misal: meroo_db
define('DB_USER', 'userdatabase_kamu');   // username database
define('DB_PASS', 'password_database_kamu'); // password database

// Jangan diubah kecuali tahu maksudnya
define('DB_CHARSET', 'utf8mb4');
define('SITE_ROOT_URL', ''); // isi kalau situs ada di subfolder, misal '/portofolio'. Kosongkan jika di root domain.

date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
