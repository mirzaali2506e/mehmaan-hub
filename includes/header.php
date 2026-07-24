<?php
start_session_safe();
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$authPages = ['login', 'register'];
$hideNav = in_array($currentPage, $authPages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? SITE_NAME) ?><?= isset($pageTitle) ? ' - ' . SITE_NAME : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
</head>
<body>
<?php if (!$hideNav): ?>
<header class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
            <i class="fas fa-home"></i>
            <span>Mehmaan<span class="logo-accent">Hub</span></span>
        </a>
        <nav class="nav-links" id="navLinks">
            <a href="<?= SITE_URL ?>/index.php" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a>
            <a href="<?= SITE_URL ?>/properties.php" class="<?= $currentPage === 'properties' ? 'active' : '' ?>">Properties</a>
            <a href="<?= SITE_URL ?>/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a>
            <a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a>
            <?php if ($user): ?>
                <?php if ($user['role'] === 'owner' || $user['role'] === 'admin'): ?>
                    <a href="<?= SITE_URL ?>/owner-dashboard.php" class="<?= $currentPage === 'owner-dashboard' ? 'active' : '' ?>">Dashboard</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'tenant'): ?>
                    <a href="<?= SITE_URL ?>/dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">My Dashboard</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/wishlist.php" class="<?= $currentPage === 'wishlist' ? 'active' : '' ?>"><i class="fas fa-heart"></i></a>
                <?php
                $notifCount = get_unread_notification_count($user['id']);
                $unreadNotifs = $notifCount > 0 ? get_unread_notifications($user['id']) : [];
                ?>
                <div class="nav-notification<?= $notifCount > 0 ? ' has-notif' : '' ?>" id="navNotification">
                    <button class="nav-notification-btn" onclick="toggleNotifPanel(event)" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($notifCount > 0): ?><span class="notif-badge"><?= $notifCount ?></span><?php endif; ?>
                    </button>
                    <div class="notif-panel" id="notifPanel">
                        <?php if (empty($unreadNotifs)): ?>
                            <div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No new notifications</p></div>
                        <?php else: ?>
                            <div class="notif-header">Notifications (<?= $notifCount ?>)</div>
                            <?php foreach ($unreadNotifs as $n): ?>
                                <a href="<?= SITE_URL ?><?= e($n['link'] ?? '/dashboard.php') ?>" class="notif-item" onclick="markNotifsRead()">
                                    <div class="notif-icon"><i class="fas fa-bell"></i></div>
                                    <div>
                                        <strong><?= e($n['title']) ?></strong>
                                        <p><?= e($n['message']) ?></p>
                                        <small><?= time_ago($n['created_at']) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="nav-user">
                    <a href="<?= SITE_URL ?>/profile.php" class="nav-user-link">
                        <div class="nav-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <span><?= e($user['name']) ?></span>
                    </a>
                </div>
                <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-sm">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </nav>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
<?php endif; ?>

<?php if ($flash = flash('success')): ?>
<div class="alert alert-success" id="flashAlert"><i class="fas fa-check-circle"></i> <?= e($flash) ?></div>
<?php endif; ?>
<?php if ($flash = flash('error')): ?>
<div class="alert alert-error" id="flashAlert"><i class="fas fa-exclamation-circle"></i> <?= e($flash) ?></div>
<?php endif; ?>

<main class="main-content">
