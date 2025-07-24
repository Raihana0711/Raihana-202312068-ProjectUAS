<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../../../index.php?page=login");
  exit;
}

if (isset($_POST['simpan'])) {
  $nama = sanitize($_POST['nama_menu']);
  $deskripsi = sanitize($_POST['deskripsi']);
  $harga = intval($_POST['harga']);
  $gambar_url = sanitize($_POST['gambar_url']);

  // Jika tidak ada URL gambar, gunakan default
  if (empty($gambar_url)) {
    $gambar_url = 'https://via.placeholder.com/300x200?text=No+Image';
  }

  mysqli_query($conn, "INSERT INTO menu (nama_menu, deskripsi, harga, gambar, id_kategori)
                       VALUES ('$nama', '$deskripsi', '$harga', '$gambar_url', 1)");

  echo "<script>alert('Menu berhasil ditambahkan!'); window.location='/backup.raihanna/index.php?page=admin&sub=menu';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Tambah Menu</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f1f8e8;
      padding: 40px;
    }
    .container {
      width: 500px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #2e4e2d;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin: 10px 0 5px;
      color: #333;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-bottom: 15px;
    }
    button {
      background: #f5b7c2;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #e890a8;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Tambah Menu Dimsum</h2>
  <form method="POST">
    <label>Nama Menu</label>
    <input type="text" name="nama_menu" placeholder="Masukkan nama menu" required>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="3" placeholder="Deskripsi menu" required></textarea>

    <label>Harga (Rp)</label>
    <input type="number" name="harga" placeholder="Masukkan harga" required>

    <label>Link Gambar</label>
    <input type="url" name="gambar_url" placeholder="https://example.com/image.jpg (opsional)">

    <button type="submit" name="simpan">Simpan Menu</button>
    <a href="<?= base_url('index.php?page=admin&sub=menu') ?>" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #aac38a; color: white; text-decoration: none; border-radius: 6px; text-align: center;">Kembali ke Menu</a>
  </form>
</div>

</body>
</html>
