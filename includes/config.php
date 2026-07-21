<?php
// Load Composer autoloader (for phpdotenv + PHPMailer)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load .env file if Dotenv is available
$envPath = __DIR__ . '/..';
if (class_exists('Dotenv\Dotenv')) {
    try {
        Dotenv\Dotenv::createImmutable($envPath)->load();
    } catch (\Throwable $e) {
        // .env missing or unreadable — fall back to defaults below
    }
}

// Helper to read env with fallback
function env($key, $default = null) {
    $val = getenv($key);
    return ($val === false || $val === '') ? $default : $val;
}

// Database
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'mehmaan_hub'));

// Site
define('SITE_NAME', env('SITE_NAME', 'Mehmaan Hub'));
define('SITE_URL', env('SITE_URL', 'http://localhost/mhman-hb/php-project'));

// Uploads
define('UPLOAD_DIR', __DIR__ . '/../uploads/properties/');
define('UPLOAD_URL', SITE_URL . '/uploads/properties/');

// SMTP mail settings (for password reset OTP emails)
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', env('MAIL_PORT', 587));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', ''));
