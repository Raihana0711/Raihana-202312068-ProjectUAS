<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../../../index.php?page=login");
  exit;
}

$mulai  = $_GET['mulai']  ?? '';
$sampai = $_GET['sampai'] ?? '';

// Query dasar
$sql = "SELECT t.*, u.username 
        FROM transaksi t 
        JOIN user u ON t.id_user = u.id_user";

// Filter tanggal jika diisi
if ($mulai && $sampai) {
  $mulai_esc  = mysqli_real_escape_string($conn, $mulai);
  $sampai_esc = mysqli_real_escape_string($conn, $sampai);
  $sql .= " WHERE t.tanggal BETWEEN '$mulai_esc' AND '$sampai_esc'";
}

$sql .= " ORDER BY t.tanggal DESC";
$data = mysqli_query($conn, $sql);

// Prepare content
ob_start();
?>
<style>
  .toolbar {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .filter-form {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .form-label {
    font-weight: 600;
    color: #2e4e2d;
    font-size: 0.9rem;
  }

  .form-input {
    padding: 0.75rem;
    border: 2px solid rgba(168, 230, 207, 0.3);
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    font-weight: 500;
  }

  .form-input:focus {
    outline: none;
    border-color: #a8e6cf;
    box-shadow: 0 0 0 4px rgba(168, 230, 207, 0.1);
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

  .btn-pink {
    background: linear-gradient(135deg, #f5b7c2, #e890a8);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 183, 194, 0.3);
  }

  .btn-pink:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(245, 183, 194, 0.4);
  }

  .table-container {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
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

  .table tfoot th {
    background: linear-gradient(135deg, #88d8a3 0%, #a8e6cf 100%);
    color: white;
    font-weight: 700;
    text-align: right;
  }

  .stats-cards {
    display: flex;
    gap: 1.5rem;
    margin: 2rem 0;
    justify-content: center;
    flex-wrap: wrap;
  }

  .stat-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    padding: 1.5rem;
    border-radius: 16px;
    text-align: center;
    min-width: 150px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
  }

  .stat-card h3 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    color: #2e4e2d;
  }

  .stat-card .stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #059669;
  }

  .menu-terlaris {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .menu-terlaris h3 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #2e4e2d;
    font-size: 1.3rem;
  }

  @media print {
    * {
      box-shadow: none !important;
      text-shadow: none !important;
      background-image: none !important;
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
    }
    
    body {
      background: #fff !important;
      padding: 20px !important;
      color: #000 !important;
      font-family: Arial, sans-serif !important;
      margin: 0;
      overflow: visible !important;
    }
    
    .hamburger-menu,
    .overlay,
    .admin-panel,
    .toolbar {
      display: none !important;
    }
    
    .main-content {
      padding: 0 !important;
      margin: 0 !important;
      position: static !important;
    }
    
    .content-container {
      background: white !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      margin: 0 !important;
      max-width: none !important;
    }
    
    .page-header {
      background: #f8f9fa !important;
      color: #333 !important;
      padding: 20px !important;
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #ddd;
    }
    
    .page-title {
      color: #333 !important;
      font-size: 24px !important;
      margin: 0 0 10px 0 !important;
      text-shadow: none !important;
    }
    
    .page-subtitle {
      color: #666 !important;
      font-size: 14px !important;
      margin: 0 !important;
    }
    
    .page-content {
      padding: 0 !important;
    }
    
    .table-container {
      background: white !important;
      border-radius: 0 !important;
      overflow: visible !important;
      box-shadow: none !important;
      border: 1px solid #ddd !important;
      margin-bottom: 20px !important;
    }
    
    .table {
      border-collapse: collapse !important;
      width: 100% !important;
    }
    
    .table th {
      background: #f8f9fa !important;
      color: #333 !important;
      border: 1px solid #ddd !important;
      padding: 10px 8px !important;
      font-size: 12px !important;
      font-weight: bold !important;
      text-transform: uppercase;
    }
    
    .table td {
      border: 1px solid #ddd !important;
      padding: 8px !important;
      font-size: 11px !important;
      background: white !important;
      color: #333 !important;
    }
    
    .table tfoot th {
      background: #e9ecef !important;
      color: #333 !important;
      font-weight: bold !important;
      border: 1px solid #ddd !important;
    }
    
    .stats-cards {
      display: flex !important;
      gap: 15px !important;
      margin: 20px 0 !important;
      justify-content: center !important;
      flex-wrap: wrap !important;
      page-break-inside: avoid;
    }
    
    .stat-card {
      background: #f8f9fa !important;
      border: 1px solid #ddd !important;
      padding: 15px 10px !important;
      border-radius: 5px !important;
      min-width: 120px !important;
      text-align: center !important;
      flex: none !important;
    }
    
    .stat-card h3 {
      font-size: 12px !important;
      margin: 0 0 8px 0 !important;
      color: #333 !important;
    }
    
    .stat-card .stat-number {
      font-size: 18px !important;
      font-weight: bold !important;
      color: #333 !important;
      margin: 0 !important;
    }
    
    .menu-terlaris {
      background: white !important;
      border-radius: 0 !important;
      padding: 20px 0 !important;
      box-shadow: none !important;
      border: none !important;
      page-break-inside: avoid;
      margin-top: 30px;
    }
    
    .menu-terlaris h3 {
      color: #333 !important;
      font-size: 16px !important;
      margin-bottom: 15px !important;
      text-align: center;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }
    
    /* Hide background gradients and effects */
    * {
      background-image: none !important;
    }
    
    /* Ensure proper page breaks */
    .stats-cards,
    .menu-terlaris {
      page-break-inside: avoid;
    }
    
    /* Clean up any remaining styling issues */
    .table tbody tr:hover {
      background: white !important;
      transform: none !important;
    }
  }

  @media (max-width: 768px) {
    .filter-form {
      flex-direction: column;
      align-items: stretch;
    }
    
    .stats-cards {
      flex-direction: column;
      align-items: center;
    }
    
    .table-container {
      overflow-x: auto;
    }
  }
</style>

<div class="toolbar">
  <form method="GET" class="filter-form">
    <input type="hidden" name="page" value="admin">
    <input type="hidden" name="sub" value="laporan">
    
    <div class="form-group">
      <label class="form-label">Dari:</label>
      <input type="date" name="mulai" value="<?= htmlspecialchars($mulai) ?>" class="form-input" required>
    </div>
    
    <div class="form-group">
      <label class="form-label">Sampai:</label>
      <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="form-input" required>
    </div>
    
    <button type="submit" class="btn btn-primary">
      <i class="fas fa-search"></i> Tampilkan
    </button>
    
    <button type="button" onclick="window.print()" class="btn btn-pink">
      <i class="fas fa-print"></i> Cetak Laporan
    </button>
  </form>
</div>

<div class="table-container">
  <table class="table">
    <thead>
      <tr>
        <th><i class="fas fa-hashtag"></i> No</th>
        <th><i class="fas fa-calendar"></i> Tanggal</th>
        <th><i class="fas fa-user"></i> Nama Pemesan</th>
        <th><i class="fas fa-money-bill"></i> Total</th>
        <th><i class="fas fa-info-circle"></i> Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      $grand_total = 0;
      $pending_count = 0;
      $selesai_count = 0;
      while ($d = mysqli_fetch_assoc($data)) {
        // Hanya akumulasi total jika status selesai
        if ($d['status'] == 'selesai') {
          $grand_total += $d['total'];
          $selesai_count++;
        } elseif ($d['status'] == 'pending' || empty($d['status'])) {
          $pending_count++;
        }
        $nama_pemesan = $d['username'];
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
        <td><?= htmlspecialchars($nama_pemesan) ?></td>
        <td><span style="font-weight: 600; color: #059669;">Rp <?= number_format($d['total']) ?></span></td>
        <td>
          <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; <?= $d['status'] == 'selesai' ? 'background: #d1e7dd; color: #0f5132;' : 'background: #fff3cd; color: #856404;' ?>">
            <?= ucfirst($d['status'] ?: 'pending') ?>
          </span>
        </td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3">TOTAL PENJUALAN (Selesai)</th>
        <th colspan="2">Rp <?= number_format($grand_total) ?></th>
      </tr>
    </tfoot>
  </table>
</div>

<!-- Statistik Ringkas -->
<div class="stats-cards">
  <div class="stat-card">
    <h3>Transaksi Selesai</h3>
    <div class="stat-number"><?= $selesai_count ?></div>
  </div>
  <div class="stat-card">
    <h3>Transaksi Pending</h3>
    <div class="stat-number"><?= $pending_count ?></div>
  </div>
  <div class="stat-card">
    <h3>Total Transaksi</h3>
    <div class="stat-number"><?= $selesai_count + $pending_count ?></div>
  </div>
</div>

<?php
// Query untuk menu terlaris
$menu_sql = "
  SELECT 
    m.nama_menu,
    m.harga,
    SUM(dt.jumlah) as total_terjual,
    SUM(dt.subtotal) as total_pendapatan,
    COUNT(DISTINCT dt.id_transaksi) as frekuensi_pesan
  FROM detail_transaksi dt
  JOIN menu m ON dt.id_menu = m.id_menu
  JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
  WHERE t.status = 'selesai'";

// Tambahkan filter tanggal jika ada
if ($mulai && $sampai) {
  $menu_sql .= " AND t.tanggal BETWEEN '$mulai_esc' AND '$sampai_esc'";
}

$menu_sql .= "
  GROUP BY m.id_menu, m.nama_menu, m.harga
  ORDER BY total_terjual DESC, total_pendapatan DESC
  LIMIT 10";

$menu_data = mysqli_query($conn, $menu_sql);
?>

<!-- Menu Terlaris -->
<div class="menu-terlaris">
  <h3><i class="fas fa-trophy"></i> Menu Terlaris</h3>
  <div class="table-container">
    <table class="table">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Nama Menu</th>
          <th>Harga</th>
          <th>Qty Terjual</th>
          <th>Frekuensi Pesan</th>
          <th>Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $rank = 1;
        if (mysqli_num_rows($menu_data) > 0):
          while ($menu = mysqli_fetch_assoc($menu_data)): 
        ?>
        <tr>
          <td style="font-weight: bold; color: <?= $rank <= 3 ? '#d4af37' : '#666' ?>;">
            <?php if ($rank == 1): ?>
              🥇
            <?php elseif ($rank == 2): ?>
              🥈
            <?php elseif ($rank == 3): ?>
              🥉
            <?php else: ?>
              <?= $rank ?>
            <?php endif; ?>
          </td>
          <td style="text-align: left;"><?= htmlspecialchars($menu['nama_menu']) ?></td>
          <td>Rp <?= number_format($menu['harga']) ?></td>
          <td style="font-weight: bold; color: #d35400;"><?= $menu['total_terjual'] ?> pcs</td>
          <td><?= $menu['frekuensi_pesan'] ?>x</td>
          <td style="font-weight: bold; color: #059669;">Rp <?= number_format($menu['total_pendapatan']) ?></td>
        </tr>
        <?php 
          $rank++;
          endwhile; 
        else:
        ?>
        <tr>
          <td colspan="6" style="text-align: center; color: #666; font-style: italic;">
            Belum ada data menu terjual untuk periode ini
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('📈 Laporan Penjualan', $content, 'laporan');
?>
