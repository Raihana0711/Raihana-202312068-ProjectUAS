<?php
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/user_layout.php';

require_user();

$username = $_SESSION['user'];
$get_user = mysqli_query($conn, "SELECT * FROM user WHERE username='$username'");
$u = mysqli_fetch_assoc($get_user);

// Check if user was found
if (!$u) {
    echo "<script>alert('User session error. Please login again.'); window.location='" . base_url('index.php?page=login') . "';</script>";
    exit;
}

$message = '';
$error = '';

// Handle adding item to cart
if (isset($_GET['id']) && !isset($_GET['action'])) {
    $id_menu = intval($_GET['id']);
    
    // Check if menu exists
    $menu_check = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$id_menu'");
    if (mysqli_num_rows($menu_check) > 0) {
        // Check if item already in cart
        $existing = mysqli_query($conn, "SELECT * FROM keranjang WHERE id_user='{$u['id_user']}' AND id_menu='$id_menu'");
        
        if (mysqli_num_rows($existing) > 0) {
            // Update quantity
            mysqli_query($conn, "UPDATE keranjang SET jumlah = jumlah + 1 WHERE id_user='{$u['id_user']}' AND id_menu='$id_menu'");
            $message = 'Jumlah item di keranjang berhasil ditambah!';
        } else {
            // Add new item to cart
            mysqli_query($conn, "INSERT INTO keranjang (id_user, id_menu, jumlah) VALUES ('{$u['id_user']}', '$id_menu', 1)");
            $message = 'Item berhasil ditambahkan ke keranjang!';
        }
    } else {
        $error = 'Menu tidak ditemukan atau tidak tersedia!';
    }
}

// Handle updating cart quantity
if (isset($_POST['update_cart'])) {
    $id_keranjang = intval($_POST['id_keranjang']);
    $jumlah = intval($_POST['jumlah']);
    
    if ($jumlah > 0) {
        mysqli_query($conn, "UPDATE keranjang SET jumlah = '$jumlah' WHERE id_keranjang = '$id_keranjang' AND id_user = '{$u['id_user']}'");
        $message = 'Keranjang berhasil diperbarui!';
    } else {
        mysqli_query($conn, "DELETE FROM keranjang WHERE id_keranjang = '$id_keranjang' AND id_user = '{$u['id_user']}'");
        $message = 'Item berhasil dihapus dari keranjang!';
    }
}

// Handle removing item from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $id_keranjang = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM keranjang WHERE id_keranjang = '$id_keranjang' AND id_user = '{$u['id_user']}'");
    $message = 'Item berhasil dihapus dari keranjang!';
}

