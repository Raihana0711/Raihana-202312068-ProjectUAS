<?php
/**
 * Nun's Dimsum - Main Application Entry Point
 * 
 * @author Raihana
 * @version 1.0
 */

// Start session
session_start();

// Define application constants
define('APP_NAME', "Nun's Dimsum");
define('APP_VERSION', '1.0.0');
define('BASE_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');
define('SRC_PATH', BASE_PATH . '/src');

// Include configuration
require_once SRC_PATH . '/includes/config.php';

// Simple routing
$request = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

switch ($request) {
    case 'home':
        include SRC_PATH . '/views/public/home.php';
        break;
        
    case 'login':
        include SRC_PATH . '/views/auth/login.php';
        break;
        
    case 'register':
        include SRC_PATH . '/views/auth/register.php';
        break;
        
    case 'admin':
        require_admin();
        $subpage = $_GET['sub'] ?? 'dashboard';
        switch ($subpage) {
            case 'dashboard':
                include SRC_PATH . '/views/admin/dashboard.php';
                break;
            case 'menu':
                include SRC_PATH . '/views/admin/menu.php';
                break;
            case 'tambah_menu':
                include SRC_PATH . '/views/admin/tambah_menu.php';
                break;
            case 'edit_menu':
                include SRC_PATH . '/views/admin/edit_menu.php';
                break;
            case 'hapus_menu':
                include SRC_PATH . '/views/admin/hapus_menu.php';
                break;
            case 'transaksi':
                include SRC_PATH . '/views/admin/transaksi.php';
                break;
            case 'laporan':
                include SRC_PATH . '/views/admin/laporan.php';
                break;
            case 'testimoni':
                include SRC_PATH . '/views/admin/testimoni.php';
                break;
            case 'security':
                include SRC_PATH . '/views/admin/security_monitor.php';
                break;
            default:
                include SRC_PATH . '/views/admin/dashboard.php';
                break;
        }
        break;
        
    case 'user':
        require_user();
        $subpage = $_GET['sub'] ?? 'dashboard';
        switch ($subpage) {
            case 'dashboard':
                include SRC_PATH . '/views/user/dashboard.php';
                break;
            case 'keranjang':
                include SRC_PATH . '/views/user/keranjang.php';
                break;
            case 'daftar_transaksi':
                include SRC_PATH . '/views/user/daftar_transaksi.php';
                break;
            case 'testimoni':
                include SRC_PATH . '/views/user/testimoni.php';
                break;
            default:
                include SRC_PATH . '/views/user/dashboard.php';
                break;
        }
        break;
        
    case 'logout':
        logout();
        break;
        
    case '404':
        http_response_code(404);
        include SRC_PATH . '/views/public/404.php';
        break;
        
    default:
        include SRC_PATH . '/views/public/404.php';
        break;
}
?>
