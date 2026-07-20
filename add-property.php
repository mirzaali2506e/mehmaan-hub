<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_role('owner');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $propertyType = $_POST['property_type'] ?? 'apartment';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $pricePeriod = $_POST['price_period'] ?? 'per_month';
    if (!in_array($pricePeriod, ['per_day', 'per_month', 'both'])) $pricePeriod = 'per_month';
    $pricePerDay = ($pricePeriod === 'both' && !empty($_POST['price_per_day'])) ? (float)$_POST['price_per_day'] : null;
    $bedrooms = (int)($_POST['bedrooms'] ?? 1);
    $bathrooms = (int)($_POST['bathrooms'] ?? 1);
    $areaSqft = !empty($_POST['area_sqft']) ? (int)$_POST['area_sqft'] : null;
    $isFurnished = isset($_POST['is_furnished']) ? 1 : 0;
    $hasParking = isset($_POST['has_parking']) ? 1 : 0;
    $hasWifi = isset($_POST['has_wifi']) ? 1 : 0;
    $hasAc = isset($_POST['has_ac']) ? 1 : 0;
    $hasGenerator = isset($_POST['has_generator']) ? 1 : 0;

    if (!$title || !$price) {
        flash('error', 'Please fill in title and price.');
    } else {
        $stmt = db()->prepare('INSERT INTO properties (owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssssdssdiiiiiii', $user['id'], $title, $description, $propertyType, $address, $city, $area, $price, $pricePeriod, $pricePerDay, $bedrooms, $bathrooms, $areaSqft, $isFurnished, $hasParking, $hasWifi, $hasAc, $hasGenerator);

        if ($stmt->execute()) {
            $propertyId = $stmt->insert_id;

            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = UPLOAD_DIR;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
                    if ($_FILES['images']['error'][$idx] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['images']['name'][$idx], PATHINFO_EXTENSION);
                        $filename = 'property_' . $propertyId . '_' . $idx . '.' . $ext;
                        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                            $isPrimary = $idx === 0 ? 1 : 0;
                            $imgStmt = db()->prepare('INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)');
                            $imgStmt->bind_param('isii', $propertyId, $filename, $isPrimary, $idx);
                            $imgStmt->execute();
                        }
                    }
                }
            }

            flash('success', 'Property added successfully!');
            redirect('/owner-dashboard.php');
        } else {
            flash('error', 'Failed to add property. Please try again.');
        }
    }
}

$pageTitle = 'Add Property';
include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>Add New Property</h1>
                <p>Fill in the details to list your property</p>
            </div>

            <form action="<?= SITE_URL ?>/add-property.php" method="POST" enctype="multipart/form-data" class="property-form">
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" placeholder="e.g. 2 Bed Apartment in Gulberg" required>
                        </div>
                        <div class="form-group">
                            <label for="property_type">Property Type</label>
                            <select id="property_type" name="property_type">
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="room">Room</option>
                                <option value="studio">Studio</option>
                                <option value="villa">Villa</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" placeholder="Describe your property..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Location</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" placeholder="Street address">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" placeholder="e.g. Lahore">
                        </div>
                        <div class="form-group">
                            <label for="area">Area</label>
                            <input type="text" id="area" name="area" placeholder="e.g. Gulberg III">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">Price (Rs) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" min="0" step="any" placeholder="e.g. 50000" required>
                            <small id="priceHint" class="form-hint">Monthly rent amount</small>
                        </div>
                        <div class="form-group">
                            <label for="price_period">Pricing Period <span class="required">*</span></label>
                            <select id="price_period" name="price_period" onchange="toggleDailyPrice()">
                                <option value="per_month">Per Month</option>
                                <option value="per_day">Per Day</option>
                                <option value="both">Both (Day/Month)</option>
                            </select>
                        </div>
                        <div class="form-group" id="pricePerDayGroup" style="display:none;">
                            <label for="price_per_day">Price Per Day (Rs) <span class="required">*</span></label>
                            <input type="number" id="price_per_day" name="price_per_day" min="0" step="any" placeholder="e.g. 2000">
                            <small class="form-hint">Daily rent amount (shown when Both is selected)</small>
                        </div>
                        <div class="form-group">
                            <label for="bedrooms">Bedrooms</label>
                            <input type="number" id="bedrooms" name="bedrooms" min="0" value="1">
                        </div>
                        <div class="form-group">
                            <label for="bathrooms">Bathrooms</label>
                            <input type="number" id="bathrooms" name="bathrooms" min="0" value="1">
                        </div>
                        <div class="form-group">
                            <label for="area_sqft">Area (sqft)</label>
                            <input type="number" id="area_sqft" name="area_sqft" min="0" placeholder="e.g. 1200">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Amenities</h3>
                    <div class="amenities-checks">
                        <label class="check-option">
                            <input type="checkbox" name="is_furnished" value="1">
                            <span><i class="fas fa-couch"></i> Furnished</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_parking" value="1">
                            <span><i class="fas fa-car"></i> Parking</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_wifi" value="1">
                            <span><i class="fas fa-wifi"></i> WiFi</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_ac" value="1">
                            <span><i class="fas fa-snowflake"></i> AC</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_generator" value="1">
                            <span><i class="fas fa-bolt"></i> Generator</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Property Images</h3>
                    <p class="form-hint">Upload multiple images. First image will be shown as the main image.</p>
                    <div class="image-upload-area" id="imageUploadArea">
                        <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
                        <label for="imageInput" class="upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload images</span>
                            <small>or drag and drop (JPG, PNG, WebP)</small>
                        </label>
                    </div>
                    <div class="image-preview-grid" id="imagePreviewGrid"></div>
                </div>

                <div class="form-actions">
                    <a href="<?= SITE_URL ?>/owner-dashboard.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Property</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleDailyPrice() {
    const period = document.getElementById('price_period').value;
    const dailyGroup = document.getElementById('pricePerDayGroup');
    const priceHint = document.getElementById('priceHint');
    const priceInput = document.getElementById('price');
    if (period === 'both') {
        dailyGroup.style.display = 'block';
        priceHint.textContent = 'Monthly rent amount';
        priceInput.placeholder = 'e.g. 50000 (monthly)';
    } else if (period === 'per_day') {
        dailyGroup.style.display = 'none';
        priceHint.textContent = 'Daily rent amount';
        priceInput.placeholder = 'e.g. 2000 (daily)';
    } else {
        dailyGroup.style.display = 'none';
        priceHint.textContent = 'Monthly rent amount';
        priceInput.placeholder = 'e.g. 50000 (monthly)';
    }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
