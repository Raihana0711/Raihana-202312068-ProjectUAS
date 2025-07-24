<?php
/**
 * Helper functions for Nun's Dimsum Application
 */

/**
 * Sanitize input to prevent XSS
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect function
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in as admin
 */
function isAdmin() {
    return isset($_SESSION['admin']);
}

/**
 * Check if user is logged in as user
 */
function isUser() {
    return isset($_SESSION['user']);
}

/**
 * Format currency in Rupiah
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Generate unique filename for uploads
 */
function generateFilename($extension) {
    return time() . '-' . uniqid() . '.' . $extension;
}

/**
 * Validate file upload
 */
function validateUpload($file) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        return 'Ekstensi file tidak diizinkan. Hanya jpg, jpeg, png, gif yang diperbolehkan.';
    }
    
    if ($file['size'] > $maxSize) {
        return 'Ukuran file terlalu besar. Maksimal 2MB.';
    }
    
    return true;
}

/**
 * Log activity
 */
function logActivity($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = BASE_PATH . '/logs/activity.log';
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Hash password using PHP's password_hash
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password using PHP's password_verify
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Create admin user with proper password hashing
 */
function createAdminUser($username, $password, $conn) {
    $hashedPassword = hashPassword($password);
    $username = mysqli_real_escape_string($conn, $username);
    
    $query = "INSERT INTO user (username, password, role) VALUES ('$username', '$hashedPassword', 'admin')";
    return mysqli_query($conn, $query);
}

/**
 * Create regular user with proper password hashing
 */
function createUser($username, $password, $conn) {
    $hashedPassword = hashPassword($password);
    $username = mysqli_real_escape_string($conn, $username);
    
    $query = "INSERT INTO user (username, password, role) VALUES ('$username', '$hashedPassword', 'user')";
    return mysqli_query($conn, $query);
}

/**
 * Smart image URL with fallback support
 */
function smart_image_url($filename) {
    if (empty($filename)) {
        return upload_url('default.svg');
    }
    
    $image_path = 'public/uploads/' . $filename;
    
    // Check if original file exists and is not corrupted
    if (file_exists($image_path) && filesize($image_path) > 100) {
        return upload_url($filename);
    }
    
    // Try SVG version
    $svg_name = pathinfo($filename, PATHINFO_FILENAME) . '.svg';
    $svg_path = 'public/uploads/' . $svg_name;
    
    if (file_exists($svg_path)) {
        return upload_url($svg_name);
    }
    
    // Use default SVG
    return upload_url('default.svg');
}

/**
 * Authenticate user login
 */
function authenticateUser($username, $password, $conn) {
    $username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (verifyPassword($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

/**
 * Get cart item count for current user
 */
function getCartCount($user_id, $conn) {
    $query = "SELECT COALESCE(SUM(jumlah), 0) as total FROM keranjang WHERE id_user = '$user_id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Get cart total amount for current user
 */
function getCartTotal($user_id, $conn) {
    $query = "SELECT COALESCE(SUM(k.jumlah * m.harga), 0) as total 
              FROM keranjang k 
              JOIN menu m ON k.id_menu = m.id_menu 
              WHERE k.id_user = '$user_id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}
?>
