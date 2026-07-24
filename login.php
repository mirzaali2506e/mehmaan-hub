<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        flash('error', 'Please fill in all fields.');
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            flash('error', 'No account found with that email.');
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();

                $remember = isset($_POST['remember']);
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expires = date('Y-m-d H:i:s', time() + 30 * 86400);
                    $stmt = db()->prepare('INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
                    $stmt->bind_param('iss', $user['id'], $tokenHash, $expires);
                    $stmt->execute();
                    setcookie('remember_me', $user['id'] . ':' . $token, [
                        'expires' => time() + 30 * 86400,
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                }
                if ($user['role'] === 'owner' || $user['role'] === 'admin') {
                    redirect('/owner-dashboard.php');
                } else {
                    redirect('/dashboard.php');
                }
            } else {
                flash('error', 'Incorrect password.');
            }
        }
    }
}

$pageTitle = 'Login';
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
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue</p>
            </div>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    </div>
                </div>
                <div class="auth-form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1"> Keep me logged in
                    </label>
                    <a href="<?= SITE_URL ?>/forgot-password.php" class="auth-forgot-link">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            <p class="auth-footer">Don't have an account? <a href="<?= SITE_URL ?>/register.php">Register here</a></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
