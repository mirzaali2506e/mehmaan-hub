<?php
require_once __DIR__ . '/config.php';

// Load Composer's autoloader (run `composer install` in project root first)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Send a password reset OTP email via SMTP using PHPMailer.
 *
 * @param string $toEmail Recipient email address
 * @param string $toName  Recipient name
 * @param string $otp      The OTP code to send
 * @return bool True on success, false on failure
 */
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
        $fromAddress = defined('MAIL_FROM_ADDRESS') && MAIL_FROM_ADDRESS
            ? MAIL_FROM_ADDRESS
            : (defined('MAIL_USERNAME') ? MAIL_USERNAME : 'no-reply@mehmaanhub.com');
        $mail->setFrom($fromAddress, SITE_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromAddress, SITE_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset Code - ' . SITE_NAME;
        $mail->Body = otp_email_template($toName, $otp);
        $mail->AltBody = "Hi {$toName},\n\nYour password reset code is: {$otp}\nThis code expires in 10 minutes.\nIf you didn't request this, you can safely ignore this email.\n\n" . SITE_NAME;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Professional branded HTML email template for OTP.
 */
function otp_email_template($name, $otp) {
    $year = date('Y');
    $siteUrl = SITE_URL;
    $name = htmlspecialchars($name);
    $otp = htmlspecialchars($otp);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Reset - Mehmaan Hub</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F8FAFC;padding:24px 0;">
    <tr>
      <td align="center">
        <table width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:18px;box-shadow:0 4px 24px rgba(0,0,0,0.06);overflow:hidden;">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#2563EB 0%,#14B8A6 100%);padding:32px 40px;text-align:center;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center">
                    <span style="font-size:24px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">
                      <span style="font-size:28px;margin-right:8px;">&#9774;</span>Mehmaan Hub
                    </span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:40px;">
              <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#0F172A;letter-spacing:-0.3px;">Reset your password</h1>
              <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
                Hi {$name},<br>
                We received a request to reset your password. Use the verification code below to continue. This code is valid for <strong style="color:#2563EB;">10 minutes</strong>.
              </p>

              <!-- OTP Box -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                <tr>
                  <td style="background-color:#EFF6FF;border:1px dashed #2563EB;border-radius:14px;padding:28px 20px;text-align:center;">
                    <span style="font-size:36px;font-weight:700;letter-spacing:10px;color:#2563EB;font-family:'Courier New',monospace;">{$otp}</span>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#64748B;">
                Enter this code on the reset page to choose a new password. If you didn't request a password reset, you can safely ignore this email &mdash; your account is still secure.
              </p>

              <!-- Divider -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin:28px 0;">
                <tr>
                  <td style="border-top:1px solid #E2E8F0;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;line-height:1.5;color:#94A3B8;">
                This is an automated message from Mehmaan Hub. Please do not reply to this email.<br>
                &copy; {$year} Mehmaan Hub. All rights reserved.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#F8FAFC;padding:20px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:#94A3B8;">
                Mehmaan Hub &mdash; Your trusted rental partner in Pakistan
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
