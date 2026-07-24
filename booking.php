<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mail.php';
$user = current_user();
if (!$user) {
    flash('error', 'Please log in to book a property.');
    redirect('/login.php');
}

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

if (has_user_booked_property($user['id'], $propertyId)) {
    flash('error', 'You have already booked this property.');
    redirect('/property-details.php?id=' . $propertyId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $bookingMode = $_POST['booking_mode'] ?? 'month';
    $numMonths = (int)($_POST['num_months'] ?? 1);
    $notes = trim($_POST['notes'] ?? '');

    if (!$startDate) {
        flash('error', 'Please select a start date.');
    } elseif (strtotime($startDate) < strtotime(date('Y-m-d'))) {
        flash('error', 'Start date cannot be in the past.');
    } elseif ($bookingMode === 'month' && $numMonths < 1) {
        flash('error', 'Please enter at least 1 month.');
    } elseif ($bookingMode === 'day' && !$endDate) {
        flash('error', 'Please select an end date.');
    } elseif ($bookingMode === 'day' && $endDate && strtotime($endDate) < strtotime($startDate)) {
        flash('error', 'End date must be after start date.');
    } elseif ($bookingMode === 'day' && $endDate && strtotime($endDate) < strtotime(date('Y-m-d'))) {
        flash('error', 'End date cannot be in the past.');
    } else {
        $price = $property['price'];
        $pricePerDay = $property['price_per_day'];
        $period = $property['price_period'];

        if ($bookingMode === 'month') {
            $numMonths = max(1, $numMonths);
            $totalAmount = $price * $numMonths;
            $endDate = date('Y-m-d', strtotime("+$numMonths months", strtotime($startDate)));
        } else {
            $days = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400);
            $dailyRate = ($pricePerDay !== null) ? $pricePerDay : $price;
            $totalAmount = $dailyRate * $days;
        }

        $overlapStmt = db()->prepare("SELECT b.start_date, b.end_date FROM bookings b WHERE b.property_id = ? AND b.status = 'confirmed' AND b.start_date < ? AND b.end_date > ?");
        $overlapStmt->bind_param('iss', $propertyId, $endDate, $startDate);
        $overlapStmt->execute();
        $overlapResult = $overlapStmt->get_result();

        if ($overlapResult->num_rows > 0) {
            $overlap = $overlapResult->fetch_assoc();
            flash('error', 'This property is already booked from ' . date('M d, Y', strtotime($overlap['start_date'])) . ' to ' . date('M d, Y', strtotime($overlap['end_date'])) . '. Please choose different dates.');
        } else {
            $stmt = db()->prepare('INSERT INTO bookings (property_id, tenant_id, start_date, end_date, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iissds', $propertyId, $user['id'], $startDate, $endDate, $totalAmount, $notes);

            if ($stmt->execute()) {
                $bookingId = $stmt->insert_id;

                $ownerStmt = db()->prepare('SELECT u.id, u.name, u.email, u.phone FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?');
                $ownerStmt->bind_param('i', $propertyId);
                $ownerStmt->execute();
                $owner = $ownerStmt->get_result()->fetch_assoc();

                if ($owner) {
                    $notifTitle = 'New Booking Request';
                    $notifMsg = $user['name'] . ' requested to book "' . $property['title'] . '" from ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)) . '.';
                    create_notification($owner['id'], 'booking_request', $notifTitle, $notifMsg, '/owner-dashboard.php');

                    $emailBody = '<p>You have received a new booking request on <strong>' . SITE_NAME . '</strong>.</p>' .
                        '<p><strong>Tenant:</strong> ' . e($user['name']) . '<br>' .
                        '<strong>Property:</strong> ' . e($property['title']) . '<br>' .
                        '<strong>Dates:</strong> ' . date('M d, Y', strtotime($startDate)) . ' to ' . date('M d, Y', strtotime($endDate)) . '<br>' .
                        '<strong>Total:</strong> Rs ' . number_format($totalAmount) . '</p>' .
                        '<p>Please log in to your dashboard to confirm or cancel this request.</p>' .
                        '<p><a href="' . SITE_URL . '/owner-dashboard.php" style="display:inline-block;background:#2563eb;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;">View Booking</a></p>';
                    send_notification_email($owner['email'], $owner['name'], 'New Booking Request - ' . SITE_NAME, $emailBody);
                }

                flash('success', 'Booking request sent! The owner will confirm shortly.');
                redirect('/dashboard.php');
            } else {
                flash('error', 'Failed to create booking.');
            }
        }
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
                <?php if ($period === 'both'): ?>
                <div class="form-group">
                    <label for="booking_mode">Booking Type <span class="required">*</span></label>
                    <select id="booking_mode" name="booking_mode" onchange="toggleBookingMode()">
                        <option value="month">Monthly</option>
                        <option value="day">Daily</option>
                    </select>
                </div>
                <?php elseif ($period === 'per_day'): ?>
                <input type="hidden" name="booking_mode" value="day">
                <?php else: ?>
                <input type="hidden" name="booking_mode" value="month">
                <?php endif; ?>

                <!-- Monthly booking: start date + number of months -->
                <div id="monthlyFields" class="form-grid">
                    <div class="form-group">
                        <label for="start_date">Start Date <span class="required">*</span></label>
                        <input type="date" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>" required onchange="updateTotal()">
                    </div>
                    <div class="form-group">
                        <label for="num_months">Number of Months <span class="required">*</span></label>
                        <input type="number" id="num_months" name="num_months" min="1" value="1" required onchange="updateTotal()">
                    </div>
                </div>

                <!-- Daily booking: check-in + check-out -->
                <div id="dailyFields" class="form-grid" style="display:none;">
                    <div class="form-group">
                        <label for="start_date_day">Check-In Date <span class="required">*</span></label>
                        <input type="date" id="start_date_day" name="start_date_day" min="<?= date('Y-m-d') ?>" onchange="syncStartDate()">
                    </div>
                    <div class="form-group">
                        <label for="end_date">Check-Out Date <span class="required">*</span></label>
                        <input type="date" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>" onchange="updateTotal()">
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
function toggleBookingMode() {
    const modeEl = document.getElementById('booking_mode');
    const mode = modeEl ? modeEl.value : 'month';
    const monthlyFields = document.getElementById('monthlyFields');
    const dailyFields = document.getElementById('dailyFields');
    const startInput = document.getElementById('start_date');
    const startDayInput = document.getElementById('start_date_day');

    if (mode === 'day') {
        monthlyFields.style.display = 'none';
        dailyFields.style.display = 'grid';
        // Transfer date value
        if (startInput.value) startDayInput.value = startInput.value;
        // start_date is still the real hidden field — sync it
        startInput.name = 'start_date';
        startDayInput.name = 'start_date_day';
    } else {
        monthlyFields.style.display = 'grid';
        dailyFields.style.display = 'none';
        if (startDayInput.value) startInput.value = startDayInput.value;
    }
    updateTotal();
}

function syncStartDate() {
    const startDayInput = document.getElementById('start_date_day');
    const startInput = document.getElementById('start_date');
    if (startDayInput.value) startInput.value = startDayInput.value;
    updateTotal();
}

function updateTotal() {
    const modeEl = document.getElementById('booking_mode');
    const mode = modeEl ? modeEl.value : 'month';
    const start = document.getElementById('start_date').value;
    const durationRow = document.getElementById('durationRow');
    const totalRow = document.getElementById('totalRow');
    const durationText = document.getElementById('durationText');
    const totalText = document.getElementById('totalText');
    const rateLabel = document.getElementById('rateLabel');
    const rateValue = document.getElementById('rateValue');
    const price = <?= $property['price'] ?>;
    const pricePerDay = <?= $property['price_per_day'] !== null ? $property['price_per_day'] : 'null' ?>;
    const period = '<?= $period ?>';

    // Determine effective mode
    let effectiveMode = mode;
    if (period === 'per_day') effectiveMode = 'day';
    if (period === 'per_month') effectiveMode = 'month';

    if (effectiveMode === 'day') {
        const daily = pricePerDay !== null ? pricePerDay : price;
        rateLabel.textContent = 'Daily Rate';
        rateValue.textContent = 'Rs ' + daily.toLocaleString();
    } else {
        rateLabel.textContent = 'Monthly Rent';
        rateValue.textContent = 'Rs ' + price.toLocaleString();
    }

    if (effectiveMode === 'month' && start) {
        const numMonths = Math.max(1, parseInt(document.getElementById('num_months').value) || 1);
        const total = price * numMonths;
        durationRow.style.display = 'flex';
        totalRow.style.display = 'flex';
        durationText.textContent = numMonths + ' month' + (numMonths > 1 ? 's' : '');
        totalText.textContent = 'Rs ' + total.toLocaleString();
    } else if (effectiveMode === 'day' && start) {
        const end = document.getElementById('end_date').value;
        if (end) {
            const days = Math.max(1, (new Date(end) - new Date(start)) / 86400000);
            const daily = pricePerDay !== null ? pricePerDay : price;
            const total = daily * days;
            durationRow.style.display = 'flex';
            totalRow.style.display = 'flex';
            durationText.textContent = days + ' day' + (days > 1 ? 's' : '');
            totalText.textContent = 'Rs ' + total.toLocaleString();
        } else {
            durationRow.style.display = 'none';
            totalRow.style.display = 'none';
        }
    } else {
        durationRow.style.display = 'none';
        totalRow.style.display = 'none';
    }
}

// On page load: if period forces a mode, set it up
(function() {
    const period = '<?= $period ?>';
    if (period === 'per_day') {
        document.getElementById('monthlyFields').style.display = 'none';
        document.getElementById('dailyFields').style.display = 'grid';
    } else if (period === 'per_month') {
        document.getElementById('monthlyFields').style.display = 'grid';
        document.getElementById('dailyFields').style.display = 'none';
    }
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
