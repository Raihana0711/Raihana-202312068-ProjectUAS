<?php
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/user_layout.php';

// Check if user is logged in, if not use this as landing page
$is_logged_in = isset($_SESSION['user']) && !empty($_SESSION['user']);

if ($is_logged_in) {
  // If logged in, redirect to dashboard
  header('Location: ' . base_url('index.php?page=user&sub=dashboard'));
  exit;
}

// Get menu items for public display
include_once __DIR__ . '/../../includes/koneksi.php';
$menu_query = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu ASC");

// Start output buffering for layout
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nun's Dimsum - Daftar Menu</title>
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
      padding: 20px;
    }
    
    .header {
      text-align: center;
      margin-bottom: 40px;
      background: rgba(255,255,255,0.9);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    }
    
    .logo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      border: 4px solid var(--primary-pink);
      object-fit: cover;
      margin-bottom: 16px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .title {
      font-size: 2.5rem;
      color: var(--dark-green);
      margin-bottom: 8px;
      font-weight: 700;
    }
    
    .subtitle {
      color: #666;
      font-size: 1.1rem;
      margin-bottom: 20px;
    }
    
    .login-prompt {
      background: linear-gradient(135deg, var(--accent-green), var(--primary-green));
      color: white;
      padding: 12px 24px;
      border-radius: 25px;
      display: inline-block;
      margin-top: 16px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(136, 216, 163, 0.4);
    }
    
    .login-prompt:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(136, 216, 163, 0.6);
    }
    
    .menu-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .menu-card {
      background: white;
      border-radius: 20px;
      padding: 24px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 2px solid transparent;
      position: relative;
      overflow: hidden;
    }
    
    .menu-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-pink), var(--accent-green));
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    
    .menu-card:hover::before {
      transform: scaleX(1);
    }
    
    .menu-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 60px rgba(0,0,0,0.15);
      border-color: var(--primary-pink);
    }
    
    .menu-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 16px;
      margin-bottom: 16px;
      transition: transform 0.3s ease;
    }
    
    .menu-card:hover img {
      transform: scale(1.05);
    }
    
    .menu-card h3 {
      font-size: 1.3rem;
      color: var(--dark-green);
      margin-bottom: 8px;
      font-weight: 600;
    }
    
    .menu-card p {
      color: #666;
      font-size: 0.95rem;
      line-height: 1.5;
      margin-bottom: 16px;
      min-height: 45px;
    }
    
    .menu-price {
      font-size: 1.2rem;
      font-weight: bold;
      color: #e74c3c;
      margin-bottom: 16px;
    }
    
    .btn-login-first {
      background: linear-gradient(135deg, var(--primary-pink), var(--secondary-pink));
      color: white;
      text-decoration: none;
      padding: 12px 24px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.95rem;
      text-align: center;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(245, 183, 194, 0.4);
      width: 100%;
      display: block;
    }
    
    .btn-login-first:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(245, 183, 194, 0.6);
    }
    
    @media (max-width: 768px) {
      .menu-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
      
      .menu-card {
        padding: 20px;
      }
      
      .title {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="<?= smart_image_url('nuns.jpg') ?>" alt="Logo Dimsum" class="logo" onerror="this.src='<?= upload_url('default.svg') ?>'">
    <h1 class="title">Nun's Dimsum</h1>
    <p class="subtitle">🥟 Daftar Menu Dimsum Terbaik 🥟</p>
    <p style="color: #888; font-size: 0.95rem;">Silakan login terlebih dahulu untuk dapat memesan</p>
    <a href="<?= base_url('index.php?page=login') ?>" class="login-prompt">
      <i class="fas fa-sign-in-alt"></i> Login / Register
    </a>
  </div>
  
  <div class="menu-grid">
    <?php if ($menu_query && mysqli_num_rows($menu_query) > 0): ?>
      <?php while($menu = mysqli_fetch_assoc($menu_query)): ?>
      <div class="menu-card">
        <img src="<?= smart_image_url($menu['gambar']) ?>" alt="<?= htmlspecialchars($menu['nama_menu']) ?>" onerror="this.src='<?= upload_url('default.svg') ?>'">
        <h3><?= htmlspecialchars($menu['nama_menu']) ?></h3>
        <p><?= htmlspecialchars($menu['deskripsi']) ?></p>
        <div class="menu-price">Rp <?= number_format($menu['harga']) ?></div>
        <a class="btn-login-first" href="<?= base_url('index.php?page=login') ?>">
          <i class="fas fa-lock"></i> Login untuk Memesan
        </a>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: rgba(255,255,255,0.9); border-radius: 20px;">
        <h3 style="color: var(--dark-green); margin-bottom: 16px;">Menu Belum Tersedia</h3>
        <p style="color: #666;">Silakan coba lagi nanti atau hubungi admin.</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>

<?php
// If not logged in, don't use user layout
echo ob_get_clean();
