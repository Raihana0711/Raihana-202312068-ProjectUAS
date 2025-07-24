<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../../../index.php?page=login");
  exit;
}

// Ambil data menu
$menu = mysqli_query($conn, "SELECT * FROM menu ORDER BY id_menu DESC");
$total_menu = mysqli_num_rows($menu);

// Prepare content
ob_start();
?>
<style>
  .menu-stats {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .stats-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: rgba(168, 230, 207, 0.2);
    border-radius: 12px;
    border: 2px solid rgba(168, 230, 207, 0.3);
  }

  .stats-info i {
    font-size: 1.5rem;
    color: #2e4e2d;
  }

  .stats-text {
    font-weight: 600;
    color: #2e4e2d;
  }

  .action-buttons {
    display: flex;
    gap: 1rem;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, #a8e6cf, #88d8a3);
    color: white;
    box-shadow: 0 4px 12px rgba(168, 230, 207, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(168, 230, 207, 0.4);
  }

  .table-container {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
  }

  .table {
    width: 100%;
    border-collapse: collapse;
  }

  .table th {
    background: linear-gradient(135deg, #a8e6cf 0%, #dcedc8 100%);
    color: #2e4e2d;
    padding: 1.2rem 1rem;
    font-weight: 600;
    text-align: left;
    font-size: 0.95rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .table td {
    padding: 1.2rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    vertical-align: middle;
  }

  .table tbody tr {
    transition: all 0.3s ease;
  }

  .table tbody tr:hover {
    background: rgba(168, 230, 207, 0.05);
    transform: scale(1.001);
  }

  .menu-image {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .menu-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 1rem;
  }

  .menu-description {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    line-height: 1.4;
  }

  .price {
    font-weight: 700;
    color: #059669;
    font-size: 1rem;
  }

  .action-buttons-table {
    display: flex;
    gap: 0.5rem;
  }

  .btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 8px;
  }

  .btn-edit {
    background: linear-gradient(135deg, #84fab0, #8fd3f4);
    color: white;
  }

  .btn-edit:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(132, 250, 176, 0.4);
  }

  .btn-delete {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
  }

  .btn-delete:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
  }

  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #64748b;
  }

  .empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
    color: #a8e6cf;
  }

  .empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #475569;
  }

  .empty-state p {
    font-size: 1rem;
    opacity: 0.8;
  }

  @media (max-width: 768px) {
    .menu-stats {
      flex-direction: column;
      align-items: stretch;
    }
    
    .action-buttons {
      justify-content: center;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    .action-buttons-table {
      flex-direction: column;
      gap: 0.25rem;
    }
  }
</style>

<div class="menu-stats">
  <div class="stats-info">
    <i class="fas fa-utensils"></i>
    <span class="stats-text">Total Menu: <?= $total_menu ?></span>
  </div>
  
  <div class="action-buttons">
    <a href="<?= base_url('index.php?page=admin&sub=tambah_menu') ?>" class="btn btn-primary">
      <i class="fas fa-plus"></i> Tambah Menu Baru
    </a>
  </div>
</div>

<div class="table-container">
  <?php if (mysqli_num_rows($menu) > 0): ?>
  <table class="table">
    <thead>
      <tr>
        <th><i class="fas fa-hashtag"></i> No</th>
        <th><i class="fas fa-image"></i> Gambar</th>
        <th><i class="fas fa-utensils"></i> Nama Menu</th>
        <th><i class="fas fa-money-bill"></i> Harga</th>
        <th><i class="fas fa-align-left"></i> Deskripsi</th>
        <th><i class="fas fa-cogs"></i> Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      mysqli_data_seek($menu, 0);
      while ($d = mysqli_fetch_assoc($menu)): 
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td>
          <img src="<?= smart_image_url($d['gambar']) ?>" alt="<?= htmlspecialchars($d['nama_menu']) ?>" 
               class="menu-image" onerror="this.src='<?= upload_url('default.svg') ?>'">
        </td>
        <td>
          <div class="menu-name"><?= htmlspecialchars($d['nama_menu']) ?></div>
        </td>
        <td><span class="price">Rp <?= number_format($d['harga']) ?></span></td>
        <td>
          <div class="menu-description"><?= htmlspecialchars($d['deskripsi']) ?></div>
        </td>
        <td>
          <div class="action-buttons-table">
            <a href="<?= base_url('index.php?page=admin&sub=edit_menu&id=' . $d['id_menu']) ?>" 
               class="btn btn-edit btn-sm" title="Edit Menu">
              <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?= base_url('index.php?page=admin&sub=hapus_menu&id=' . $d['id_menu']) ?>" 
               class="btn btn-delete btn-sm" title="Hapus Menu" 
               onclick="return confirm('Yakin ingin menghapus menu ini?')">
              <i class="fas fa-trash"></i> Hapus
            </a>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state">
    <i class="fas fa-utensils"></i>
    <h3>Belum Ada Menu</h3>
    <p>Belum ada menu dimsum yang ditambahkan</p>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('🍽️ Kelola Menu Dimsum', $content, 'menu');
?>
