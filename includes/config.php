<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mehmaan_hub');
define('SITE_NAME', 'Mehmaan Hub');
define('SITE_URL', 'http://localhost/mhman-hb/php-project');

// SMTP email configuration (Gmail)
// To enable email notifications, fill in your Gmail address and an App Password.
// Generate an App Password at: https://myaccount.google.com/apppasswords
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');       // e.g. 'your-email@gmail.com'
define('MAIL_PASSWORD', '');       // Your Gmail App Password (16 chars, no spaces)
define('MAIL_ENCRYPTION', 'tls');  // 'tls' for port 587, 'ssl' for port 465
define('MAIL_FROM_ADDRESS', '');   // Sender email, usually same as MAIL_USERNAME
define('UPLOAD_DIR', __DIR__ . '/../uploads/properties/');
define('UPLOAD_URL', SITE_URL . '/uploads/properties/');
