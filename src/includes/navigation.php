<?php
/**
 * Navigation Helper Functions
 * Menyediakan fungsi untuk navigasi yang konsisten
 */

/**
 * Base URL untuk aplikasi
 */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // Hapus trailing slash kecuali untuk root
    $base = rtrim($protocol . '://' . $host . $script_name, '/');
    
    if ($path) {
        return $base . '/' . ltrim($path, '/');
    }
    
    return $base;
}

/**
 * Redirect ke halaman tertentu
 */
function redirect_to($page, $params = []) {
    $url = base_url('index.php?page=' . $page);
    
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    
    header('Location: ' . $url);
    exit;
}

/**
 * Cek apakah user sudah login sebagai admin
 */
function require_admin() {
    if (!isset($_SESSION['admin'])) {
        redirect_to('login');
    }
}

/**
 * Cek apakah user sudah login sebagai user
 */
function require_user() {
    if (!isset($_SESSION['user'])) {
        redirect_to('login');
    }
}

/**
 * Generate URL untuk asset
 */
function asset_url($path) {
    return base_url('public/' . ltrim($path, '/'));
}

/**
 * Generate URL untuk upload
 */
function upload_url($filename) {
    return base_url('public/uploads/' . $filename);
}

/**
 * Logout function
 */
function logout() {
    session_destroy();
    redirect_to('home');
}

/**
 * Get current page
 */
function current_page() {
    return $_GET['page'] ?? 'home';
}

/**
 * Check if current page matches
 */
function is_current_page($page) {
    return current_page() === $page;
}
?>