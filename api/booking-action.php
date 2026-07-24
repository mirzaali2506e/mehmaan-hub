<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mail.php';
$user = require_login();

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!in_array($action, ['confirm', 'cancel', 'complete'])) {
    flash('error', 'Invalid action.');
    redirect('/dashboard.php');
}

$stmt = db()->prepare('SELECT b.*, p.owner_id FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    flash('error', 'Booking not found.');
    redirect('/dashboard.php');
}

$isOwner = $booking['owner_id'] == $user['id'] || $user['role'] === 'admin';
$isTenant = $booking['tenant_id'] == $user['id'];

if (!$isOwner && !$isTenant) {
    flash('error', 'Access denied.');
    redirect('/dashboard.php');
}

$statusMap = [
    'confirm' => 'confirmed',
    'cancel' => 'cancelled',
    'complete' => 'completed'
];

$newStatus = $statusMap[$action];

if ($action === 'confirm' && !$isOwner) {
    flash('error', 'Only the owner can confirm bookings.');
    redirect('/dashboard.php');
}

$stmt = db()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
$stmt->bind_param('si', $newStatus, $id);
$stmt->execute();

if ($action === 'confirm') {
    $stmt = db()->prepare("UPDATE properties SET status = 'rented' WHERE id = ?");
    $stmt->bind_param('i', $booking['property_id']);
    $stmt->execute();
} elseif ($action === 'cancel' || $action === 'complete') {
    $stmt = db()->prepare("UPDATE properties SET status = 'available' WHERE id = ?");
    $stmt->bind_param('i', $booking['property_id']);
    $stmt->execute();
}

$tenantStmt = db()->prepare('SELECT u.id, u.name, u.email FROM bookings b JOIN users u ON b.tenant_id = u.id WHERE b.id = ?');
$tenantStmt->bind_param('i', $id);
$tenantStmt->execute();
$tenant = $tenantStmt->get_result()->fetch_assoc();

$propStmt = db()->prepare('SELECT title FROM properties WHERE id = ?');
$propStmt->bind_param('i', $booking['property_id']);
$propStmt->execute();
$propTitle = $propStmt->get_result()->fetch_assoc()['title'] ?? 'Property';

if ($tenant) {
    if ($action === 'confirm') {
        $notifTitle = 'Booking Confirmed!';
        $notifMsg = 'Your booking for "' . $propTitle . '" has been confirmed by the owner. Dates: ' . date('M d, Y', strtotime($booking['start_date'])) . ' to ' . date('M d, Y', strtotime($booking['end_date'])) . '.';
        create_notification($tenant['id'], 'booking_confirmed', $notifTitle, $notifMsg, '/dashboard.php');
        $emailBody = '<p>Great news! Your booking request has been <strong>confirmed</strong>.</p>' .
            '<p><strong>Property:</strong> ' . e($propTitle) . '<br>' .
            '<strong>Dates:</strong> ' . date('M d, Y', strtotime($booking['start_date'])) . ' to ' . date('M d, Y', strtotime($booking['end_date'])) . '</p>' .
            '<p><a href="' . SITE_URL . '/dashboard.php" style="display:inline-block;background:#2563eb;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;">View Booking</a></p>';
        send_notification_email($tenant['email'], $tenant['name'], 'Booking Confirmed - ' . SITE_NAME, $emailBody);
    } elseif ($action === 'cancel' && $isOwner) {
        $notifTitle = 'Booking Cancelled';
        $notifMsg = 'Your booking request for "' . $propTitle . '" was cancelled by the owner.';
        create_notification($tenant['id'], 'booking_cancelled', $notifTitle, $notifMsg, '/dashboard.php');
        $emailBody = '<p>Your booking request for <strong>' . e($propTitle) . '</strong> was cancelled by the owner.</p>' .
            '<p>You can browse other properties on our site.</p>';
        send_notification_email($tenant['email'], $tenant['name'], 'Booking Cancelled - ' . SITE_NAME, $emailBody);
    }
}

flash('success', 'Booking ' . $newStatus . ' successfully.');
redirect($isOwner ? '/owner-dashboard.php' : '/dashboard.php');
