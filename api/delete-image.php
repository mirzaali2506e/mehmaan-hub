<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

$user = require_role('owner');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Invalid request method.');
    redirect('/owner-dashboard.php');
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$propertyId = (int)($_POST['property_id'] ?? 0);

$stmt = db()->prepare('SELECT pi.*, p.owner_id FROM property_images pi JOIN properties p ON pi.property_id = p.id WHERE pi.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$img = $stmt->get_result()->fetch_assoc();

if (!$img || ($img['owner_id'] != $user['id'] && $user['role'] !== 'admin')) {
    flash('error', 'Image not found.');
    redirect('/edit-property.php?id=' . $propertyId);
}

$path = UPLOAD_DIR . $img['image_path'];
if (file_exists($path)) {
    unlink($path);
}

$stmt = db()->prepare('DELETE FROM property_images WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

log_activity($user['id'], 'delete_image', 'property', $img['property_id']);
flash('success', 'Image deleted.');
redirect('/edit-property.php?id=' . $propertyId);
