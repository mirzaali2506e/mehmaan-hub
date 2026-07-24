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

                <!-- REAL MAP SECTION -->
                <div class="property-map-section">
                    <h3><i class="fas fa-map-marked-alt"></i> Location & Nearby Places</h3>
                    <p class="map-subtitle">See the exact location and what's around this property — schools, hospitals, parks, mosques and more.</p>
                    <div id="propertyMap">
                        <div class="map-loading"><i class="fas fa-spinner fa-spin"></i> Loading map...</div>
                    </div>
                    <div class="map-legend" id="mapLegend" style="display:none;">
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#dc2626;"></span> Property</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#2563eb;"></span> Schools</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#059669;"></span> Hospitals</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#16a34a;"></span> Parks</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#0ea5e9;"></span> Mosques</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#f59e0b;"></span> Markets</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#7c3aed;"></span> Restaurants</div>
                        <div class="map-legend-item"><span class="map-legend-pin" style="background:#6b7280;"></span> Fuel / Banks</div>
                    </div>
                    <div class="map-nearby-list" id="mapNearbyList"></div>
                </div>

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

<!-- Leaflet CSS/JS (OpenStreetMap — free, no API key required) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var address = <?= json_encode(($property['address'] ?? '') . ', ' . ($property['city'] ?? '')) ?>;
    var city = <?= json_encode($property['city'] ?? '') ?>;
    var title = <?= json_encode($property['title']) ?>;
    var mapEl = document.getElementById('propertyMap');
    if (!mapEl || typeof L === 'undefined') return;
    mapEl.innerHTML = '';

    var defaultCenter = [31.5204, 74.3587];
    var map = L.map('propertyMap').setView(defaultCenter, 14);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        maxZoom: 19
    }).addTo(map);

    var propIcon = L.divIcon({
        className: 'prop-map-icon',
        html: '<div style="background:#dc2626;width:28px;height:28px;border-radius:50% 50% 50% 0;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4);transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;"><i class="fas fa-home" style="color:#fff;font-size:11px;transform:rotate(45deg);"></i></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28]
    });
    var propMarker = L.marker(defaultCenter, { icon: propIcon }).addTo(map).bindPopup('<strong>' + title + '</strong><br>' + address);

    var categories = {
        school:     { label: 'School',      color: '#2563eb', icon: 'fa-graduation-cap' },
        hospital:   { label: 'Hospital',    color: '#059669', icon: 'fa-hospital' },
        park:       { label: 'Park',        color: '#16a34a', icon: 'fa-tree' },
        mosque:     { label: 'Mosque',      color: '#0ea5e9', icon: 'fa-mosque' },
        market:     { label: 'Market',      color: '#f59e0b', icon: 'fa-shopping-basket' },
        restaurant: { label: 'Restaurant',  color: '#7c3aed', icon: 'fa-utensils' },
        fuel:       { label: 'Fuel/Bank',   color: '#6b7280', icon: 'fa-gas-pump' }
    };

    function makeCatIcon(color, faIcon) {
        return L.divIcon({
            className: 'nearby-map-icon',
            html: '<div style="background:' + color + ';width:22px;height:22px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;"><i class="fas ' + faIcon + '" style="color:#fff;font-size:9px;"></i></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });
    }

    var nearbyList = document.getElementById('mapNearbyList');
    var allCoords = [defaultCenter];

    var R = 6371000, toRad = Math.PI / 180;
    function haversine(lat1, lon1, lat2, lon2) {
        var dLat = (lat2 - lat1) * toRad, dLon = (lon2 - lon1) * toRad;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2) + Math.cos(lat1*toRad)*Math.cos(lat2*toRad)*Math.sin(dLon/2)*Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }
    function escapeHtml(s) {
        var d = document.createElement('div'); d.textContent = s; return d.innerHTML;
    }

    function addNearbyCard(catKey, name, dist) {
        var cat = categories[catKey];
        if (!cat) return;
        var card = document.createElement('div');
        card.className = 'map-nearby-card';
        card.innerHTML =
            '<div class="map-nearby-icon" style="background:' + cat.color + '"><i class="fas ' + cat.icon + '"></i></div>' +
            '<div class="map-nearby-info"><strong>' + escapeHtml(name) + '</strong><span>' + cat.label + ' &middot; ' + dist + ' m away</span></div>';
        nearbyList.appendChild(card);
    }

    function classifyElement(el) {
        var tags = el.tags || {};
        if (tags.amenity === 'school' || tags.amenity === 'kindergarten' || tags.amenity === 'college' || tags.amenity === 'university') return 'school';
        if (tags.amenity === 'hospital' || tags.amenity === 'clinic' || tags.amenity === 'doctors' || tags.amenity === 'pharmacy') return 'hospital';
        if (tags.leisure === 'park' || tags.leisure === 'garden' || tags.leisure === 'playground') return 'park';
        if (tags.amenity === 'place_of_worship') return 'mosque';
        if (tags.shop === 'supermarket' || tags.shop === 'convenience' || tags.shop === 'marketplace') return 'market';
        if (tags.amenity === 'restaurant' || tags.amenity === 'fast_food' || tags.amenity === 'cafe') return 'restaurant';
        if (tags.amenity === 'fuel' || tags.amenity === 'atm' || tags.amenity === 'bank') return 'fuel';
        return null;
    }

    function loadNearby(lat, lon) {
        document.getElementById('mapLegend').style.display = 'flex';
        var rad = 1800;
        var query = '[out:json][timeout:15];(' +
            'node["amenity"~"^(school|kindergarten|college|university|hospital|clinic|doctors|pharmacy|place_of_worship|restaurant|fast_food|cafe|fuel|atm|bank)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            'way["amenity"~"^(school|kindergarten|college|university|hospital|clinic|doctors|pharmacy|place_of_worship|restaurant|fast_food|cafe|fuel|atm|bank)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            'node["leisure"~"^(park|garden|playground)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            'way["leisure"~"^(park|garden|playground)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            'node["shop"~"^(supermarket|convenience|marketplace)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            'way["shop"~"^(supermarket|convenience|marketplace)$"](around:' + rad + ',' + lat + ',' + lon + ');' +
            ');out center 40;';

        fetch('https://overpass-api.de/api/interpreter', {
            method: 'POST',
            body: 'data=' + encodeURIComponent(query)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data || !data.elements) return;
            var seen = {};
            var perCat = {};
            data.elements.forEach(function(el) {
                var elat = el.lat || (el.center && el.center.lat);
                var elon = el.lon || (el.center && el.center.lon);
                if (!elat || !elon) return;
                var catKey = classifyElement(el);
                if (!catKey) return;
                perCat[catKey] = (perCat[catKey] || 0) + 1;
                if (perCat[catKey] > 5) return;
                var name = (el.tags && (el.tags['name:en'] || el.tags.name)) ? (el.tags['name:en'] || el.tags.name) : categories[catKey].label;
                var dist = Math.round(haversine(lat, lon, elat, elon));
                var idKey = catKey + ':' + elat + ':' + elon;
                if (seen[idKey]) return;
                seen[idKey] = true;
                var cat = categories[catKey];
                var marker = L.marker([elat, elon], { icon: makeCatIcon(cat.color, cat.icon) }).addTo(map);
                marker.bindPopup('<strong style="color:' + cat.color + '">' + escapeHtml(name) + '</strong><br>' + cat.label + ' &middot; ' + dist + ' m away');
                addNearbyCard(catKey, name, dist);
                allCoords.push([elat, elon]);
            });
            if (allCoords.length > 1) {
                map.fitBounds(L.latLngBounds(allCoords), { padding: [50, 50], maxZoom: 16 });
            }
        })
        .catch(function() {
            var errDiv = document.createElement('p');
            errDiv.style.cssText = 'color:#999;font-size:.85rem;text-align:center;margin-top:12px;';
            errDiv.textContent = 'Could not load nearby places. The map location is still visible above.';
            nearbyList.appendChild(errDiv);
        });
    }

    var geocodeUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(address);
    fetch(geocodeUrl, { headers: { 'Accept-Language': 'en' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.length > 0) {
                var lat = parseFloat(data[0].lat);
                var lon = parseFloat(data[0].lon);
                propMarker.setLatLng([lat, lon]);
                map.setView([lat, lon], 15);
                loadNearby(lat, lon);
            } else {
                var cityUrl = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(city);
                return fetch(cityUrl, { headers: { 'Accept-Language': 'en' } })
                    .then(function(r2) { return r2.json(); })
                    .then(function(data2) {
                        if (data2 && data2.length > 0) {
                            var lat2 = parseFloat(data2[0].lat);
                            var lon2 = parseFloat(data2[0].lon);
                            propMarker.setLatLng([lat2, lon2]);
                            map.setView([lat2, lon2], 14);
                            loadNearby(lat2, lon2);
                        } else {
                            loadNearby(defaultCenter[0], defaultCenter[1]);
                        }
                    });
            }
        })
        .catch(function() {
            loadNearby(defaultCenter[0], defaultCenter[1]);
        });
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
