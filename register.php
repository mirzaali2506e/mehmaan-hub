<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('/index.php');
}

$prefillRole = ($_GET['role'] ?? '') === 'owner' ? 'owner' : 'tenant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'tenant';

    if (!$name || !$email || !$password) {
        flash('error', 'Please fill in all required fields.');
    } elseif ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } elseif (!in_array($role, ['tenant', 'owner'])) {
        flash('error', 'Invalid account type.');
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            flash('error', 'Email already registered. Please login.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $name, $email, $hashed, $phone, $role);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;
                flash('success', 'Account created successfully! Welcome to Mehmaan Hub.');
                if ($role === 'owner') {
                    redirect('/owner-dashboard.php');
                } else {
                    redirect('/dashboard.php');
                }
            } else {
                flash('error', 'Something went wrong. Please try again.');
            }
        }
    }
}

$pageTitle = 'Register';
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
                <h1>Create Account</h1>
                <p>Join Mehmaan Hub to find or list properties</p>
            </div>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Your full name" autocomplete="name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" placeholder="03XX-XXXXXXX" autocomplete="tel">
                    </div>
                </div>
                <div class="form-group">
                    <label>Account Type</label>
                    <div class="role-selector">
                        <label class="role-option">
                            <input type="radio" name="role" value="tenant" <?= $prefillRole === 'tenant' ? 'checked' : '' ?>>
                            <div class="role-card">
                                <i class="fas fa-user"></i>
                                <span>Tenant</span>
                                <small>I want to rent</small>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="owner" <?= $prefillRole === 'owner' ? 'checked' : '' ?>>
                            <div class="role-card">
                                <i class="fas fa-building"></i>
                                <span>Owner</span>
                                <small>I want to list</small>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Min 6 characters" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            <p class="auth-footer">Already have an account? <a href="<?= SITE_URL ?>/login.php">Login here</a></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
