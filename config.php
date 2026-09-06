<?php
/**
 * ============================================================
 *  DATABASE CONFIGURATION
 *  Edit the 4 lines below to match your database info from
 *  cPanel/hosting (usually under a "MySQL Databases" menu).
 * ============================================================
 */
define('DB_HOST', 'localhost');           // usually 'localhost'
define('DB_NAME', 'your_database_name');  // database name, e.g. meroo_db
define('DB_USER', 'your_database_user');  // database username
define('DB_PASS', 'your_database_password'); // database password

// Don't change these unless you know what you're doing
define('DB_CHARSET', 'utf8mb4');
define('SITE_ROOT_URL', ''); // set this if the site lives in a subfolder, e.g. '/portfolio'. Leave empty if it's at the domain root.

date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);