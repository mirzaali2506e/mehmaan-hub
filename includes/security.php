<?php
require_once __DIR__ . '/db.php';

// Security headers
function security_headers() {
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com cdncloudflare.com cdnjs.cloudflare.com; font-src 'self' fonts.gstatic.com; img-src 'self' data: blob: https:; connect-src 'self'; frame-ancestors 'self'");
}

// CSRF token generation
function csrf_token() {
    start_session_safe();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify() {
    start_session_safe();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        flash('error', 'Security token expired. Please try again.');
        redirect($_SERVER['REQUEST_URI'] ?? '/index.php');
    }
}

// For AJAX/JSON endpoints - returns false instead of redirecting
function csrf_verify_ajax() {
    start_session_safe();
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Rate limiting using database
function rate_limit($key, $maxAttempts, $windowSeconds) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $identifier = $key . ':' . $ip;
    $windowStart = date('Y-m-d H:i:s', time() - $windowSeconds);

    $stmt = db()->prepare('SELECT COUNT(*) as cnt FROM rate_limits WHERE identifier = ? AND created_at > ?');
    $stmt->bind_param('ss', $identifier, $windowStart);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_assoc()['cnt'];

    if ($count >= $maxAttempts) {
        return false;
    }

    $stmt = db()->prepare('INSERT INTO rate_limits (identifier, created_at) VALUES (?, NOW())');
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    return true;
}

// Activity logging for audit trail
function log_activity($userId, $action, $entityType = null, $entityId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $stmt = db()->prepare('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ississ', $userId, $action, $entityType, $entityId, $ip, $ua);
    $stmt->execute();
}

// Secure file upload validation
function validate_and_upload_image($file, $uploadDir, $prefix = 'img_') {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['size'] > $maxSize) return null;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimes[$mime])) return null;

    // Double-check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) return null;
    if ($allowedMimes[$mime] !== $ext && !($mime === 'image/jpeg' && in_array($ext, ['jpg', 'jpeg']))) {
        // Allow jpg/jpeg mismatch
    }

    // Verify it's actually an image
    if (!getimagesize($file['tmp_name'])) return null;

    // Generate secure random filename
    $filename = $prefix . bin2hex(random_bytes(12)) . '.' . $allowedMimes[$mime];

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return $filename;
    }
    return null;
}
