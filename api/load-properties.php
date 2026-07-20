<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$offset = (int)($_GET['offset'] ?? 0);
$limit = (int)($_GET['limit'] ?? 6);
if ($limit > 20) $limit = 20;

$search = trim($_GET['search'] ?? '');
$type = trim($_GET['type'] ?? '');
$city = trim($_GET['city'] ?? '');
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
if ($minPrice !== '' && $minPrice !== null) $minPrice = (float)$minPrice; else $minPrice = null;
if ($maxPrice !== '' && $maxPrice !== null) $maxPrice = (float)$maxPrice; else $maxPrice = null;

$sql = "SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.status = 'available'";
$params = [];
$types = '';
if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
    $term = "%$search%";
    array_push($params, $term, $term, $term, $term);
    $types .= 'ssss';
}
if ($type) {
    $sql .= " AND p.property_type = ?";
    $params[] = $type; $types .= 's';
}
if ($city) {
    $sql .= " AND p.city LIKE ?";
    $params[] = "%$city%"; $types .= 's';
}
if ($minPrice !== null) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice; $types .= 'd';
}
if ($maxPrice !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice; $types .= 'd';
}
$sql .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = db()->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$html = '';
foreach ($properties as $property) {
    $img = get_primary_image($property['id']);
    $periodLabel = $property['price_period'] === 'per_day' ? 'day' : 'month';
    $priceDisplay = format_price($property['price']) . '<small>/' . $periodLabel . '</small>';
    if ($property['price_period'] === 'both' && $property['price_per_day'] !== null) {
        $priceDisplay = format_price($property['price']) . '<small>/mo</small> &middot; ' . format_price($property['price_per_day']) . '<small>/day</small>';
    } elseif ($property['price_period'] === 'both') {
        $priceDisplay = format_price($property['price']) . '<small>/month</small>';
    }
    ob_start(); ?>
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
                <span class="property-price"><?= $priceDisplay ?></span>
                <a href="<?= SITE_URL ?>/property-details.php?id=<?= $property['id'] ?>" class="btn btn-outline btn-sm">View</a>
            </div>
        </div>
    </div>
    <?php $html .= ob_get_clean();
}

$countSql = "SELECT COUNT(*) as c FROM properties p WHERE p.status = 'available'";
$countParams = [];
$countTypes = '';
if ($search) {
    $countSql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
    $term = "%$search%";
    array_push($countParams, $term, $term, $term, $term);
    $countTypes .= 'ssss';
}
if ($type) { $countSql .= " AND p.property_type = ?"; $countParams[] = $type; $countTypes .= 's'; }
if ($city) { $countSql .= " AND p.city LIKE ?"; $countParams[] = "%$city%"; $countTypes .= 's'; }
if ($minPrice !== null) { $countSql .= " AND p.price >= ?"; $countParams[] = $minPrice; $countTypes .= 'd'; }
if ($maxPrice !== null) { $countSql .= " AND p.price <= ?"; $countParams[] = $maxPrice; $countTypes .= 'd'; }

$stmt = db()->prepare($countSql);
if ($countParams) { $stmt->bind_param($countTypes, ...$countParams); }
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c'];

echo json_encode([
    'html' => $html,
    'total' => $total,
    'has_more' => ($offset + $limit) < $total
]);
