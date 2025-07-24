<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: /backup.raihanna/index.php?page=login");
    exit;
}

// Validate and sanitize input
$id = intval($_GET['id']);
$force = isset($_GET['force']) && $_GET['force'] == '1';

if ($id > 0) {
    // Begin transaction for data integrity
    mysqli_begin_transaction($conn);
    
    try {
        // Get menu name for confirmation message using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT nama_menu FROM menu WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $menu = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($menu) {
            // Check if menu is used in any transactions (skip if force delete)
            if (!$force) {
                $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM detail_transaksi WHERE id_menu = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $transaction_result = mysqli_stmt_get_result($stmt);
                $transaction_data = mysqli_fetch_assoc($transaction_result);
                $transaction_count = $transaction_data['count'];
                mysqli_stmt_close($stmt);
            } else {
                $transaction_count = 0; // Skip check if forced
            }
            
            // Check if menu is used in any carts using prepared statement
            $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM keranjang WHERE id_menu = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $cart_result = mysqli_stmt_get_result($stmt);
            $cart_data = mysqli_fetch_assoc($cart_result);
            $cart_count = $cart_data['count'];
            mysqli_stmt_close($stmt);
            
            if ($transaction_count > 0) {
                // Menu is used in transactions - show warning but allow deletion with confirmation
                echo "<script>
                        if (confirm('PERINGATAN!\\n\\nMenu \"" . htmlspecialchars($menu['nama_menu']) . "\" sudah digunakan dalam $transaction_count transaksi.\\n\\nJika dihapus, data transaksi akan tetap ada tapi referensi menu akan hilang.\\n\\nYakin ingin menghapus?')) {
                          // User confirmed, proceed with deletion
                          window.location='" . base_url('index.php?page=admin&sub=hapus_menu&id=' . $id . '&force=1') . "';
                        } else {
                          window.location='" . base_url('index.php?page=admin&sub=menu') . "';
                        }
                      </script>";
                exit;
            } else {
                // Proceed with deletion - remove from cart first if exists, then delete menu
                if ($cart_count > 0) {
                    $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE id_menu = ?");
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    $cart_delete_result = mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    
                    if (!$cart_delete_result) {
                        throw new Exception("Gagal menghapus menu dari keranjang: " . mysqli_error($conn));
                    }
                }
                
                // Delete the menu item
                $stmt = mysqli_prepare($conn, "DELETE FROM menu WHERE id_menu = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                $delete_result = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                if ($delete_result) {
                    mysqli_commit($conn);
                    $message = $force ? 'Menu berhasil dihapus (termasuk referensi transaksi)!' : 'Menu berhasil dihapus!';
                    echo "<script>
                            alert('Menu \"" . htmlspecialchars($menu['nama_menu']) . "\" $message');
                            window.location='" . base_url('index.php?page=admin&sub=menu') . "';
                          </script>";
                } else {
                    throw new Exception("Gagal menghapus menu: " . mysqli_error($conn));
                }
            }
        } else {
            throw new Exception("Menu tidak ditemukan!");
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "<script>
                alert('Error: " . htmlspecialchars($e->getMessage()) . "');
                window.location='" . base_url('index.php?page=admin&sub=menu') . "';
              </script>";
    }
} else {
    echo "<script>
            alert('ID menu tidak valid!');
            window.location='" . base_url('index.php?page=admin&sub=menu') . "';
          </script>";
}
exit;
?>
