<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$user = require_role('owner');
if (!$user) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$title = trim($input['title'] ?? '');
$propertyType = $input['property_type'] ?? 'apartment';
$city = trim($input['city'] ?? '');
$area = trim($input['area'] ?? '');
$address = trim($input['address'] ?? '');
$bedrooms = (int)($input['bedrooms'] ?? 1);
$bathrooms = (int)($input['bathrooms'] ?? 1);
$areaSqft = (int)($input['area_sqft'] ?? 0);
$price = (float)($input['price'] ?? 0);
$pricePeriod = $input['price_period'] ?? 'per_month';
$pricePerDay = (float)($input['price_per_day'] ?? 0);

$amenities = [];
if (!empty($input['is_furnished'])) $amenities[] = 'fully furnished';
if (!empty($input['has_parking'])) $amenities[] = 'dedicated parking';
if (!empty($input['has_wifi'])) $amenities[] = 'high-speed WiFi';
if (!empty($input['has_ac'])) $amenities[] = 'air conditioning';
if (!empty($input['has_generator'])) $amenities[] = 'backup generator';

$typeLabels = [
    'apartment' => 'apartment',
    'house' => 'house',
    'room' => 'room',
    'studio' => 'studio apartment',
    'villa' => 'villa',
];
$typeLabel = $typeLabels[$propertyType] ?? 'property';

$location = '';
if ($address && $city) $location = $address . ', ' . $city;
elseif ($city) $location = $city;
elseif ($area) $location = $area;

$priceText = 'Rs ' . number_format($price);
if ($pricePeriod === 'per_day') $priceText .= '/day';
elseif ($pricePeriod === 'per_month') $priceText .= '/month';
elseif ($pricePeriod === 'both') {
    $priceText .= '/month';
    if ($pricePerDay) $priceText .= ' or Rs ' . number_format($pricePerDay) . '/day';
}

$openings = [
    "Looking for a comfortable place to call home?",
    "Welcome to your next living space.",
    "Step into comfort and convenience with this exceptional rental.",
    "Discover the perfect blend of comfort and style.",
    "Your ideal rental home awaits.",
];
$opening = $openings[array_rand($openings)];

$introParts = [];
$introParts[] = $opening;
if ($title) {
    $introParts[] = "This " . $typeLabel . ", \"" . $title . "\",";
} else {
    $introParts[] = "This " . $typeLabel;
}
if ($location) {
    $introParts[] = "is conveniently located in " . $location . ".";
} else {
    $introParts[] = "offers a great living experience.";
}

$descParts = [];
$descParts[] = implode(' ', $introParts);

$detailParts = [];
if ($bedrooms > 0) {
    $detailParts[] = "Featuring " . $bedrooms . " " . ($bedrooms === 1 ? 'bedroom' : 'bedrooms');
}
if ($bathrooms > 0) {
    $detailParts[] = ($bedrooms > 0 ? 'and ' : 'Featuring ') . $bathrooms . " " . ($bathrooms === 1 ? 'bathroom' : 'bathrooms');
}
if ($areaSqft > 0) {
    $detailParts[] = "spread across " . number_format($areaSqft) . " square feet of well-designed living space";
}
if (count($detailParts) > 0) {
    $descParts[] = implode(', ', $detailParts) . ".";
}

if (!empty($amenities)) {
    $amenityText = "The property comes with " . implode(', ', array_slice($amenities, 0, -1));
    if (count($amenities) > 1) {
        $amenityText .= ', and ' . end($amenities);
    } else {
        $amenityText = "The property comes with " . $amenities[0];
    }
    $amenityText .= ", ensuring a comfortable and convenient lifestyle.";
    $descParts[] = $amenityText;
}

$neighborhoodParts = [];
if ($city) {
    $neighborhoodParts[] = "Situated in " . $city;
}
if ($area) {
    $neighborhoodParts[] = "the " . $area . " area offers easy access to local amenities, dining, shopping, and transportation";
}
if (count($neighborhoodParts) > 0) {
    $descParts[] = implode(', ', $neighborhoodParts) . ".";
} else {
    $descParts[] = "The surrounding area provides convenient access to everyday necessities.";
}

$descParts[] = "Available for rent at " . $priceText . ", this " . $typeLabel . " presents an excellent opportunity for those seeking quality accommodation.";

$closings = [
    "Don't miss the chance to make this your new home — schedule a visit today!",
    "Contact us now to arrange a viewing and secure this property.",
    "Book your visit today and experience comfortable living at its best.",
    "Reach out today to learn more and schedule a tour of this wonderful property.",
];
$descParts[] = $closings[array_rand($closings)];

$description = implode("\n\n", $descParts);

echo json_encode(['description' => $description]);
