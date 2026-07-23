<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();
$featuredProperties = get_all_properties(6);
$totalProperties = count(get_all_properties());
$bookedSet = [];
if ($user && $user['role'] === 'tenant') {
    $bookedSet = array_flip(get_user_booked_property_ids($user['id']));
}
$cities = [];
$res = db()->query("SELECT DISTINCT city FROM properties WHERE city != '' AND status = 'available' ORDER BY city");
while ($row = $res->fetch_assoc()) {
    $cities[] = $row['city'];
}
$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <h1>Find Your Perfect <span class="text-accent">Rental Home</span></h1>
        <p>Discover verified rental properties from trusted owners across Pakistan. Apartments, houses, rooms, and more.</p>
        <form id="heroSearchForm" class="hero-search">
            <div class="search-field">
                <i class="fas fa-search"></i>
                <input type="text" name="search" id="heroSearch" placeholder="Search by title, city, or address...">
            </div>
            <div class="search-field">
                <i class="fas fa-map-marker-alt"></i>
                <select name="city" id="heroCity">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong><?= $totalProperties ?></strong>
                <span>Properties</span>
            </div>
            <div class="hero-stat">
                <strong><?= db()->query("SELECT COUNT(*) as c FROM users WHERE role = 'owner'")->fetch_assoc()['c'] ?></strong>
                <span>Owners</span>
            </div>
            <div class="hero-stat">
                <strong><?= count($cities) ?></strong>
                <span>Cities</span>
            </div>
        </div>
    </div>
</section>

<section class="section" id="featuredProperties">
    <div class="container">
        <div class="section-header">
            <h2>Featured Properties</h2>
            <p>Handpicked premium rental listings just for you</p>
        </div>
        <?php if (empty($featuredProperties)): ?>
            <div class="empty-state">
                <i class="fas fa-home"></i>
                <h3>No properties yet</h3>
                <p>Property owners haven't listed any properties yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="property-grid" id="featuredGrid">
                <?php foreach ($featuredProperties as $property): ?>
                    <?php $img = get_primary_image($property['id']);
                    $periodLabel = $property['price_period'] === 'per_day' ? 'day' : 'month';
                    $priceClass = '';
                    if ($property['price_period'] === 'both' && $property['price_per_day'] !== null) {
                        $priceDisplay = format_price($property['price']) . '<small>/mo</small> &middot; ' . format_price($property['price_per_day']) . '<small>/day</small>';
                        $priceClass = ' dual';
                    } elseif ($property['price_period'] === 'both') {
                        $priceDisplay = format_price($property['price']) . '<small>/month</small>';
                    } else {
                        $priceDisplay = format_price($property['price']) . '<small>/' . $periodLabel . '</small>';
                    } ?>
                    <div class="property-card">
                        <a href="<?= SITE_URL ?>/property-details.php?id=<?= $property['id'] ?>" class="property-img">
                            <?php if ($img): ?>
                                <img src="<?= e(image_url($img)) ?>" alt="<?= e($property['title']) ?>">
                            <?php else: ?>
                                <div class="property-img-placeholder"><i class="fas fa-home"></i></div>
                            <?php endif; ?>
                            <?php if ($property['featured']): ?>
                                <span class="badge badge-featured">Featured</span>
                            <?php endif; ?>
                            <?php if (isset($bookedSet[$property['id']])): ?>
                                <span class="badge badge-booked">Booked by You</span>
                            <?php endif; ?>
                            <span class="badge badge-type"><?= get_property_type_label($property['property_type']) ?></span>
                        </a>
                        <div class="property-body">
                            <h3><a href="<?= SITE_URL ?>/property-details.php?id=<?= $property['id'] ?>"><?= e($property['title']) ?></a></h3>
                            <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?= e($property['address'] . ', ' . $property['city']) ?></p>
                            <div class="property-specs">
                                <span><i class="fas fa-bed"></i> <?= (int)$property['bedrooms'] ?> Beds</span>
                                <span><i class="fas fa-bath"></i> <?= (int)$property['bathrooms'] ?> Baths</span>
                                <?php if ($property['area_sqft']): ?>
                                    <span><i class="fas fa-ruler-combined"></i> <?= (int)$property['area_sqft'] ?> sqft</span>
                                <?php endif; ?>
                            </div>
                            <div class="property-footer">
                                <span class="property-price<?= $priceClass ?>"><?= $priceDisplay ?></span>
                                <?php if (isset($bookedSet[$property['id']])): ?>
                                    <span class="badge badge-booked badge-booked-sm">Booked</span>
                                <?php else: ?>
                                    <a href="<?= SITE_URL ?>/property-details.php?id=<?= $property['id'] ?>" class="btn btn-outline btn-sm">View</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($totalProperties > 6): ?>
                <div class="section-cta" id="showMoreCta">
                    <button class="btn btn-primary" id="showMoreBtn" onclick="loadMoreProperties()">
                        <i class="fas fa-chevron-down"></i> Show More Properties
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Find your rental in three simple steps</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon"><i class="fas fa-search"></i></div>
                <h3>Search</h3>
                <p>Browse our extensive collection of verified rental properties across Pakistan.</p>
            </div>
            <div class="step-card">
                <div class="step-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Book</h3>
                <p>Contact the owner directly and book your preferred property in minutes.</p>
            </div>
            <div class="step-card">
                <div class="step-icon"><i class="fas fa-key"></i></div>
                <h3>Move In</h3>
                <p>Complete the booking and get your keys. Welcome to your new home!</p>
            </div>
        </div>
    </div>
