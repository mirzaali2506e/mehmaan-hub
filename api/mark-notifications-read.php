<?php
require_once __DIR__ . '/../includes/functions.php';
$user = require_login();
header('Content-Type: application/json');
mark_notifications_read($user['id']);
echo json_encode(['success' => true]);
