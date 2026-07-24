<?php
require_once __DIR__ . '/config.php';

// Load Composer's autoloader (run `composer install` in project root first)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

function send_otp_email($toEmail, $toName, $otp) {
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log('PHPMailer not installed. Run `composer install` in the project root.');
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // SMTP settings from config.php
        $mail->isSMTP();
        $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('MAIL_USERNAME') ? MAIL_USERNAME : '';
        $mail->Password   = defined('MAIL_PASSWORD') ? MAIL_PASSWORD : '';
        $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl')
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('MAIL_PORT') ? (int)MAIL_PORT : 587;

        // Recipients
        $fromAddress = defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS ? MAIL_FROM_ADDRESS : (defined('MAIL_USERNAME') ? MAIL_USERNAME : 'no-reply@mehmaanhub.com');
        $mail->setFrom($fromAddress, SITE_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromAddress, SITE_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP - ' . SITE_NAME;
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;padding:20px;'>
                <h2 style='color:#2563eb;'>Password Reset</h2>
                <p>Hi " . htmlspecialchars($toName) . ",</p>
                <p>We received a request to reset your password. Use the OTP below to continue:</p>
                <div style='font-size:28px;font-weight:bold;letter-spacing:6px;color:#2563eb;background:#eff6ff;padding:15px;text-align:center;border-radius:8px;margin:15px 0;'>" . htmlspecialchars($otp) . "</div>
                <p>This OTP expires in 10 minutes.</p>
                <p>If you didn't request this, you can safely ignore this email.</p>
                <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
                <p style='color:#888;font-size:12px;'>&copy; " . date('Y') . " " . SITE_NAME . "</p>
            </div>";
        $mail->AltBody = "Your password reset OTP is: {$otp}\nThis OTP expires in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

function send_notification_email($toEmail, $toName, $subject, $bodyHtml) {
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log('send_notification_email: PHPMailer not installed. Run `composer install` in the project root.');
        // Fallback to PHP mail() if PHPMailer is unavailable
        $plainBody = strip_tags($bodyHtml);
        $headers = "MIME-Version: 1.0\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n" .
                   "From: " . SITE_NAME . " <no-reply@mehmaanhub.com>\r\n";
        $wrappedBody = "<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;'>" .
            "<h2 style='color:#2563eb;'>" . htmlspecialchars(SITE_NAME) . "</h2>" .
            "<p>Hi " . htmlspecialchars($toName) . ",</p>" .
            $bodyHtml .
            "<hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>" .
            "<p style='color:#888;font-size:12px;'>&copy; " . date('Y') . " " . SITE_NAME . "</p></div>";
        return @mail($toEmail, $subject, $wrappedBody, $headers);
    }

    // If SMTP credentials are not configured, use PHP's built-in mail() as fallback
    if (!defined('MAIL_USERNAME') || !MAIL_USERNAME || !defined('MAIL_PASSWORD') || !MAIL_PASSWORD) {
        error_log('send_notification_email: SMTP credentials not set in config.php. Falling back to PHP mail().');
        $headers = "MIME-Version: 1.0\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n" .
                   "From: " . SITE_NAME . " <no-reply@mehmaanhub.com>\r\n";
        $wrappedBody = "<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;'>" .
            "<h2 style='color:#2563eb;'>" . htmlspecialchars(SITE_NAME) . "</h2>" .
            "<p>Hi " . htmlspecialchars($toName) . ",</p>" .
            $bodyHtml .
            "<hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>" .
            "<p style='color:#888;font-size:12px;'>&copy; " . date('Y') . " " . SITE_NAME . "</p></div>";
        return @mail($toEmail, $subject, $wrappedBody, $headers);
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = defined('MAIL_HOST') ? MAIL_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = (defined('MAIL_ENCRYPTION') && MAIL_ENCRYPTION === 'ssl')
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('MAIL_PORT') ? (int)MAIL_PORT : 587;
        $fromAddress = defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS ? MAIL_FROM_ADDRESS : MAIL_USERNAME;
        $mail->setFrom($fromAddress, SITE_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromAddress, SITE_NAME);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;'>" .
            "<h2 style='color:#2563eb;'>" . htmlspecialchars(SITE_NAME) . "</h2>" .
            "<p>Hi " . htmlspecialchars($toName) . ",</p>" .
            $bodyHtml .
            "<hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>" .
            "<p style='color:#888;font-size:12px;'>&copy; " . date('Y') . " " . SITE_NAME . "</p>" .
            "</div>";
        $mail->AltBody = strip_tags($bodyHtml);
        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('send_notification_email Mailer Error: ' . $mail->ErrorInfo);
        // Fallback to PHP mail() on SMTP failure
        $headers = "MIME-Version: 1.0\r\n" .
                   "Content-Type: text/html; charset=UTF-8\r\n" .
                   "From: " . SITE_NAME . " <no-reply@mehmaanhub.com>\r\n";
        $wrappedBody = "<div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;'>" .
            "<h2 style='color:#2563eb;'>" . htmlspecialchars(SITE_NAME) . "</h2>" .
            "<p>Hi " . htmlspecialchars($toName) . ",</p>" .
            $bodyHtml .
            "<hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>" .
            "<p style='color:#888;font-size:12px;'>&copy; " . date('Y') . " " . SITE_NAME . "</p></div>";
        return @mail($toEmail, $subject, $wrappedBody, $headers);
    }
}
