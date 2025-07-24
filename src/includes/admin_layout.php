<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/config.php';

function render_admin_layout($page_title, $content, $active_menu = '') {
  if (!isset($_SESSION['admin'])) {
    header("Location: " . base_url('index.php?page=login'));
    exit;
  }
  $admin = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> - Admin Nun's Dimsum</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-green: #a8e6cf;
      --secondary-green: #dcedc8;
      --accent-green: #88d8a3;
      --dark-green: #2e4e2d;
      --primary-pink: #f5b7c2;
      --secondary-pink: #e890a8;
      --light-pink: #fff0f2;
      --white: #ffffff;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
      --shadow-lg: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-pink) 100%);
      min-height: 100vh;
      color: var(--dark-green);
      overflow-x: hidden;
    }
    
    /* Hamburger Menu Button */
    .hamburger-menu {
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 1001;
      width: 50px;
      height: 50px;
      background: white;
      border: none;
      border-radius: 50%;
      box-shadow: var(--shadow-lg);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hamburger-menu:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }
    
    .hamburger-line {
      width: 20px;
      height: 2px;
      background: var(--dark-green);
      border-radius: 2px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hamburger-menu.open .hamburger-line:nth-child(1) {
      transform: translateY(6px) rotate(45deg);
    }
    
    .hamburger-menu.open .hamburger-line:nth-child(2) {
      opacity: 0;
      transform: scaleX(0);
    }
    
    .hamburger-menu.open .hamburger-line:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }
    
    /* Overlay */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(8px);
      opacity: 0;
      pointer-events: none;
      transition: all 0.4s ease;
      z-index: 999;
    }
    
    .overlay.active {
      opacity: 1;
      pointer-events: all;
    }
    
    /* Sidebar Panel */
    .admin-panel {
      position: fixed;
      top: 0;
      left: -350px;
      width: 350px;
      height: 100vh;
      background: linear-gradient(180deg, var(--dark-green) 0%, var(--accent-green) 100%);
      box-shadow: var(--shadow-lg);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 1000;
      overflow-y: auto;
    }
    
    .admin-panel.open {
      left: 0;
    }
    
    .panel-header {
      padding: 2rem 1.5rem;
      text-align: center;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .panel-logo {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      margin: 0 auto 1rem;
      border: 3px solid rgba(255, 255, 255, 0.3);
      object-fit: cover;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .panel-title {
      color: white;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .panel-subtitle {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
      font-weight: 400;
    }
    
    .admin-info {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-top: 1rem;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      backdrop-filter: blur(10px);
    }
    
    .admin-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-pink), var(--secondary-pink));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
      font-weight: bold;
      box-shadow: var(--shadow-md);
    }
    
    .admin-details h4 {
      color: white;
      font-size: 1rem;
      margin-bottom: 0.25rem;
    }
    
    .admin-details p {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.8rem;
    }
    
    .panel-menu {
      padding: 1.5rem 0;
    }
    
    .menu-item {
      display: block;
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      padding: 1rem 1.5rem;
      margin: 0.25rem 1rem;
      border-radius: 12px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .menu-item:hover, .menu-item.active {
      background: rgba(255, 255, 255, 0.15);
      color: white;
      transform: translateX(8px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .menu-item i {
      width: 20px;
      margin-right: 12px;
      font-size: 1rem;
    }
    
    .menu-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
      transition: left 0.5s ease;
    }
    
    .menu-item:hover::before {
      left: 100%;
    }
    
    .logout-section {
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Main Content */
    .main-content {
      padding: 2rem;
      padding-top: 6rem;
      min-height: 100vh;
      transition: all 0.3s ease;
    }
    
    .content-container {
      max-width: 1400px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 24px;
      box-shadow: var(--shadow-lg);
      backdrop-filter: blur(10px);
      overflow: hidden;
    }
    
    .page-header {
      background: linear-gradient(135deg, var(--accent-green) 0%, var(--secondary-pink) 100%);
      padding: 2rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .page-title {
      color: white;
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 2;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .page-subtitle {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
      font-weight: 400;
      position: relative;
      z-index: 2;
    }
    
    .page-content {
      padding: 2rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
      .admin-panel {
        width: 280px;
        left: -280px;
      }
      
      .main-content {
        padding: 1rem;
        padding-top: 5rem;
      }
      
      .page-title {
        font-size: 2rem;
      }
      
      .content-container {
        margin: 0;
        border-radius: 16px;
      }
    }
  </style>
</head>
<body>
  <!-- Hamburger Menu -->
  <button class="hamburger-menu" id="hamburgerMenu">
    <div class="hamburger-line"></div>
    <div class="hamburger-line"></div>
    <div class="hamburger-line"></div>
  </button>
  
  <!-- Overlay -->
  <div class="overlay" id="overlay"></div>
  
  <!-- Admin Panel -->
  <div class="admin-panel" id="adminPanel">
    <div class="panel-header">
      <img src="<?= smart_image_url('nuns.jpg') ?>" alt="Logo Dimsum" class="panel-logo" onerror="this.src='<?= upload_url('default.svg') ?>'">
      <h1 class="panel-title">Nun's Dimsum</h1>
      <p class="panel-subtitle">Admin Dashboard</p>
      
      <div class="admin-info">
        <div class="admin-avatar">
          <?= strtoupper(substr($admin, 0, 1)) ?>
        </div>
        <div class="admin-details">
          <h4><?= htmlspecialchars($admin) ?></h4>
          <p>Administrator</p>
        </div>
      </div>
    </div>
    
    <nav class="panel-menu">
      <a href="<?= base_url('index.php?page=admin') ?>" class="menu-item <?= $active_menu === 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard
      </a>
      <a href="<?= base_url('index.php?page=admin&sub=menu') ?>" class="menu-item <?= $active_menu === 'menu' ? 'active' : '' ?>">
        <i class="fas fa-utensils"></i>
        Kelola Menu
      </a>
      <a href="<?= base_url('index.php?page=admin&sub=transaksi') ?>" class="menu-item <?= $active_menu === 'transaksi' ? 'active' : '' ?>">
        <i class="fas fa-receipt"></i>
        Data Transaksi
      </a>
      <a href="<?= base_url('index.php?page=admin&sub=laporan') ?>" class="menu-item <?= $active_menu === 'laporan' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i>
        Laporan Penjualan
      </a>
            <a href="<?= base_url('index.php?page=admin&sub=testimoni') ?>" 
               class="menu-item <?= $active_menu === 'testimoni' ? 'active' : '' ?>">
                <i class="fas fa-star"></i>
                Testimoni
            </a>
            
            <a href="<?= base_url('index.php?page=admin&sub=security') ?>" 
               class="menu-item <?= $active_menu === 'security' ? 'active' : '' ?>">
                <i class="fas fa-shield-alt"></i>
                Security Monitor
            </a>
      <div class="logout-section">
        <a href="<?= base_url('index.php?page=logout') ?>" class="menu-item">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </a>
      </div>
    </nav>
  </div>
  
  <!-- Main Content -->
  <main class="main-content">
    <div class="content-container">
      <div class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
        <p class="page-subtitle">Sistem manajemen Nun's Dimsum</p>
      </div>
      <div class="page-content">
        <?= $content ?>
      </div>
    </div>
  </main>

  <script>
    // Hamburger Menu Functionality
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const adminPanel = document.getElementById('adminPanel');
    const overlay = document.getElementById('overlay');
    
    function togglePanel() {
      hamburgerMenu.classList.toggle('open');
      adminPanel.classList.toggle('open');
      overlay.classList.toggle('active');
      
      // Prevent body scroll when panel is open
      document.body.style.overflow = adminPanel.classList.contains('open') ? 'hidden' : 'auto';
    }
    
    function closePanel() {
      hamburgerMenu.classList.remove('open');
      adminPanel.classList.remove('open');
      overlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
    
    // Event listeners
    hamburgerMenu.addEventListener('click', togglePanel);
    overlay.addEventListener('click', closePanel);
    
    // Close panel when pressing Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && adminPanel.classList.contains('open')) {
        closePanel();
      }
    });
    
    // Smooth scroll for menu links
    document.querySelectorAll('.menu-item').forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          closePanel();
        }
      });
    });
    
    // Add entrance animation to content
    window.addEventListener('load', function() {
      const contentContainer = document.querySelector('.content-container');
      contentContainer.style.opacity = '0';
      contentContainer.style.transform = 'translateY(20px)';
      contentContainer.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      
      setTimeout(() => {
        contentContainer.style.opacity = '1';
        contentContainer.style.transform = 'translateY(0)';
      }, 100);
    });
  </script>
</body>
</html>
<?php
}
?>
