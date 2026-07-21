<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

$user = require_login();

$propertyId = (int)($_GET['property_id'] ?? 0);
$property = get_property_by_id($propertyId);

if (!$property) {
    flash('error', 'Property not found.');
    redirect('/properties.php');
}

if ($property['owner_id'] == $user['id']) {
    flash('error', 'You cannot book your own property.');
    redirect('/property-details.php?id=' . $propertyId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $bookingMode = $_POST['booking_mode'] ?? 'month';
    $notes = trim($_POST['notes'] ?? '');

    if (!$startDate || !$endDate) {
        flash('error', 'Please select booking dates.');
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        flash('error', 'End date must be after start date.');
    } else {
        $days = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400);
        $price = $property['price'];
        $pricePerDay = $property['price_per_day'];
        $period = $property['price_period'];

        if ($period === 'per_day') {
            $totalAmount = $price * $days;
        } elseif ($period === 'per_month') {
            $months = max(1, ceil($days / 30));
            $totalAmount = $price * $months;
        } else {
            if ($bookingMode === 'day') {
                $dailyRate = $pricePerDay !== null ? $pricePerDay : $price;
                $totalAmount = $dailyRate * $days;
            } else {
                $months = max(1, ceil($days / 30));
                $totalAmount = $price * $months;
            }
        }

        $stmt = db()->prepare('INSERT INTO bookings (property_id, tenant_id, start_date, end_date, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iissds', $propertyId, $user['id'], $startDate, $endDate, $totalAmount, $notes);

        if ($stmt->execute()) {
            flash('success', 'Booking request sent! The owner will confirm shortly.');
            redirect('/dashboard.php');
        } else {
            flash('error', 'Failed to create booking.');
        }
    }
}

$days = 0;
$totalAmount = 0;
$previewStart = $_GET['start_date'] ?? '';
$previewEnd = $_GET['end_date'] ?? '';
if ($previewStart && $previewEnd && strtotime($previewEnd) >= strtotime($previewStart)) {
    $days = max(1, (strtotime($previewEnd) - strtotime($previewStart)) / 86400);
    $period = $property['price_period'];
    if ($period === 'per_day') {
        $totalAmount = $property['price'] * $days;
    } elseif ($period === 'both') {
        $dailyRate = $property['price_per_day'] !== null ? $property['price_per_day'] : $property['price'];
        $totalAmount = $dailyRate * $days;
    } else {
        $months = max(1, ceil($days / 30));
        $totalAmount = $property['price'] * $months;
    }
}

$period = $property['price_period'];
if ($period === 'per_day') {
    $priceLabel = 'Rs ' . number_format($property['price']) . '/day';
} elseif ($period === 'both') {
    $priceLabel = 'Rs ' . number_format($property['price']) . '/month';
    if ($property['price_per_day'] !== null) {
        $priceLabel .= ' &middot; Rs ' . number_format($property['price_per_day']) . '/day';
    }
} else {
    $priceLabel = 'Rs ' . number_format($property['price']) . '/month';
}

$pageTitle = 'Book Property';
include __DIR__ . '/includes/header.php';
?>

<div class="form-page">
    <div class="container">
        <div class="form-container narrow">
            <div class="form-header">
                <h1>Book Property</h1>
                <p><?= e($property['title']) ?></p>
            </div>

            <div class="booking-property-info">
                <?php $img = get_primary_image($propertyId); ?>
                <?php if ($img): ?>
                    <img src="<?= e(image_url($img)) ?>" alt="<?= e($property['title']) ?>">
                <?php endif; ?>
                <div>
                    <h3><?= e($property['title']) ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?= e($property['address'] . ', ' . $property['city']) ?></p>
                    <p class="booking-price"><?= $priceLabel ?></p>
                </div>
            </div>

            <form method="POST" class="booking-form">
                <?= csrf_field() ?>
                <?php if ($period === 'both'): ?>
                <div class="form-group">
                    <label for="booking_mode">Booking Type <span class="required">*</span></label>
                    <select id="booking_mode" name="booking_mode" onchange="updateTotal()">
                        <option value="month">Monthly</option>
                        <option value="day">Daily</option>
                    </select>
                </div>
                <?php elseif ($period === 'per_day'): ?>
                <input type="hidden" name="booking_mode" value="day">
                <?php else: ?>
                <input type="hidden" name="booking_mode" value="month">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="start_date">Start Date <span class="required">*</span></label>
                        <input type="date" id="start_date" name="start_date" required onchange="updateTotal()">
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date <span class="required">*</span></label>
                        <input type="date" id="end_date" name="end_date" required onchange="updateTotal()">
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any special requests..."></textarea>
                </div>

                <div class="booking-summary" id="bookingSummary">
                    <h3>Booking Summary</h3>
                    <div class="summary-row" id="rateRow">
                        <span id="rateLabel">Monthly Rent</span>
                        <span id="rateValue"><?= format_price($property['price']) ?></span>
                    </div>
                    <div class="summary-row" id="durationRow" style="display:none;">
                        <span>Duration</span>
                        <span id="durationText">1 month</span>
                    </div>
                    <div class="summary-row summary-total" id="totalRow" style="display:none;">
                        <span>Total Amount</span>
                        <span id="totalText"><?= format_price(0) ?></span>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= SITE_URL ?>/property-details.php?id=<?= $propertyId ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTotal() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const durationRow = document.getElementById('durationRow');
    const totalRow = document.getElementById('totalRow');
    const durationText = document.getElementById('durationText');
    const totalText = document.getElementById('totalText');
    const rateLabel = document.getElementById('rateLabel');
    const rateValue = document.getElementById('rateValue');
    const price = <?= $property['price'] ?>;
    const pricePerDay = <?= $property['price_per_day'] !== null ? $property['price_per_day'] : 'null' ?>;
    const period = '<?= $period ?>';
    let mode = 'month';
    const modeEl = document.getElementById('booking_mode');
    if (modeEl) mode = modeEl.value;

    if (period === 'per_day') {
        rateLabel.textContent = 'Daily Rate';
        rateValue.textContent = 'Rs ' + price.toLocaleString();
    } else if (period === 'both') {
        if (mode === 'day') {
            const daily = pricePerDay !== null ? pricePerDay : price;
            rateLabel.textContent = 'Daily Rate';
            rateValue.textContent = 'Rs ' + daily.toLocaleString();
        } else {
            rateLabel.textContent = 'Monthly Rent';
            rateValue.textContent = 'Rs ' + price.toLocaleString();
        }
    } else {
        rateLabel.textContent = 'Monthly Rent';
        rateValue.textContent = 'Rs ' + price.toLocaleString();
    }

    if (start && end) {
        const days = Math.max(1, (new Date(end) - new Date(start)) / 86400000);
        let total, label;
        if (period === 'per_day' || (period === 'both' && mode === 'day')) {
            const daily = (period === 'both' && pricePerDay !== null) ? pricePerDay : price;
            total = daily * days;
            label = days + ' day' + (days > 1 ? 's' : '');
        } else {
            const months = Math.max(1, Math.ceil(days / 30));
            total = price * months;
            label = months + ' month' + (months > 1 ? 's' : '');
        }
        durationRow.style.display = 'flex';
        totalRow.style.display = 'flex';
        durationText.textContent = label;
        totalText.textContent = 'Rs ' + total.toLocaleString();
    } else {
        durationRow.style.display = 'none';
        totalRow.style.display = 'none';
    }
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
