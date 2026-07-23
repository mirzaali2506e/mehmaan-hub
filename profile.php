<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

$bookingCount = 0;
$wishlistCount = 0;
$stmt = db()->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE tenant_id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$bookingCount = (int)$stmt->get_result()->fetch_assoc()['cnt'];
$stmt = db()->prepare("SELECT COUNT(*) as cnt FROM wishlist WHERE user_id = ?");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$wishlistCount = (int)$stmt->get_result()->fetch_assoc()['cnt'];

$memberSince = date('M Y', strtotime($user['created_at'] ?? 'now'));
?>

<div class="profile-page">
    <div class="container">
        <!-- Profile Banner -->
        <div class="profile-banner">
            <div class="profile-banner-bg"></div>
            <div class="profile-banner-content">
                <div class="profile-avatar-xl"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <div class="profile-banner-info">
                    <h1><?= e($user['name']) ?></h1>
                    <p><i class="fas fa-envelope"></i> <?= e($user['email']) ?></p>
                    <div class="profile-badges">
                        <span class="profile-badge"><i class="fas fa-tag"></i> <?= ucfirst(e($user['role'])) ?></span>
                        <span class="profile-badge"><i class="fas fa-calendar-alt"></i> Member since <?= $memberSince ?></span>
                    </div>
                </div>
                <button type="button" class="btn btn-primary profile-edit-btn" onclick="toggleEditProfile()" id="editToggleBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="profile-stats">
            <div class="profile-stat-card">
                <div class="profile-stat-icon stat-blue"><i class="fas fa-calendar-check"></i></div>
                <div class="profile-stat-body">
                    <strong><?= $bookingCount ?></strong>
                    <span>Bookings</span>
                </div>
            </div>
            <div class="profile-stat-card">
                <div class="profile-stat-icon stat-red"><i class="fas fa-heart"></i></div>
                <div class="profile-stat-body">
                    <strong><?= $wishlistCount ?></strong>
                    <span>Wishlist</span>
                </div>
            </div>
            <div class="profile-stat-card">
                <div class="profile-stat-icon stat-green"><i class="fas fa-shield-alt"></i></div>
                <div class="profile-stat-body">
                    <strong>Verified</strong>
                    <span>Account</span>
                </div>
            </div>
        </div>

        <!-- Read-only profile view -->
        <div id="profileView" class="profile-view">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3><i class="fas fa-id-card"></i> Personal Information</h3>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-row">
                        <div class="profile-info-icon"><i class="fas fa-user"></i></div>
                        <div class="profile-info-text">
                            <span class="profile-info-label">Full Name</span>
                            <span class="profile-info-value"><?= e($user['name']) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="profile-info-text">
                            <span class="profile-info-label">Email Address</span>
                            <span class="profile-info-value"><?= e($user['email']) ?></span>
                        </div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-icon"><i class="fas fa-phone"></i></div>
                        <div class="profile-info-text">
                            <span class="profile-info-label">Phone Number</span>
                            <span class="profile-info-value"><?= e($user['phone'] ?: 'Not provided') ?></span>
                        </div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-icon"><i class="fas fa-user-tag"></i></div>
                        <div class="profile-info-text">
                            <span class="profile-info-label">Account Type</span>
                            <span class="profile-info-value"><?= ucfirst(e($user['role'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Editable form (hidden by default) -->
        <form method="POST" class="profile-form" id="profileForm" style="display:none;">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h3><i class="fas fa-id-card"></i> Edit Personal Information</h3>
                </div>
                <div class="profile-card-body">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email (cannot change)</label>
                        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>" placeholder="Enter your phone number">
                    </div>
                    <div class="form-group">
                        <label for="role"><i class="fas fa-user-tag"></i> Account Type</label>
                        <input type="text" id="role" value="<?= ucfirst(e($user['role'])) ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-card-header">
                    <h3><i class="fas fa-lock"></i> Change Password</h3>
                </div>
                <div class="profile-card-body">
                    <div class="form-group">
                        <label for="current_password"><i class="fas fa-key"></i> Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Min 6 characters">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" class="btn btn-outline" onclick="toggleEditProfile()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEditProfile() {
    var view = document.getElementById('profileView');
    var form = document.getElementById('profileForm');
    var editBtn = document.getElementById('editToggleBtn');
    if (form.style.display === 'none') {
        view.style.display = 'none';
        form.style.display = 'block';
        editBtn.style.display = 'none';
    } else {
        view.style.display = 'block';
        form.style.display = 'none';
        editBtn.style.display = 'inline-flex';
    }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