// Handle creating order from cart
if (isset($_POST['create_order'])) {
    // Get cart items
    $cart_query = "SELECT k.*, m.nama_menu, m.harga FROM keranjang k 
                   JOIN menu m ON k.id_menu = m.id_menu 
                   WHERE k.id_user = '{$u['id_user']}'";
    $cart_result = mysqli_query($conn, $cart_query);
    
    if (mysqli_num_rows($cart_result) > 0) {
        // Create a default pembeli entry (nama akan diambil dari username)
        $pembeli_nama = $u['username'] . ' (Order dari Keranjang)';
        mysqli_query($conn, "INSERT INTO pembeli (nama, no_hp, alamat) VALUES ('$pembeli_nama', '-', '-')");
        $id_pembeli = mysqli_insert_id($conn);
        
        // Calculate total
        $total = 0;
        mysqli_data_seek($cart_result, 0);
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $total += $item['harga'] * $item['jumlah'];
        }
        
        // Insert transaksi
        $tanggal = date('Y-m-d');
        mysqli_query($conn, "INSERT INTO transaksi (id_user, id_pembeli, tanggal, total, status, catatan) 
                             VALUES ('{$u['id_user']}', '$id_pembeli', '$tanggal', '$total', 'pending', 'Pesanan dibuat dari keranjang')");
        $id_transaksi = mysqli_insert_id($conn);
        
        // Insert detail transaksi and clear cart
        mysqli_data_seek($cart_result, 0);
        while ($item = mysqli_fetch_assoc($cart_result)) {
            $subtotal = $item['harga'] * $item['jumlah'];
            mysqli_query($conn, "INSERT INTO detail_transaksi (id_transaksi, id_menu, nama_menu, harga, jumlah, subtotal) 
                                 VALUES ('$id_transaksi', '{$item['id_menu']}', '{$item['nama_menu']}', '{$item['harga']}', '{$item['jumlah']}', '$subtotal')");
        }
        
        // Clear cart
        mysqli_query($conn, "DELETE FROM keranjang WHERE id_user = '{$u['id_user']}'");
        
        echo "<script>alert('Pesanan berhasil dibuat! ID Transaksi: #$id_transaksi'); window.location='" . base_url('index.php?page=user&sub=daftar_transaksi') . "';</script>";
        exit;
    } else {
        $error = 'Keranjang kosong!';
    }
}

// Get cart items with menu details
$cart_query = "SELECT k.*, m.nama_menu, m.harga, m.gambar 
               FROM keranjang k 
               JOIN menu m ON k.id_menu = m.id_menu 
               WHERE k.id_user = '{$u['id_user']}' 
               ORDER BY k.created_at DESC";
$cart_items = mysqli_query($conn, $cart_query);

// Calculate cart totals
$cart_total = 0;
$cart_count = 0;
if ($cart_items && mysqli_num_rows($cart_items) > 0) {
    mysqli_data_seek($cart_items, 0);
    while ($item = mysqli_fetch_assoc($cart_items)) {
        $cart_total += $item['harga'] * $item['jumlah'];
        $cart_count += $item['jumlah'];
    }
    mysqli_data_seek($cart_items, 0); // Reset for display
}

// Start output buffering
ob_start();
?>
<!-- Cart Summary -->
<div style="background: rgba(255,255,255,0.9); padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
  <div style="text-align: center;">
    <h2 style="color: var(--dark-green); margin-bottom: 16px;">🛒 Keranjang Belanja</h2>
    <div style="display: flex; justify-content: center; gap: 32px; flex-wrap: wrap;">
      <div style="background: var(--primary-green); color: white; padding: 12px 20px; border-radius: 25px; font-weight: bold;">
        Total Item: <?= $cart_count ?>
      </div>
      <div style="background: var(--primary-pink); color: white; padding: 12px 20px; border-radius: 25px; font-weight: bold;">
        Total Harga: Rp <?= number_format($cart_total) ?>
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div style="background: #d4edda; color: #155724; padding: 16px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #28a745;"><?= $message ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div style="background: #f8d7da; color: #721c24; padding: 16px; border-radius: 12px; margin-bottom: 20px; border-left: 4px solid #dc3545;"><?= $error ?></div>
<?php endif; ?>

<style>
.cart-item {
  background: white;
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 24px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.cart-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  border-color: var(--primary-pink);
}

.cart-item img {
  width: 120px;
  height: 100px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.cart-item:hover img {
  transform: scale(1.05);
}

.cart-item-info {
  flex: 1;
}

.cart-item-info h4 {
  color: var(--dark-green);
  font-size: 1.4rem;
  margin: 0 0 8px 0;
  font-weight: 600;
}

.cart-item-price {
  font-weight: bold;
  color: #e74c3c;
  font-size: 1.1rem;
  margin-bottom: 16px;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.quantity-input {
  width: 70px;
  padding: 8px;
  text-align: center;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: bold;
}

.btn-modern {
  padding: 10px 20px;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 600;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.btn-update {
  background: linear-gradient(135deg, var(--accent-green), var(--primary-green));
  color: white;
  box-shadow: 0 4px 15px rgba(136, 216, 163, 0.4);
}

.btn-update:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(136, 216, 163, 0.6);
}

.btn-remove {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
}

.btn-remove:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(231, 76, 60, 0.6);
}

.cart-total {
  background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
  padding: 32px;
  border-radius: 20px;
  margin-top: 32px;
  text-align: center;
  color: white;
  box-shadow: 0 12px 40px rgba(168, 230, 207, 0.4);
}

.cart-total h3 {
  font-size: 2rem;
  margin-bottom: 16px;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-checkout {
  background: linear-gradient(135deg, var(--primary-pink), var(--secondary-pink));
  color: white;
  padding: 16px 32px;
  border: none;
  border-radius: 25px;
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
  max-width: 400px;
  margin-top: 20px;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(245, 183, 194, 0.5);
}

.btn-checkout:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(245, 183, 194, 0.7);
}

.empty-cart {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255,255,255,0.9);
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.empty-cart h3 {
  color: var(--dark-green);
  font-size: 2rem;
  margin-bottom: 16px;
}

.empty-cart p {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 24px;
}

.btn-shop {
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

.btn-shop:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 183, 194, 0.6);
}

@media (max-width: 768px) {
  .cart-item {
    flex-direction: column;
    text-align: center;
    padding: 20px;
  }
  
  .quantity-controls {
    justify-content: center;
    margin-top: 16px;
  }
}
</style>

<?php if ($cart_items && mysqli_num_rows($cart_items) > 0): ?>
  <?php while ($item = mysqli_fetch_assoc($cart_items)): ?>
  <div class="cart-item">
    <img src="<?= smart_image_url($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_menu']) ?>" onerror="this.src='<?= upload_url('default.svg') ?>'">
    
    <div class="cart-item-info">
      <h4><?= htmlspecialchars($item['nama_menu']) ?></h4>
      <div class="cart-item-price">Rp <?= number_format($item['harga']) ?> × <?= $item['jumlah'] ?> = Rp <?= number_format($item['harga'] * $item['jumlah']) ?></div>
      
      <div class="quantity-controls">
        <form method="POST" style="display: inline-flex; align-items: center; gap: 8px;">
          <input type="hidden" name="id_keranjang" value="<?= $item['id_keranjang'] ?>">
          <input type="number" name="jumlah" value="<?= $item['jumlah'] ?>" min="0" class="quantity-input">
          <button type="submit" name="update_cart" class="btn-modern btn-update">Update</button>
        </form>
        <a href="<?= base_url('index.php?page=user&sub=keranjang&action=remove&id=' . $item['id_keranjang']) ?>" 
           class="btn-modern btn-remove" 
           onclick="return confirm('Yakin ingin menghapus item ini?')">Hapus</a>
      </div>
    </div>
  </div>
  <?php endwhile; ?>

  <div class="cart-total">
    <h3>💰 Total Belanja: Rp <?= number_format($cart_total) ?></h3>
    <p style="margin: 16px 0; opacity: 0.9; font-size: 1rem;">💡 Anda dapat mengubah jumlah atau menghapus item dari keranjang</p>
    
    <form method="POST">
      <button type="submit" name="create_order" class="btn-checkout"
              onclick="return confirm('Yakin ingin membuat pesanan dari keranjang ini?')">
        🛍️ Buat Pesanan Sekarang
      </button>
    </form>
    
    <p style="margin-top: 16px; opacity: 0.8; font-size: 0.9rem;">
      * Item akan dipindahkan ke Transaksi Saya setelah pesanan dibuat
    </p>
  </div>

<?php else: ?>
  <div class="empty-cart">
    <h3>🛒 Keranjang Kosong</h3>
    <p>Belum ada item dalam keranjang belanja Anda.</p>
    <a href="<?= base_url('index.php?page=user&sub=dashboard') ?>" class="btn-shop">Mulai Belanja</a>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
render_user_layout('Keranjang Belanja', $content, 'keranjang');