</section>

<?php if (!$user || $user['role'] === 'tenant'): ?>
<section class="section">
    <div class="container">
        <div class="cta-banner">
            <div class="cta-content">
                <h2>Have a property to rent out?</h2>
                <p>List your property on Mehmaan Hub and reach thousands of potential tenants.</p>
                <?php if ($user && $user['role'] === 'tenant'): ?>
                    <a href="<?= SITE_URL ?>/become-host.php" class="btn btn-light">Become a Host</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/register.php?role=owner" class="btn btn-light">Become a Host</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
let currentOffset = 6;
let heroSearch = '';
let heroCity = '';
let heroFilterTimer = null;

function triggerHeroFilter() {
    clearTimeout(heroFilterTimer);
    heroFilterTimer = setTimeout(function() {
        heroSearch = document.getElementById('heroSearch').value.trim();
        heroCity = document.getElementById('heroCity').value;
        currentOffset = 0;
        document.getElementById('featuredGrid').innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading properties...</div>';
        loadMoreProperties(true);
    }, 300);
}

document.getElementById('heroSearch').addEventListener('input', triggerHeroFilter);
document.getElementById('heroCity').addEventListener('change', function() {
    heroSearch = document.getElementById('heroSearch').value.trim();
    heroCity = document.getElementById('heroCity').value;
    currentOffset = 0;
    document.getElementById('featuredGrid').innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading properties...</div>';
    document.getElementById('featuredProperties').scrollIntoView({ behavior: 'smooth', block: 'start' });
    loadMoreProperties(true);
});

document.getElementById('heroSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    heroSearch = document.getElementById('heroSearch').value.trim();
    heroCity = document.getElementById('heroCity').value;
    currentOffset = 0;
    document.getElementById('featuredGrid').innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading properties...</div>';
    document.getElementById('featuredProperties').scrollIntoView({ behavior: 'smooth', block: 'start' });
    loadMoreProperties(true);
});

function loadMoreProperties(reset) {
    const btn = document.getElementById('showMoreBtn');
    if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

    const params = new URLSearchParams({
        offset: reset ? 0 : currentOffset,
        limit: 6,
        search: heroSearch,
        city: heroCity
    });

    fetch(SITE_URL + '/api/load-properties.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (reset) {
                document.getElementById('featuredGrid').innerHTML = data.html || '<div class="empty-state"><i class="fas fa-search"></i><h3>No properties found</h3><p>Try adjusting your search.</p></div>';
                currentOffset = 6;
            } else {
                document.getElementById('featuredGrid').insertAdjacentHTML('beforeend', data.html);
                currentOffset += 6;
            }
            const cta = document.getElementById('showMoreCta');
            if (cta) {
                cta.style.display = data.has_more ? 'block' : 'none';
            }
            if (btn) btn.innerHTML = '<i class="fas fa-chevron-down"></i> Show More Properties';
        })
        .catch(() => {
            if (btn) btn.innerHTML = '<i class="fas fa-chevron-down"></i> Show More Properties';
        });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
