<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';

require_admin();
$admin = $_SESSION['admin'];

// Prepare content
ob_start();
?>
<style>
  .welcome-section {
    text-align: center;
    margin-bottom: 3rem;
  }

  .welcome-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    max-width: 600px;
    margin: 0 auto;
  }

  .admin-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .admin-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f5b7c2, #e890a8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .admin-details h3 {
    font-size: 1.5rem;
    color: #2e4e2d;
    margin-bottom: 0.25rem;
  }

  .admin-details p {
    color: #6c757d;
    font-size: 0.9rem;
  }

  .welcome-message {
    font-size: 1rem;
    line-height: 1.6;
    color: #6c757d;
    text-align: center;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
  }

  .stat-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #a8e6cf, #f5b7c2);
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
  }

  .stat-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  }

  .stat-icon.menu { background: linear-gradient(135deg, #a8e6cf, #88d8a3); }
  .stat-icon.transaction { background: linear-gradient(135deg, #84fab0, #8fd3f4); }
  .stat-icon.report { background: linear-gradient(135deg, #ffd89b, #19547b); }
  .stat-icon.testimonial { background: linear-gradient(135deg, #f5b7c2, #e890a8); }

  .stat-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2e4e2d;
    margin-bottom: 0.75rem;
  }

  .stat-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
  }

  .action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #a8e6cf, #88d8a3);
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(168, 230, 207, 0.3);
  }

  .action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(168, 230, 207, 0.4);
    color: white;
  }

  @media (max-width: 768px) {
    .stats-grid {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .welcome-card {
      padding: 1.5rem;
      margin: 1rem;
    }

    .admin-info {
      flex-direction: column;
      text-align: center;
    }
  }
</style>

<div class="welcome-section">
  <div class="welcome-card">
    <div class="admin-info">
      <div class="admin-avatar">
        <?= strtoupper(substr($admin, 0, 1)) ?>
      </div>
      <div class="admin-details">
        <h3><?= htmlspecialchars($admin) ?></h3>
        <p>Administrator • Login: <?= date('d M Y, H:i') ?></p>
      </div>
    </div>
    
    <p class="welcome-message">
      💚 Sistem manajemen yang powerful untuk mengelola menu, transaksi, dan testimoni pelanggan. 
      Mari wujudkan pengalaman terbaik untuk pelanggan Nun's Dimsum! 💗
    </p>
  </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon menu">
      <i class="fas fa-utensils"></i>
    </div>
    <h3 class="stat-title">Kelola Menu</h3>
    <p class="stat-description">
      Tambah, edit, dan hapus menu dimsum. Atur harga dan deskripsi produk dengan mudah.
    </p>
    <a href="<?= base_url('index.php?page=admin&sub=menu') ?>" class="action-button">
      <i class="fas fa-arrow-right"></i> Kelola Sekarang
    </a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon transaction">
      <i class="fas fa-receipt"></i>
    </div>
    <h3 class="stat-title">Data Transaksi</h3>
    <p class="stat-description">
      Pantau semua transaksi pelanggan dan update status pesanan secara real-time.
    </p>
    <a href="<?= base_url('index.php?page=admin&sub=transaksi') ?>" class="action-button">
      <i class="fas fa-arrow-right"></i> Lihat Transaksi
    </a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon report">
      <i class="fas fa-chart-line"></i>
    </div>
    <h3 class="stat-title">Laporan Penjualan</h3>
    <p class="stat-description">
      Analisis mendalam tentang performa penjualan dan menu terlaris.
    </p>
    <a href="<?= base_url('index.php?page=admin&sub=laporan') ?>" class="action-button">
      <i class="fas fa-arrow-right"></i> Lihat Laporan
    </a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon testimonial">
      <i class="fas fa-comments"></i>
    </div>
    <h3 class="stat-title">Testimoni Pelanggan</h3>
    <p class="stat-description">
      Baca feedback dan testimoni dari pelanggan yang puas dengan layanan.
    </p>
    <a href="<?= base_url('index.php?page=admin&sub=testimoni') ?>" class="action-button">
      <i class="fas fa-arrow-right"></i> Baca Testimoni
    </a>
  </div>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('🏠 Dashboard Admin', $content, 'dashboard');
?>
