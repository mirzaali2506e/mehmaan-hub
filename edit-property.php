<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_role('owner');

$id = (int)($_GET['id'] ?? 0);
$property = get_property_by_id($id);

if (!$property || ($property['owner_id'] != $user['id'] && $user['role'] !== 'admin')) {
    flash('error', 'Property not found or access denied.');
    redirect('/owner-dashboard.php');
}

$images = get_property_images($id);

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
    $status = $_POST['status'] ?? 'available';

    if (!$title || !$price) {
        flash('error', 'Please fill in title and price.');
    } else {
        $stmt = db()->prepare('UPDATE properties SET title=?, description=?, property_type=?, address=?, city=?, area=?, price=?, price_period=?, price_per_day=?, bedrooms=?, bathrooms=?, area_sqft=?, is_furnished=?, has_parking=?, has_wifi=?, has_ac=?, has_generator=?, status=? WHERE id=?');
        $stmt->bind_param('ssssssdssdiiiiiiisi', $title, $description, $propertyType, $address, $city, $area, $price, $pricePeriod, $pricePerDay, $bedrooms, $bathrooms, $areaSqft, $isFurnished, $hasParking, $hasWifi, $hasAc, $hasGenerator, $status, $id);

        if ($stmt->execute()) {
            if (!empty($_FILES['images']['name'][0])) {
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
                $startIdx = count($images);
                foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
                    if ($_FILES['images']['error'][$idx] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['images']['name'][$idx], PATHINFO_EXTENSION);
                        $filename = 'property_' . $id . '_' . ($startIdx + $idx) . '.' . $ext;
                        if (move_uploaded_file($tmpName, UPLOAD_DIR . $filename)) {
                            $imgStmt = db()->prepare('INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?, ?, 0, ?)');
                            $order = $startIdx + $idx;
                            $imgStmt->bind_param('isi', $id, $filename, $order);
                            $imgStmt->execute();
                        }
                    }
                }
            }
            flash('success', 'Property updated successfully!');
            redirect('/owner-dashboard.php');
        } else {
            flash('error', 'Failed to update property.');
        }
    }
}

