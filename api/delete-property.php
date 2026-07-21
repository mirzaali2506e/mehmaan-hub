<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_role('owner');

$id = (int)($_GET['id'] ?? 0);
$property = get_property_by_id($id);

if (!$property || ($property['owner_id'] != $user['id'] && $user['role'] !== 'admin')) {
    flash('error', 'Property not found.');
    redirect('/owner-dashboard.php');
}

$images = get_property_images($id);
foreach ($images as $img) {
    $path = UPLOAD_DIR . $img['image_path'];
    if (file_exists($path)) {
        unlink($path);
    }
}

$stmt = db()->prepare('DELETE FROM properties WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

flash('success', 'Property deleted successfully.');
redirect('/owner-dashboard.php');
