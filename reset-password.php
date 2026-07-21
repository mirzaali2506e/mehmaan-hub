<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

security_headers();

if (current_user()) {
    redirect('/index.php');
}

$userId = $_SESSION['reset_user_id'] ?? null;
if (!$userId) {
    flash('error', 'Please start the password reset process again.');
    redirect('/forgot-password.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    // Rate limiting: 5 OTP attempts per 15 minutes
    if (!rate_limit('otp_verify', 5, 900)) {
        $error = 'Too many attempts. Please try again in 15 minutes.';
    } else {
        $otp = trim($_POST['otp'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$otp || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $error = 'Please enter the 6-digit OTP.';
        } elseif (!$newPassword || !$confirmPassword) {
            $error = 'Please enter and confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!verify_password_reset_otp($userId, $otp)) {
            $error = 'Invalid or expired OTP. Please try again.';
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->bind_param('si', $hashed, $userId);
            $stmt->execute();

            db()->query("DELETE FROM password_resets WHERE user_id = " . (int)$userId);
            log_activity($userId, 'password_reset');
            unset($_SESSION['reset_user_id'], $_SESSION['reset_identifier'], $_SESSION['reset_last_sent']);

            flash('success', 'Password changed successfully! You can now login with your new password.');
            redirect('/login.php');
        }
    }
}

$pageTitle = 'Reset Password';
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
                <h1>Reset Password</h1>
                <p>Verify the OTP and set a new password</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="position:static;transform:none;max-width:none;"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
            <?php endif; ?>

            <div class="auth-otp-notice">
                <i class="fas fa-shield-alt"></i>
                <span>Enter the 6-digit code we sent you, then choose a new password.</span>
            </div>

            <form method="POST" class="auth-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="otp">Enter OTP <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-key"></i>
                        <input type="text" id="otp" name="otp" class="otp-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="------" autocomplete="one-time-code" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="new_password" name="new_password" placeholder="Min 6 characters" autocomplete="new-password" required>
                        <button type="button" class="pwd-toggle" data-target="new_password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" autocomplete="new-password" required>
                        <button type="button" class="pwd-toggle" data-target="confirm_password" aria-label="Show password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-check"></i> Reset Password</button>
            </form>

            <p class="auth-footer"><a href="<?= SITE_URL ?>/forgot-password.php" class="auth-back-link"><i class="fas fa-arrow-left"></i> Back</a></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
