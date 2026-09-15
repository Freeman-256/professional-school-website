<?php
/**
 * Helper Functions
 */

// Load environment variables
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        require_once 'UserModel.php';
        $userModel = new User();
        return $userModel->findById($_SESSION['user_id']);
    }
    return null;
}

/**
 * Check if user has role
 */
function hasRole($role) {
    if (isLoggedIn()) {
        return $_SESSION['role'] === $role;
    }
    return false;
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input));
}

/**
 * Format date
 */
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

/**
 * Get alert message
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * Set alert message
 */
function setAlert($type, $message) {
    $_SESSION['alert'] = ['type' => $type, 'message' => $message];
}

/**
 * Get grade letter from score
 */
function getGradeLetter($score) {
    if ($score >= 90) return 'A';
    if ($score >= 80) return 'B';
    if ($score >= 70) return 'C';
    if ($score >= 60) return 'D';
    return 'F';
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Upload file
 */
function uploadFile($file, $destination = 'uploads/') {
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'path' => $filepath, 'filename' => $filename];
    }
    return ['success' => false, 'message' => 'File upload failed'];
}

/**
 * Send email notification
 */
function sendNotification($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@school.com' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get current timestamp
 */
function getCurrentTimestamp() {
    return date('Y-m-d H:i:s');
}

/**
 * Check user permission
 */
function checkPermission($required_role) {
    if (!isLoggedIn()) {
        redirect('/school-website/login');
    }
    
    if ($_SESSION['role'] !== $required_role) {
        redirect('/school-website/');
    }
}

/**
 * Log activity
 */
function logActivity($user_id, $action, $description) {
    global $conn;
    $query = "INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iss', $user_id, $action, $description);
    return $stmt->execute();
}
?>