$pageTitle = 'Edit Property';
include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1>Edit Property</h1>
                <p>Update your property details</p>
            </div>

            <form action="<?= SITE_URL ?>/edit-property.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data" class="property-form">
                <div class="form-section">
                    <h3>Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" value="<?= e($property['title']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="property_type">Property Type</label>
                            <select id="property_type" name="property_type">
                                <?php foreach (['apartment', 'house', 'room', 'studio', 'villa'] as $t): ?>
                                    <option value="<?= $t ?>" <?= $property['property_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="description">Description</label>
                            <div class="description-toolbar">
                                <button type="button" class="btn btn-ai" id="generateDescBtn" onclick="generateDescription()">
                                    <i class="fas fa-magic"></i> <span id="generateBtnText">Generate with AI</span>
                                </button>
                                <button type="button" class="btn btn-ai btn-ai-regen" id="regenerateDescBtn" onclick="generateDescription()" style="display:none;">
                                    <i class="fas fa-sync-alt"></i> Regenerate
                                </button>
                            </div>
                            <textarea id="description" name="description" rows="4"><?= e($property['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Location</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= e($property['address']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?= e($property['city']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="area">Area</label>
                            <input type="text" id="area" name="area" value="<?= e($property['area']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">Price (Rs) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" min="0" step="any" value="<?= e($property['price']) ?>" required>
                            <small id="priceHint" class="form-hint">Monthly rent amount</small>
                        </div>
                        <div class="form-group">
                            <label for="price_period">Pricing Period <span class="required">*</span></label>
                            <select id="price_period" name="price_period" onchange="toggleDailyPrice()">
                                <option value="per_month" <?= $property['price_period'] === 'per_month' ? 'selected' : '' ?>>Per Month</option>
                                <option value="per_day" <?= $property['price_period'] === 'per_day' ? 'selected' : '' ?>>Per Day</option>
                                <option value="both" <?= $property['price_period'] === 'both' ? 'selected' : '' ?>>Both (Day/Month)</option>
                            </select>
                        </div>
                        <div class="form-group" id="pricePerDayGroup" style="display:<?= $property['price_period'] === 'both' ? 'block' : 'none' ?>;">
                            <label for="price_per_day">Price Per Day (Rs) <span class="required">*</span></label>
                            <input type="number" id="price_per_day" name="price_per_day" min="0" step="any" value="<?= e($property['price_per_day'] ?? '') ?>" placeholder="e.g. 2000">
                            <small class="form-hint">Daily rent amount (shown when Both is selected)</small>
                        </div>
                        <div class="form-group">
                            <label for="bedrooms">Bedrooms</label>
                            <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?= (int)$property['bedrooms'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="bathrooms">Bathrooms</label>
                            <input type="number" id="bathrooms" name="bathrooms" min="0" value="<?= (int)$property['bathrooms'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="area_sqft">Area (sqft)</label>
                            <input type="number" id="area_sqft" name="area_sqft" min="0" value="<?= (int)$property['area_sqft'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="available" <?= $property['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="rented" <?= $property['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                                <option value="inactive" <?= $property['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Amenities</h3>
                    <div class="amenities-checks">
                        <label class="check-option">
                            <input type="checkbox" name="is_furnished" value="1" <?= $property['is_furnished'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-couch"></i> Furnished</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_parking" value="1" <?= $property['has_parking'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-car"></i> Parking</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_wifi" value="1" <?= $property['has_wifi'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-wifi"></i> WiFi</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_ac" value="1" <?= $property['has_ac'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-snowflake"></i> AC</span>
                        </label>
                        <label class="check-option">
                            <input type="checkbox" name="has_generator" value="1" <?= $property['has_generator'] ? 'checked' : '' ?>>
                            <span><i class="fas fa-bolt"></i> Generator</span>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Current Images</h3>
                    <?php if (empty($images)): ?>
                        <p class="form-hint">No images uploaded yet.</p>
                    <?php else: ?>
                        <div class="current-images-grid">
                            <?php foreach ($images as $img): ?>
                                <div class="current-image">
                                    <img src="<?= e(image_url($img['image_path'])) ?>" alt="">
                                    <a href="<?= SITE_URL ?>/api/delete-image.php?id=<?= $img['id'] ?>&property_id=<?= $id ?>" class="remove-image" onclick="return confirm('Delete this image?')"><i class="fas fa-times"></i></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <h4>Add More Images</h4>
                    <div class="image-upload-area" id="imageUploadArea">
                        <input type="file" name="images[]" id="imageInput" accept="image/*" multiple hidden>
                        <label for="imageInput" class="upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload more images</span>
                            <small>or drag and drop (JPG, PNG, WebP) — up to 10</small>
                        </label>
                    </div>
                    <small id="imageCountLabel" class="form-hint" style="text-align:center;display:block;margin-top:8px;"></small>
                    <div class="image-preview-grid" id="imagePreviewGrid"></div>
                </div>

                <div class="form-actions">
                    <a href="<?= SITE_URL ?>/owner-dashboard.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
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
function generateDescription() {
    var btn = document.getElementById('generateDescBtn');
    var btnText = document.getElementById('generateBtnText');
    var regenBtn = document.getElementById('regenerateDescBtn');
    var textarea = document.getElementById('description');

    btn.disabled = true;
    btnText.textContent = 'Generating...';

    var data = {
        title: document.getElementById('title').value,
        property_type: document.getElementById('property_type').value,
        city: document.getElementById('city').value,
        area: document.getElementById('area').value,
        address: document.getElementById('address').value,
        bedrooms: document.getElementById('bedrooms').value,
        bathrooms: document.getElementById('bathrooms').value,
        area_sqft: document.getElementById('area_sqft').value,
        price: document.getElementById('price').value,
        price_period: document.getElementById('price_period').value,
        price_per_day: document.getElementById('price_per_day') ? document.getElementById('price_per_day').value : '',
        is_furnished: document.querySelector('input[name="is_furnished"]') && document.querySelector('input[name="is_furnished"]').checked ? 1 : 0,
        has_parking: document.querySelector('input[name="has_parking"]') && document.querySelector('input[name="has_parking"]').checked ? 1 : 0,
        has_wifi: document.querySelector('input[name="has_wifi"]') && document.querySelector('input[name="has_wifi"]').checked ? 1 : 0,
        has_ac: document.querySelector('input[name="has_ac"]') && document.querySelector('input[name="has_ac"]').checked ? 1 : 0,
        has_generator: document.querySelector('input[name="has_generator"]') && document.querySelector('input[name="has_generator"]').checked ? 1 : 0
    };

    fetch(SITE_URL + '/api/generate-description.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false;
        btnText.textContent = 'Generate with AI';
        if (res.error) {
            alert('Error: ' + res.error);
        } else {
            textarea.value = res.description;
            btn.style.display = 'none';
            regenBtn.style.display = 'inline-flex';
        }
    })
    .catch(function() {
        btn.disabled = false;
        btnText.textContent = 'Generate with AI';
        alert('Failed to generate description. Please try again.');
    });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>