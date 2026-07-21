<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
start_session_safe();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!csrf_verify_ajax()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$propertyId) {
    echo json_encode(['success' => false, 'message' => 'Invalid property']);
    exit;
}

if (is_in_wishlist($userId, $propertyId)) {
    $stmt = db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND property_id = ?');
    $stmt->bind_param('ii', $userId, $propertyId);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $stmt = db()->prepare('INSERT INTO wishlist (user_id, property_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $userId, $propertyId);
    $stmt->execute();
    echo json_encode(['success' => true, 'action' => 'added']);
}
