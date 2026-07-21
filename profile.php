<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (!$name) {
        flash('error', 'Name cannot be empty.');
    } else {
        $stmt = db()->prepare('UPDATE users SET name=?, phone=? WHERE id=?');
        $stmt->bind_param('ssi', $name, $phone, $user['id']);
        $stmt->execute();

        if ($newPassword) {
            $pwStmt = db()->prepare('SELECT password FROM users WHERE id = ?');
            $pwStmt->bind_param('i', $user['id']);
            $pwStmt->execute();
            $pwRow = $pwStmt->get_result()->fetch_assoc();
            if (!password_verify($currentPassword, $pwRow['password'])) {
                flash('error', 'Current password is incorrect.');
            } elseif (strlen($newPassword) < 6) {
                flash('error', 'New password must be at least 6 characters.');
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $pwStmt = db()->prepare('UPDATE users SET password=? WHERE id=?');
                $pwStmt->bind_param('si', $hashed, $user['id']);
                $pwStmt->execute();
                flash('success', 'Profile updated successfully!');
            }
        } else {
            flash('success', 'Profile updated successfully!');
        }
    }
    redirect('/profile.php');
}

$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
$user = current_user();
?>

<div class="form-page">
    <div class="container">
        <div class="form-container narrow">
            <div class="form-header">
                <div class="profile-avatar-lg"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <h1><?= e($user['name']) ?></h1>
                <p><?= ucfirst(e($user['role'])) ?> Account</p>
            </div>

            <form method="POST" class="profile-form">
                <?= csrf_field() ?>
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email (cannot change)</label>
                        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Account Type</label>
                        <input type="text" id="role" value="<?= ucfirst(e($user['role'])) ?>" disabled>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Leave blank to keep current">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Min 6 characters">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
