<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/mail.php';

security_headers();

if (current_user()) {
    redirect('/index.php');
}

$step = 'request';
$identifier = '';
$maskedContact = '';
$userId = null;
$resendCooldown = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'send';

    if ($action === 'send') {
        // Rate limiting: 3 requests per 15 minutes
        if (!rate_limit('forgot_password', 3, 900)) {
            flash('error', 'Too many reset requests. Please try again in 15 minutes.');
        } else {
            $identifier = trim($_POST['identifier'] ?? '');
            $user = find_user_by_email_or_phone($identifier);

            if (!$user) {
                // Generic message — no user enumeration
                flash('success', 'If an account exists for that email or phone, a reset code has been sent.');
                $step = 'sent';
            } else {
                $otp = create_password_reset($user['id']);
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_identifier'] = $identifier;
                $_SESSION['reset_last_sent'] = time();

                $email = $user['email'];
                $atPos = strpos($email, '@');
                $maskedEmail = $atPos > 1
                    ? substr($email, 0, 1) . str_repeat('*', min(4, $atPos - 1)) . substr($email, $atPos)
                    : $email;
                $phone = $user['phone'] ?? '';
                $maskedPhone = strlen($phone) > 4
                    ? str_repeat('*', strlen($phone) - 4) . substr($phone, -4)
                    : $phone;

                $maskedContact = trim($maskedEmail . ' / ' . $maskedPhone, ' /');
                $step = 'sent';
                $userId = $user['id'];

                if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                    $mailSent = send_otp_email($user['email'], $user['name'], $otp);
                    if ($mailSent) {
                        flash('success', 'A reset code has been sent to your email ' . $maskedEmail . '. Check your inbox (and spam folder).');
                    } else {
                        // SMTP not configured — do NOT expose OTP in production
                        flash('error', 'Could not send reset email. Please contact support or try again later.');
                        error_log('OTP email failed for user ' . $user['id'] . '. SMTP may not be configured.');
                    }
                } else {
                    flash('success', 'A reset code has been sent to your phone ' . $maskedPhone . '.');
                    // SMS not configured — OTP is NOT displayed in UI
                    error_log('OTP SMS not sent for user ' . $user['id'] . '. SMS not configured.');
                }
            }
        }
    } elseif ($action === 'resend') {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $identifier = $_SESSION['reset_identifier'] ?? '';
        $lastSent = $_SESSION['reset_last_sent'] ?? 0;
        $cooldown = 60; // 60 second resend cooldown
        $elapsed = time() - $lastSent;

        if (!$userId) {
            redirect('/forgot-password.php');
        }

        if ($elapsed < $cooldown) {
            $resendCooldown = $cooldown - $elapsed;
            flash('error', 'Please wait ' . $resendCooldown . ' seconds before requesting a new code.');
            $step = 'sent';
        } elseif (!rate_limit('forgot_password', 3, 900)) {
            flash('error', 'Too many reset requests. Please try again in 15 minutes.');
            $step = 'sent';
        } else {
            $otp = create_password_reset($userId);
            $_SESSION['reset_last_sent'] = time();
            $step = 'sent';
            $user = find_user_by_email_or_phone($identifier);

            $email = $user['email'] ?? '';
            $atPos = strpos($email, '@');
            $maskedEmail = $atPos > 1
                ? substr($email, 0, 1) . str_repeat('*', min(4, $atPos - 1)) . substr($email, $atPos)
                : $email;
            $phone = $user['phone'] ?? '';
            $maskedPhone = strlen($phone) > 4
                ? str_repeat('*', strlen($phone) - 4) . substr($phone, -4)
                : $phone;
            $maskedContact = trim($maskedEmail . ' / ' . $maskedPhone, ' /');

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $mailSent = send_otp_email($user['email'], $user['name'], $otp);
                if ($mailSent) {
                    flash('success', 'A new reset code has been sent to your email ' . $maskedEmail . '.');
                } else {
                    flash('error', 'Could not send reset email. Please try again later.');
                }
            } else {
                flash('success', 'A new reset code has been sent to your phone ' . $maskedPhone . '.');
            }
        }
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= SITE_URL ?>/index.php" class="auth-logo">
                    <i class="fas fa-home"></i>
                    <span>Mehmaan<span class="logo-accent">Hub</span></span>
                </a>
                <h1>Forgot Password</h1>
                <p>Reset your account password</p>
            </div>

            <?php if ($step === 'request'): ?>
                <form method="POST" class="auth-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="send">
                    <div class="form-group">
                        <label for="identifier">Email or Phone Number</label>
                        <div class="input-wrap">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="identifier" name="identifier" placeholder="you@example.com or 03XX-XXXXXXX" autocomplete="username" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send OTP</button>
                </form>
            <?php else: ?>
                <div class="auth-otp-notice">
                    <i class="fas fa-shield-alt"></i>
                    <span>We've sent a 6-digit verification code to <strong><?= e($maskedContact) ?></strong>. Enter it below to continue.</span>
                </div>
                <form method="POST" action="<?= SITE_URL ?>/reset-password.php" class="auth-form">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="otp">Enter OTP</label>
                        <div class="input-wrap">
                            <i class="fas fa-key"></i>
                            <input type="text" id="otp" name="otp" class="otp-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="------" autocomplete="one-time-code" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-check"></i> Verify OTP</button>
                </form>
                <form method="POST" class="auth-form" style="margin-top:8px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="resend">
                    <button type="submit" class="btn btn-outline btn-block" <?= $resendCooldown > 0 ? 'disabled' : '' ?>>Resend OTP<?= $resendCooldown > 0 ? ' (' . $resendCooldown . 's)' : '' ?></button>
                </form>
            <?php endif; ?>

            <p class="auth-footer"><a href="<?= SITE_URL ?>/login.php" class="auth-back-link"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
