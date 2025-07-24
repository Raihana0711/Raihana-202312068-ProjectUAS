<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';

$id = $_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM menu WHERE id_menu='$id'");
$d = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
  $nama = sanitize($_POST['nama']);
  $harga = intval($_POST['harga']);
  $deskripsi = sanitize($_POST['deskripsi']);
  $gambar_url = sanitize($_POST['gambar_url']);

  // Update dengan URL gambar
  if (!empty($gambar_url)) {
    mysqli_query($conn, "UPDATE menu SET nama_menu='$nama', harga='$harga', deskripsi='$deskripsi', gambar='$gambar_url' WHERE id_menu='$id'");
  } else {
    mysqli_query($conn, "UPDATE menu SET nama_menu='$nama', harga='$harga', deskripsi='$deskripsi' WHERE id_menu='$id'");
  }

  echo "<script>alert('Menu berhasil diupdate!'); window.location='/backup.raihanna/index.php?page=admin&sub=menu';</script>";
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Menu</title>
  <style>
    body {
      background: #f1f8e8;
      font-family: sans-serif;
      padding: 30px;
    }
    form {
      background: white;
      padding: 25px;
      border-radius: 12px;
      width: 400px;
      margin: auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      text-align: center;
      color: #2e4e2d;
      margin-bottom: 20px;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 12px;
      border-radius: 6px;
      border: 1px solid #aac38a;
      background: #dce8c6;
      color: #2e4e2d;
    }
    textarea {
      resize: vertical;
    }
    button {
      background: #f5b7c2;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      width: 100%;
      transition: 0.3s;
    }
    button:hover {
      background: #e890a8;
    }
  </style>
</head>
<body>

  <form method="POST">
    <h2>Edit Menu</h2>
    <input type="text" name="nama" value="<?= htmlspecialchars($d['nama_menu']) ?>" placeholder="Nama Menu" required>
    <input type="number" name="harga" value="<?= $d['harga'] ?>" placeholder="Harga" required>
    <textarea name="deskripsi" placeholder="Deskripsi menu" required><?= htmlspecialchars($d['deskripsi']) ?></textarea>
    <input type="url" name="gambar_url" value="<?= htmlspecialchars($d['gambar']) ?>" placeholder="Link gambar (contoh: https://example.com/image.jpg)">
    <button name="update">Update</button>
    <a href="<?= base_url('index.php?page=admin&sub=menu') ?>" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #aac38a; color: white; text-decoration: none; border-radius: 6px; text-align: center; width: 100%; box-sizing: border-box;">Kembali ke Menu</a>
  </form>

</body>
</html>
