<?php
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/user_layout.php';

require_user();

$username = $_SESSION['user'];
$get_user = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
$u = mysqli_fetch_assoc($get_user);

$menu = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu ASC");

// Start output buffering to capture content
ob_start();
?>
<!-- Welcome Section -->
<div style="background: rgba(255,255,255,0.8); padding: 20px; border-radius: 16px; margin-bottom: 30px; text-align: center; backdrop-filter: blur(10px); box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
  <h2 style="font-size: 1.8rem; color: var(--dark-green); margin-bottom: 10px;">💚💗 Selamat Datang <?= htmlspecialchars($username) ?>!</h2>
  <p style="color: #666; font-size: 1.1rem;">Yuk ajak orang tersayang untuk berbagi kebahagiaan dengan dimsum favoritmu!</p>
</div>

<!-- Menu Grid -->
<style>
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  padding: 20px 0;
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

.menu-card h4 {
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

.btn-add-cart {
  display: inline-block;
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
}

.btn-add-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 183, 194, 0.6);
  background: linear-gradient(135deg, var(--secondary-pink), var(--primary-pink));
}

@media (max-width: 768px) {
  .menu-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .menu-card {
    padding: 20px;
  }
}
</style>

<div class="menu-grid">
  <?php while($d = mysqli_fetch_assoc($menu)): ?>
  <div class="menu-card">
    <img src="<?= smart_image_url($d['gambar']) ?>" alt="<?= htmlspecialchars($d['nama_menu']) ?>" onerror="this.src='<?= upload_url('default.svg') ?>'">
    <h4><?= htmlspecialchars($d['nama_menu']) ?></h4>
    <p><?= htmlspecialchars($d['deskripsi']) ?></p>
    <div class="menu-price">Rp <?= number_format($d['harga']) ?></div>
    <a class="btn-add-cart" href="<?= base_url('index.php?page=user&sub=keranjang&id=' . $d['id_menu']) ?>">
      <i class="fas fa-plus"></i> Tambah ke Keranjang
    </a>
  </div>
  <?php endwhile; ?>
</div>

<?php
$content = ob_get_clean();
render_user_layout('Daftar Menu Dimsum', $content, 'dashboard');
