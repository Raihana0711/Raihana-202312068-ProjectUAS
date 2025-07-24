<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/user_layout.php';

require_user();

$user_id = $_SESSION['user_id'] ?? 0;

/* -------------------------------------------------------------
   Ambil data user login
------------------------------------------------------------- */
$get_user = mysqli_query($conn, "SELECT * FROM user WHERE id_user='$user_id'");
$user = mysqli_fetch_assoc($get_user);
if (!$user) {
  // kalau entah kenapa user hilang dari DB
  echo "<script>alert('Data user tidak ditemukan!'); window.location='" . base_url('logout.php') . "';</script>";
  exit;
}
$id_user = (int)$user['id_user'];

/* -------------------------------------------------------------
   Ambil data transaksi + detail + menu user ini
   (satu baris = satu item menu di dalam transaksi)
------------------------------------------------------------- */
$sql = "
  SELECT 
    t.id_transaksi,
    t.tanggal,
    t.total,
    t.status,
    m.nama_menu,
    m.gambar,
    dt.jumlah,
    dt.subtotal
  FROM transaksi t
  JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
  JOIN menu m ON dt.id_menu = m.id_menu
  WHERE t.id_user = $id_user
  ORDER BY t.tanggal DESC, t.id_transaksi DESC, dt.id_detail ASC
";
$data = mysqli_query($conn, $sql);

// Start output buffering
ob_start();
?>
<style>
.transaction-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.transaction-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  border-color: var(--primary-pink);
}

.transaction-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 16px 0;
  border-bottom: 1px solid #f0f0f0;
}

.transaction-item:last-child {
  border-bottom: none;
}

.transaction-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.transaction-item:hover .transaction-img {
  transform: scale(1.1);
}

.transaction-info {
  flex: 1;
}

.transaction-info h4 {
  color: var(--dark-green);
  font-size: 1.2rem;
  margin: 0 0 8px 0;
  font-weight: 600;
}

.transaction-date {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 4px;
}

.transaction-price {
  font-weight: bold;
  color: #e74c3c;
  font-size: 1.1rem;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: bold;
  color: white;
  text-align: center;
  min-width: 80px;
}

.status-pending {
  background: linear-gradient(135deg, #f39c12, #d68910);
}

.status-selesai {
  background: linear-gradient(135deg, #27ae60, #219a52);
}

.status-unknown {
  background: linear-gradient(135deg, #95a5a6, #7f8c8d);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255,255,255,0.9);
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.empty-state h3 {
  color: var(--dark-green);
  font-size: 2rem;
  margin-bottom: 16px;
}

.empty-state p {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 24px;
}

.btn-shop-now {
  background: linear-gradient(135deg, var(--primary-pink), var(--secondary-pink));
  color: white;
  padding: 14px 28px;
  border-radius: 25px;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(245, 183, 194, 0.4);
}

.btn-shop-now:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 183, 194, 0.6);
}

@media (max-width: 768px) {
  .transaction-item {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .transaction-img {
    width: 60px;
    height: 60px;
  }
}
</style>

<?php if (mysqli_num_rows($data) === 0): ?>
  <div class="empty-state">
    <h3>📋 Belum Ada Transaksi</h3>
    <p>Anda belum melakukan pembelian apapun.</p>
    <a href="<?= base_url('index.php?page=user&sub=dashboard') ?>" class="btn-shop-now">Mulai Belanja Sekarang</a>
  </div>
<?php else: ?>
  <?php
    $current_transaction = null;
    $transaction_items = [];
    $all_transactions = [];
    
    // Group items by transaction
    mysqli_data_seek($data, 0);
    while ($row = mysqli_fetch_assoc($data)) {
      if ($current_transaction !== $row['id_transaksi']) {
        // Save previous transaction if exists
        if ($current_transaction !== null) {
          $all_transactions[] = $transaction_items;
        }
        
        // Start new transaction
        $current_transaction = $row['id_transaksi'];
        $transaction_items = [];
      }
      
      $transaction_items[] = $row;
    }
    
    // Save last transaction
    if ($current_transaction !== null) {
      $all_transactions[] = $transaction_items;
    }
    
    // Display all transactions
    foreach ($all_transactions as $items):
      if (empty($items)) continue;
      
      $first_item = $items[0];
      $total_amount = 0;
      foreach ($items as $item) {
        $total_amount += $item['subtotal'];
      }
      
      // Status styling
      $status_class = 'status-unknown';
      $status_text = 'Unknown';
      $st_raw = strtolower($first_item['status'] ?? '');
      
      if ($st_raw === 'pending' || $st_raw === '') {
        $status_class = 'status-pending';
        $status_text = 'Pending';
      } elseif (in_array($st_raw, ['selesai', 'success', 'done'])) {
        $status_class = 'status-selesai';
        $status_text = 'Selesai';
      } else {
        $status_text = ucfirst($first_item['status']);
      }
  ?>
      <div class="transaction-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
          <div>
            <h3 style="color: var(--dark-green); margin: 0; font-size: 1.4rem;">Transaksi #<?= $first_item['id_transaksi'] ?></h3>
            <p style="color: #666; margin: 4px 0 0 0; font-size: 0.95rem;"><?= date('d M Y', strtotime($first_item['tanggal'])) ?></p>
          </div>
          <div class="status-badge <?= $status_class ?>"><?= $status_text ?></div>
        </div>
        
        <?php foreach ($items as $item): ?>
        <div class="transaction-item">
          <img src="<?= smart_image_url($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_menu']) ?>" 
               class="transaction-img" onerror="this.src='<?= upload_url('default.svg') ?>'">
          
          <div class="transaction-info">
            <h4><?= htmlspecialchars($item['nama_menu']) ?></h4>
            <div style="color: #666; margin-bottom: 4px;"><?= (int)$item['jumlah'] ?> pcs</div>
            <div class="transaction-price">Rp <?= number_format($item['subtotal']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        
        <div style="border-top: 2px solid var(--primary-green); margin-top: 16px; padding-top: 16px; text-align: right;">
          <h3 style="color: var(--dark-green); margin: 0; font-size: 1.3rem;">Total: Rp <?= number_format($total_amount) ?></h3>
        </div>
      </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
render_user_layout('Riwayat Pembelian Saya', $content, 'transaksi');
