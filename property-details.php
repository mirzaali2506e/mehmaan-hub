<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();

$id = (int)($_GET['id'] ?? 0);
$property = get_property_by_id($id);

if (!$property) {
    flash('error', 'Property not found.');
    redirect('/properties.php');
}

$images = get_property_images($id);
$reviews = get_reviews($id);
$ratingData = get_avg_rating($id);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['count'] ?? 0;

$pageTitle = e($property['title']);
include __DIR__ . '/includes/header.php';
?>

<div class="property-detail">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/index.php">Home</a> /
            <a href="<?= SITE_URL ?>/properties.php">Properties</a> /
            <span><?= e($property['title']) ?></span>
        </div>

        <?php if (!empty($images)): ?>
        <div class="property-gallery">
            <div class="gallery-main">
                <img src="<?= e(image_url($images[0]['image_path'])) ?>" alt="<?= e($property['title']) ?>" id="mainGalleryImg">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="gallery-thumbs">
                <?php foreach ($images as $idx => $img): ?>
                    <div class="gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" onclick="changeMainImage(this, '<?= e(image_url($img['image_path'])) ?>')">
                        <img src="<?= e(image_url($img['image_path'])) ?>" alt="Thumbnail <?= $idx + 1 ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="property-gallery">
            <div class="gallery-main">
                <div class="property-img-placeholder large"><i class="fas fa-home"></i></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="property-detail-layout">
            <div class="property-detail-main">
                <div class="property-detail-header">
                    <div>
                        <h1><?= e($property['title']) ?></h1>
                        <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?= e($property['address'] . ', ' . $property['city']) ?></p>
                    </div>
                    <div class="property-detail-price">
                        <?php if ($property['price_period'] === 'both'): ?>
                            <span class="price"><?= format_price($property['price']) ?><small>/month</small></span>
                            <?php if ($property['price_per_day'] !== null): ?>
                                <span class="price price-alt"><?= format_price($property['price_per_day']) ?><small>/day</small></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="price"><?= format_price($property['price']) ?></span>
                            <small>/<?= $property['price_period'] === 'per_day' ? 'day' : 'month' ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="property-detail-specs">
                    <div class="spec-item">
                        <i class="fas fa-bed"></i>
                        <div>
                            <strong><?= (int)$property['bedrooms'] ?></strong>
                            <span>Bedrooms</span>
                        </div>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-bath"></i>
                        <div>
                            <strong><?= (int)$property['bathrooms'] ?></strong>
                            <span>Bathrooms</span>
                        </div>
                    </div>
                    <?php if ($property['area_sqft']): ?>
                    <div class="spec-item">
                        <i class="fas fa-ruler-combined"></i>
                        <div>
                            <strong><?= (int)$property['area_sqft'] ?></strong>
                            <span>Sq Ft</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="spec-item">
                        <i class="fas fa-building"></i>
                        <div>
                            <strong><?= get_property_type_label($property['property_type']) ?></strong>
                            <span>Type</span>
                        </div>
                    </div>
                </div>

                <?php
                $amenities = [];
                if ($property['has_wifi']) $amenities[] = ['fa-wifi', 'WiFi'];
                if ($property['has_ac']) $amenities[] = ['fa-snowflake', 'Air Conditioning'];
                if ($property['has_parking']) $amenities[] = ['fa-car', 'Parking'];
                if ($property['has_generator']) $amenities[] = ['fa-bolt', 'Backup Generator'];
                if ($property['is_furnished']) $amenities[] = ['fa-couch', 'Furnished'];
                ?>
                <?php if (!empty($amenities)): ?>
                <div class="property-amenities">
                    <h3>Amenities</h3>
                    <div class="amenities-grid">
                        <?php foreach ($amenities as $a): ?>
                        <div class="amenity-item">
                            <i class="fas <?= $a[0] ?>"></i>
                            <span><?= $a[1] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="property-description">
                    <h3>Description</h3>
                    <p><?= nl2br(e($property['description'])) ?></p>
                </div>

                <div class="property-reviews">
                    <h3>Reviews (<?= $reviewCount ?>)</h3>
                    <?php if ($avgRating > 0): ?>
                    <div class="rating-summary">
                        <span class="rating-big"><?= $avgRating ?></span>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= round($avgRating) ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span><?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($user && $user['role'] === 'tenant'): ?>
                    <form action="<?= SITE_URL ?>/api/add-review.php" method="POST" class="review-form">
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        <h4>Write a Review</h4>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                            <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                        <textarea name="comment" placeholder="Share your experience..." rows="3" required></textarea>
                        <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                    </form>
                    <?php endif; ?>

                    <?php if (empty($reviews)): ?>
                        <p class="no-reviews">No reviews yet. Be the first to review!</p>
                    <?php else: ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-avatar"><?= strtoupper(substr($review['user_name'], 0, 1)) ?></div>
                                    <div>
                                        <strong><?= e($review['user_name']) ?></strong>
                                        <div class="rating-stars small">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></span>
                                </div>
                                <p><?= e($review['comment']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="property-detail-sidebar">
                <div class="sidebar-card">
                    <h3>Contact Owner</h3>
                    <div class="owner-info">
                        <div class="owner-avatar"><?= strtoupper(substr($property['owner_name'], 0, 1)) ?></div>
                        <div>
                            <strong><?= e($property['owner_name']) ?></strong>
                            <span>Property Owner</span>
                        </div>
                    </div>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> <?= e($property['owner_phone'] ?? 'N/A') ?></p>
                        <p><i class="fas fa-envelope"></i> <?= e($property['owner_email']) ?></p>
                    </div>
                    <?php if ($user): ?>
                        <?php if ($property['owner_id'] == $user['id']): ?>
                            <div class="owner-own-notice">This is your property</div>
                        <?php elseif (has_user_booked_property($user['id'], $property['id'])): ?>
                            <div class="owner-own-notice" style="background:var(--success-50, #ecfdf5); color:var(--success, #10b981); border-color:var(--success, #10b981);">
                                <i class="fas fa-check-circle"></i> You already booked this property
                            </div>
                            <button class="btn btn-outline btn-block" onclick="toggleWishlist(event, <?= $property['id'] ?>)">
                                <i class="fas fa-heart"></i> <?= is_in_wishlist($user['id'], $property['id']) ? 'Remove from Wishlist' : 'Add to Wishlist' ?>
                            </button>
                        <?php else:
                            $confirmedBookings = get_confirmed_bookings_for_property($property['id']);
                        ?>
                            <a href="<?= SITE_URL ?>/booking.php?property_id=<?= $property['id'] ?>" class="btn btn-primary btn-block">Book Now</a>
                            <?php if (!empty($confirmedBookings)): ?>
                                <div class="booked-dates-notice">
                                    <h4><i class="fas fa-calendar-times"></i> Already Booked Dates</h4>
                                    <ul>
                                        <?php foreach ($confirmedBookings as $cb): ?>
                                            <li><?= date('M d, Y', strtotime($cb['start_date'])) ?> — <?= date('M d, Y', strtotime($cb['end_date'])) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <button class="btn btn-outline btn-block" onclick="toggleWishlist(event, <?= $property['id'] ?>)">
                                <i class="fas fa-heart"></i> <?= is_in_wishlist($user['id'], $property['id']) ? 'Remove from Wishlist' : 'Add to Wishlist' ?>
                            </button>
                        <?php endif; ?>
                    <?php elseif (!$user): ?>
                        <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary btn-block">Login to Book</a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
