<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
$wishlist = get_wishlist($user['id']);

$pageTitle = 'My Wishlist';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>My Wishlist</h1>
        <p>Properties you've saved for later</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if (empty($wishlist)): ?>
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Save properties you like by clicking the heart icon.</p>
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
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
