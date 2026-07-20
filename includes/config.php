<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mehmaan_hub');
define('SITE_NAME', 'Mehmaan Hub');
define('SITE_URL', 'http://localhost/mhman-hb/php-project');
define('UPLOAD_DIR', __DIR__ . '/../uploads/properties/');
define('UPLOAD_URL', SITE_URL . '/uploads/properties/');

// SMTP mail settings (for password reset OTP emails)
// Configure these with your SMTP provider (e.g. Gmail, SendGrid, Mailgun)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');      // your SMTP email address
define('MAIL_PASSWORD', '');      // your SMTP app password
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('MAIL_FROM_ADDRESS', '');  // sender email (usually same as MAIL_USERNAME)
