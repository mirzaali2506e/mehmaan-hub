<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';
$user = require_role('tenant');

$myBookings = get_user_bookings($user['id']);
$wishlist = get_wishlist($user['id']);

$stats = [
    'bookings' => count($myBookings),
    'pending' => 0,
    'confirmed' => 0,
    'wishlist' => count($wishlist),
];
foreach ($myBookings as $b) {
    if ($b['status'] === 'pending') $stats['pending']++;
    if ($b['status'] === 'confirmed') $stats['confirmed']++;
}

$pageTitle = 'My Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>My Dashboard</h1>
                <p>Welcome back, <?= e($user['name']) ?>!</p>
            </div>
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-primary"><i class="fas fa-search"></i> Browse Properties</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-blue"><i class="fas fa-calendar"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['bookings'] ?></strong>
                    <span>Total Bookings</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['pending'] ?></strong>
                    <span>Pending</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['confirmed'] ?></strong>
                    <span>Confirmed</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-red"><i class="fas fa-heart"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['wishlist'] ?></strong>
                    <span>Wishlist</span>
                </div>
            </div>
        </div>

        <div class="dashboard-tabs">
            <button class="tab-btn active" data-tab="bookings"><i class="fas fa-calendar"></i> My Bookings</button>
            <button class="tab-btn" data-tab="wishlist"><i class="fas fa-heart"></i> Wishlist</button>
        </div>

        <div class="tab-content active" id="tab-bookings">
            <?php if (empty($myBookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <h3>No bookings yet</h3>
                    <p>Browse properties and book your favorite ones.</p>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn btn-primary">Browse Properties</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myBookings as $b): ?>
                            <tr>
                                <td>
                                    <div class="table-property">
                                        <?php if (!empty($b['primary_image'])): ?>
                                            <img src="<?= e(image_url($b['primary_image'])) ?>" alt="">
                                        <?php else: ?>
                                            <div class="table-img-placeholder"><i class="fas fa-home"></i></div>
                                        <?php endif; ?>
                                        <a href="<?= SITE_URL ?>/property-details.php?id=<?= $b['property_id'] ?>"><strong><?= e($b['property_title']) ?></strong></a>
                                    </div>
                                </td>
                                <td><?= date('M d', strtotime($b['start_date'])) ?> - <?= date('M d, Y', strtotime($b['end_date'])) ?></td>
                                <td><?= format_price($b['total_amount']) ?></td>
                                <td><span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span></td>
                                <td>
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <form method="POST" action="<?= SITE_URL ?>/api/booking-action.php" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="tab-wishlist">
            <?php if (empty($wishlist)): ?>
                <div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save properties you like to find them quickly later.</p>
                    <a href="<?= SITE_URL ?>/properties.php" class="btn btn-primary">Browse Properties</a>
                </div>
            <?php else: ?>
                <div class="property-grid">
                    <?php foreach ($wishlist as $p): ?>
                        <div class="property-card">
                            <a href="<?= SITE_URL ?>/property-details.php?id=<?= $p['id'] ?>" class="property-img">
                                <?php if (!empty($p['primary_image'])): ?>
                                    <img src="<?= e(image_url($p['primary_image'])) ?>" alt="<?= e($p['title']) ?>">
                                <?php else: ?>
                                    <div class="property-img-placeholder"><i class="fas fa-home"></i></div>
                                <?php endif; ?>
                                <span class="badge badge-type"><?= get_property_type_label($p['property_type']) ?></span>
                            </a>
                            <div class="property-body">
                                <h3><a href="<?= SITE_URL ?>/property-details.php?id=<?= $p['id'] ?>"><?= e($p['title']) ?></a></h3>
                                <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?= e($p['city']) ?></p>
                                <div class="property-specs">
                                    <span><i class="fas fa-bed"></i> <?= (int)$p['bedrooms'] ?> Beds</span>
                                    <span><i class="fas fa-bath"></i> <?= (int)$p['bathrooms'] ?> Baths</span>
                                </div>
                                <div class="property-footer">
                                    <span class="property-price"><?php if ($p['price_period'] === 'both' && $p['price_per_day'] !== null): ?><?= format_price($p['price']) ?><small>/mo</small> &middot; <?= format_price($p['price_per_day']) ?><small>/day</small><?php else: ?><?= format_price($p['price']) ?><small>/<?= $p['price_period'] === 'per_day' ? 'day' : 'month' ?></small><?php endif; ?></span>
                                    <button class="btn btn-danger btn-sm" onclick="toggleWishlist(event, <?= $p['id'] ?>)">Remove</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
