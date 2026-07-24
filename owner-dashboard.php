<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_role('owner');

$myProperties = get_user_properties($user['id']);
$ownerBookings = get_owner_bookings($user['id']);

$stats = [
    'properties' => count($myProperties),
    'available' => 0,
    'rented' => 0,
    'bookings' => count($ownerBookings),
    'pending' => 0,
    'confirmed' => 0,
];
foreach ($myProperties as $p) {
    if ($p['status'] === 'available') $stats['available']++;
    if ($p['status'] === 'rented') $stats['rented']++;
}
foreach ($ownerBookings as $b) {
    if ($b['status'] === 'pending') $stats['pending']++;
    if ($b['status'] === 'confirmed') $stats['confirmed']++;
}

$pageTitle = 'Owner Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Owner Dashboard</h1>
                <p>Welcome back, <?= e($user['name']) ?>!</p>
            </div>
            <a href="<?= SITE_URL ?>/add-property.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Property</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-blue"><i class="fas fa-building"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['properties'] ?></strong>
                    <span>Total Properties</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['available'] ?></strong>
                    <span>Available</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-orange"><i class="fas fa-calendar"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['bookings'] ?></strong>
                    <span>Total Bookings</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-purple"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['pending'] ?></strong>
                    <span>Pending Bookings</span>
                </div>
            </div>
        </div>

        <div class="dashboard-tabs">
            <button class="tab-btn active" data-tab="properties"><i class="fas fa-building"></i> My Properties</button>
            <button class="tab-btn" data-tab="bookings"><i class="fas fa-calendar"></i> Bookings</button>
        </div>

        <div class="tab-content active" id="tab-properties">
            <?php if (empty($myProperties)): ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <h3>No properties yet</h3>
                    <p>Start listing your properties to receive bookings.</p>
                    <a href="<?= SITE_URL ?>/add-property.php" class="btn btn-primary">Add Your First Property</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myProperties as $p): ?>
                            <tr>
                                <td data-label="Property">
                                    <div class="table-property">
                                        <?php if (!empty($p['primary_image'])): ?>
                                            <img src="<?= e(image_url($p['primary_image'])) ?>" alt="">
                                        <?php else: ?>
                                            <div class="table-img-placeholder"><i class="fas fa-home"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= e($p['title']) ?></strong>
                                            <span><?= e($p['city']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Type"><?= get_property_type_label($p['property_type']) ?></td>
                                <td data-label="Price"><?php if ($p['price_period'] === 'both' && $p['price_per_day'] !== null): ?><?= format_price($p['price']) ?><small style="color:#888"> /mo</small> &middot; <?= format_price($p['price_per_day']) ?><small style="color:#888"> /day</small><?php else: ?><?= format_price($p['price']) ?><small style="color:#888"> /<?= $p['price_period'] === 'per_day' ? 'day' : 'month' ?></small><?php endif; ?></td>
                                <td data-label="Status"><span class="status-badge status-<?= e($p['status']) ?>"><?= ucfirst(e($p['status'])) ?></span></td>
                                <td data-label="Actions">
                                    <a href="<?= SITE_URL ?>/property-details.php?id=<?= $p['id'] ?>" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= SITE_URL ?>/edit-property.php?id=<?= $p['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= SITE_URL ?>/api/delete-property.php?id=<?= $p['id'] ?>" class="btn-icon btn-danger" title="Delete" onclick="return confirm('Delete this property?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="tab-bookings">
            <?php if (empty($ownerBookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar"></i>
                    <h3>No bookings yet</h3>
                    <p>When tenants book your properties, they will appear here.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ownerBookings as $b): ?>
                            <tr>
                                <td data-label="Property">
                                    <div class="table-property">
                                        <?php if (!empty($b['primary_image'])): ?>
                                            <img src="<?= e(image_url($b['primary_image'])) ?>" alt="">
                                        <?php else: ?>
                                            <div class="table-img-placeholder"><i class="fas fa-home"></i></div>
                                        <?php endif; ?>
                                        <strong><?= e($b['property_title']) ?></strong>
                                    </div>
                                </td>
                                <td data-label="Tenant">
                                    <strong><?= e($b['tenant_name']) ?></strong><br>
                                    <small><?= e($b['tenant_phone'] ?? '') ?></small>
                                </td>
                                <td data-label="Dates"><?= date('M d', strtotime($b['start_date'])) ?> - <?= date('M d, Y', strtotime($b['end_date'])) ?></td>
                                <td data-label="Amount"><?= format_price($b['total_amount']) ?></td>
                                <td data-label="Status"><span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span></td>
                                <td data-label="Actions">
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <?php
                                        $waPhone = preg_replace('/[^0-9]/', '', $b['tenant_phone'] ?? '');
                                        if (!str_starts_with($waPhone, '92') && str_starts_with($waPhone, '0')) {
                                            $waPhone = '92' . substr($waPhone, 1);
                                        }
                                        $waMsg = rawurlencode("Hello " . $b['tenant_name'] . ", this is regarding your booking request for '" . $b['property_title'] . "' (" . date('M d', strtotime($b['start_date'])) . " - " . date('M d, Y', strtotime($b['end_date'])) . ") on Mehmaan Hub.");
                                        ?>
                                        <a href="<?= SITE_URL ?>/api/booking-action.php?id=<?= $b['id'] ?>&action=confirm" class="btn btn-success btn-sm">Confirm</a>
                                        <a href="<?= SITE_URL ?>/api/booking-action.php?id=<?= $b['id'] ?>&action=cancel" class="btn btn-danger btn-sm">Cancel</a>
                                        <?php if ($waPhone): ?>
                                        <a href="https://wa.me/<?= $waPhone ?>?text=<?= $waMsg ?>" target="_blank" class="btn btn-outline btn-sm" style="color:#25D366;border-color:#25D366;" title="Message tenant on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
