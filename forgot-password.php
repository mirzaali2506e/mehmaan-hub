<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('/index.php');
}

$step = 'request';
$identifier = '';
$maskedContact = '';
$userId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'send';

    if ($action === 'send') {
        $identifier = trim($_POST['identifier'] ?? '');
        $user = find_user_by_email_or_phone($identifier);

        if (!$user) {
            flash('error', 'No account found with that email or phone number.');
        } else {
            $otp = create_password_reset($user['id']);
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_identifier'] = $identifier;

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

            if (strpos($identifier, '@') !== false) {
                $demoChannel = 'email ' . $maskedEmail;
            } else {
                $demoChannel = 'phone ' . $maskedPhone;
            }
            flash('success', 'OTP sent to your ' . $demoChannel . '. (Demo OTP: ' . $otp . ')');
        }
    } elseif ($action === 'resend') {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $identifier = $_SESSION['reset_identifier'] ?? '';
        if (!$userId) {
            redirect('/forgot-password.php');
        }
        $otp = create_password_reset($userId);
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
        flash('success', 'New OTP sent. (Demo OTP: ' . $otp . ')');
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
                    <input type="hidden" name="action" value="resend">
                    <button type="submit" class="btn btn-outline btn-block">Resend OTP</button>
                </form>
            <?php endif; ?>

            <p class="auth-footer"><a href="<?= SITE_URL ?>/login.php" class="auth-back-link"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
