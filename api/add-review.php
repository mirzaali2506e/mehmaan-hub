<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

csrf_verify();

$propertyId = (int)($_POST['property_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$comment = trim($_POST['comment'] ?? '');

if (!$propertyId || !$comment) {
    flash('error', 'Please provide a rating and comment.');
    redirect('/property-details.php?id=' . $propertyId);
}

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

$stmt = db()->prepare('INSERT INTO reviews (property_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
$stmt->bind_param('iiis', $propertyId, $user['id'], $rating, $comment);

if ($stmt->execute()) {
    flash('success', 'Review added successfully!');
} else {
    flash('error', 'Failed to add review.');
}
redirect('/property-details.php?id=' . $propertyId);
