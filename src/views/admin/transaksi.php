<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../../../index.php?page=login");
  exit;
}

// Update status transaksi
if (isset($_POST['update_status'])) {
  $id = intval($_POST['id_transaksi']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);
  mysqli_query($conn, "UPDATE transaksi SET status='$status' WHERE id_transaksi=$id");
  echo "<script>alert('Status berhasil diperbarui!'); window.location='/backup.raihanna/index.php?page=admin&sub=transaksi';</script>";
  exit;
}

// Ambil data transaksi + user
$data = mysqli_query($conn, "
  SELECT t.id_transaksi, t.tanggal, u.username, t.total, t.status
  FROM transaksi t
  JOIN user u ON t.id_user = u.id_user
  ORDER BY t.tanggal DESC
");

// Hitung statistik
$stats_query = mysqli_query($conn, "
  SELECT 
    COUNT(*) as total_transaksi,
    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
    SUM(CASE WHEN status = 'pending' OR status IS NULL THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'selesai' THEN total ELSE 0 END) as total_pendapatan
  FROM transaksi
");
$stats = mysqli_fetch_assoc($stats_query);

// Prepare content
ob_start();
?>
<style>
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    padding: 2rem;
    border-radius: 16px;
    color: white;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-8px) rotate(180deg); }
  }

  .stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
  }

  .stat-label {
    font-size: 1rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
  }

  .stat-icon {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    opacity: 0.3;
    font-size: 2rem;
    z-index: 1;
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

  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .status-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 2px solid #fed7aa;
  }

  .status-selesai {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    border: 2px solid #a7f3d0;
  }

  .action-form {
    display: flex;
    gap: 0.75rem;
    align-items: center;
  }

  .select-input {
    padding: 0.75rem;
    border: 2px solid rgba(168, 230, 207, 0.3);
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
    min-width: 120px;
    font-weight: 500;
  }

  .select-input:focus {
    outline: none;
    border-color: #a8e6cf;
    box-shadow: 0 0 0 4px rgba(168, 230, 207, 0.1);
  }

  .btn-update {
    background: linear-gradient(135deg, #a8e6cf 0%, #88d8a3 100%);
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(168, 230, 207, 0.3);
  }

  .btn-update:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(168, 230, 207, 0.4);
  }

  .currency {
    font-weight: 700;
    color: #059669;
    font-size: 1rem;
  }

  .transaction-id {
    font-family: 'Monaco', 'Courier New', monospace;
    background: linear-gradient(135deg, rgba(168, 230, 207, 0.2) 0%, rgba(245, 183, 194, 0.2) 100%);
    padding: 0.5rem 0.75rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #2e4e2d;
    border: 1px solid rgba(168, 230, 207, 0.3);
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
    .stats-grid {
      grid-template-columns: 1fr;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    .action-form {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .select-input {
      min-width: auto;
      width: 100%;
    }
  }
</style>

<div class="stats-grid">
  <div class="stat-card" style="background: linear-gradient(135deg, #a8e6cf 0%, #88d8a3 100%);">
    <i class="fas fa-shopping-cart stat-icon"></i>
    <div class="stat-number"><?= number_format($stats['total_transaksi']) ?></div>
    <div class="stat-label">Total Transaksi</div>
  </div>
  <div class="stat-card" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);">
    <i class="fas fa-check-circle stat-icon"></i>
    <div class="stat-number"><?= number_format($stats['selesai']) ?></div>
    <div class="stat-label">Transaksi Selesai</div>
  </div>
  <div class="stat-card" style="background: linear-gradient(135deg, #f5b7c2 0%, #e890a8 100%);">
    <i class="fas fa-clock stat-icon"></i>
    <div class="stat-number"><?= number_format($stats['pending']) ?></div>
    <div class="stat-label">Menunggu Konfirmasi</div>
  </div>
  <div class="stat-card" style="background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);">
    <i class="fas fa-money-bill-wave stat-icon"></i>
    <div class="stat-number">Rp <?= number_format($stats['total_pendapatan']) ?></div>
    <div class="stat-label">Total Pendapatan</div>
  </div>
</div>

<div class="table-container">
  <?php if (mysqli_num_rows($data) > 0): ?>
  <table class="table">
    <thead>
      <tr>
        <th><i class="fas fa-hashtag"></i> No</th>
        <th><i class="fas fa-id-card"></i> ID Transaksi</th>
        <th><i class="fas fa-user"></i> Nama Pelanggan</th>
        <th><i class="fas fa-calendar"></i> Tanggal</th>
        <th><i class="fas fa-money-bill"></i> Total</th>
        <th><i class="fas fa-info-circle"></i> Status</th>
        <th><i class="fas fa-cogs"></i> Kelola</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      mysqli_data_seek($data, 0);
      while ($d = mysqli_fetch_assoc($data)) {
        $nama_pemesan = $d['username'];
        $status = $d['status'] ?: 'pending';
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><span class="transaction-id">#TXN<?= str_pad($d['id_transaksi'], 4, '0', STR_PAD_LEFT) ?></span></td>
        <td><?= htmlspecialchars($nama_pemesan) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($d['tanggal'])) ?></td>
        <td><span class="currency">Rp <?= number_format($d['total']) ?></span></td>
        <td>
          <span class="status-badge status-<?= $status ?>">
            <?php if ($status == 'selesai'): ?>
              <i class="fas fa-check"></i>
            <?php else: ?>
              <i class="fas fa-clock"></i>
            <?php endif; ?>
            <?= ucfirst($status) ?>
          </span>
        </td>
        <td>
          <form method="POST" class="action-form">
            <input type="hidden" name="id_transaksi" value="<?= $d['id_transaksi'] ?>">
            <select name="status" class="select-input">
              <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
            <button type="submit" name="update_status" class="btn-update" title="Update Status">
              <i class="fas fa-sync-alt"></i>
            </button>
          </form>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state">
    <i class="fas fa-inbox"></i>
    <h3>Belum Ada Transaksi</h3>
    <p>Belum ada transaksi yang tercatat dalam sistem</p>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('📊 Kelola Transaksi', $content, 'transaksi');
?>
