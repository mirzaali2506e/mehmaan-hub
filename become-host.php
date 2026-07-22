<?php
require_once __DIR__ . '/includes/functions.php';

$user = require_login();

if ($user['role'] === 'owner' || $user['role'] === 'admin') {
    redirect('/owner-dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
    $role = 'owner';
    $stmt->bind_param('si', $role, $user['id']);
    if ($stmt->execute()) {
        $_SESSION['user_role'] = 'owner';
        flash('success', 'You are now a host! Start listing your properties.');
        redirect('/owner-dashboard.php');
    } else {
        flash('error', 'Something went wrong. Please try again.');
    }
}

$pageTitle = 'Become a Host';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="form-container" style="max-width: 540px;">
        <div style="text-align:center; margin-bottom: var(--sp-6);">
            <div style="width:72px; height:72px; border-radius:50%; background: var(--primary-50); display:inline-flex; align-items:center; justify-content:center; margin-bottom: var(--sp-4);">
                <i class="fas fa-building" style="font-size:1.8rem; color: var(--primary);"></i>
            </div>
            <h1>Become a Host</h1>
            <p style="color: var(--text-secondary); margin-top: var(--sp-2);">
                Upgrade your account to list properties and start earning.
            </p>
        </div>

        <div style="background: var(--muted); border-radius: var(--radius); padding: var(--sp-5); margin-bottom: var(--sp-5);">
            <h3 style="font-size: .95rem; margin-bottom: var(--sp-3); color: var(--text);">What you get as a host:</h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--sp-2);">
                <li style="display:flex; align-items:center; gap:10px; font-size:.9rem; color: var(--text-soft);">
                    <i class="fas fa-check" style="color: var(--success);"></i> List unlimited properties
                </li>
                <li style="display:flex; align-items:center; gap:10px; font-size:.9rem; color: var(--text-soft);">
                    <i class="fas fa-check" style="color: var(--success);"></i> Manage bookings and tenants
                </li>
                <li style="display:flex; align-items:center; gap:10px; font-size:.9rem; color: var(--text-soft);">
                    <i class="fas fa-check" style="color: var(--success);"></i> Access the owner dashboard
                </li>
            </ul>
        </div>

        <form method="POST" action="">
            <p style="font-size: .85rem; color: var(--text-secondary); margin-bottom: var(--sp-4); text-align:center;">
                Your account will be upgraded from <strong>Tenant</strong> to <strong>Owner</strong>. You can still browse and book properties as before.
            </p>
            <button type="submit" class="btn btn-primary btn-block">Upgrade to Host</button>
            <a href="<?= SITE_URL ?>/dashboard.php" style="display:block; text-align:center; margin-top: var(--sp-3); color: var(--text-secondary); font-size: .9rem;">Maybe later</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
