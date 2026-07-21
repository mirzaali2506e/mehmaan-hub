<?php
require_once __DIR__ . '/../includes/functions.php';
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

flash('success', 'Booking ' . $newStatus . ' successfully.');
redirect($isOwner ? '/owner-dashboard.php' : '/dashboard.php');
