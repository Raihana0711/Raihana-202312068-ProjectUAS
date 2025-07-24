<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/user_layout.php';

require_user();

$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['user_name'] ?? 'User';

// Ambil data user login
$user_query = mysqli_query($conn, "SELECT * FROM user WHERE id_user='$user_id'");
$user = mysqli_fetch_assoc($user_query);

// Nama default: gunakan kolom lain jika ada (misal 'nama'), kalau tidak pakai username
$default_nama = $user && isset($user['nama']) && $user['nama'] !== ''
  ? $user['nama']
  : $username;

// --- Proses submit testimoni ---
if (isset($_POST['kirim'])) {
  // Ambil data form
  $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
  if ($nama === '') { $nama = $default_nama; } // fallback

  $isi = mysqli_real_escape_string($conn, trim($_POST['isi']));
  $tanggal = date('Y-m-d');
  $gambar = null;

  // Ambil URL gambar (opsional)
  $gambar_url = trim($_POST['gambar_url'] ?? '');
  if (!empty($gambar_url)) {
    $gambar = $gambar_url;
  }

  // Siapkan nilai gambar untuk query
  if ($gambar === null) {
    $sql = "INSERT INTO testimoni (nama, isi, tanggal, gambar)
            VALUES ('$nama', '$isi', '$tanggal', NULL)";
  } else {
    $sql = "INSERT INTO testimoni (nama, isi, tanggal, gambar)
            VALUES ('$nama', '$isi', '$tanggal', '$gambar')";
  }

  if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Testimoni berhasil dikirim!'); window.location='" . base_url('index.php?page=user&sub=testimoni') . "';</script>";
    exit;
  } else {
    echo "<script>alert('Gagal menyimpan testimoni: ".addslashes(mysqli_error($conn))."');</script>";
  }
}

// Ambil semua testimoni untuk ditampilkan
$testimoni = mysqli_query($conn, "SELECT * FROM testimoni ORDER BY tanggal DESC, id_testimoni DESC");

// Start output buffering
ob_start();
?>
<style>
.testimoni-form {
  background: white;
  padding: 32px;
  border-radius: 20px;
  margin-bottom: 32px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.testimoni-form:hover {
  border-color: var(--primary-pink);
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.form-title {
  text-align: center;
  color: var(--dark-green);
  font-size: 2rem;
  margin-bottom: 24px;
  font-weight: 700;
}

.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-weight: 600;
  color: var(--dark-green);
  margin-bottom: 8px;
  font-size: 1rem;
}

.form-input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #ddd;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: rgba(168, 230, 207, 0.1);
  color: var(--dark-green);
}

.form-input:focus {
  border-color: var(--primary-pink);
  background: white;
  outline: none;
  box-shadow: 0 0 0 3px rgba(245, 183, 194, 0.2);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.btn-submit {
  background: linear-gradient(135deg, var(--primary-pink), var(--secondary-pink));
  color: white;
  padding: 14px 32px;
  border: none;
  border-radius: 25px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 6px 20px rgba(245, 183, 194, 0.4);
  width: 100%;
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 183, 194, 0.6);
}

.testimoni-list {
  margin-top: 48px;
}

.list-title {
  text-align: center;
  color: var(--dark-green);
  font-size: 2rem;
  margin-bottom: 32px;
  font-weight: 700;
}

.testimoni-item {
  background: white;
  padding: 24px;
  border-radius: 16px;
  margin-bottom: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.testimoni-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
  border-color: var(--accent-green);
}

.testimoni-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.testimoni-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-green), var(--accent-green));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.testimoni-info h4 {
  color: var(--dark-green);
  font-size: 1.2rem;
  margin: 0 0 4px 0;
  font-weight: 600;
}

.testimoni-date {
  color: #666;
  font-size: 0.9rem;
}

.testimoni-content {
  color: #444;
  line-height: 1.6;
  font-size: 1rem;
  margin-bottom: 16px;
  white-space: pre-line;
}

.testimoni-image {
  max-width: 200px;
  max-height: 150px;
  border-radius: 12px;
  object-fit: cover;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.testimoni-image:hover {
  transform: scale(1.1);
}

@media (max-width: 768px) {
  .testimoni-form {
    padding: 24px;
    margin: 0 0 24px 0;
  }
  
  .testimoni-header {
    flex-direction: column;
    text-align: center;
  }
}
</style>

<!-- Testimoni Form -->
<div class="testimoni-form">
  <h2 class="form-title">✨ Berikan Testimoni Anda</h2>
  
  <form method="POST">
    <div class="form-group">
      <label class="form-label">Nama (boleh diganti):</label>
      <input type="text" name="nama" class="form-input" value="<?= htmlspecialchars($default_nama) ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Testimoni:</label>
      <textarea name="isi" class="form-input form-textarea" placeholder="Bagikan pengalaman Anda dengan dimsum kami..." required></textarea>
    </div>

    <div class="form-group">
      <label class="form-label">Link Gambar (opsional):</label>
      <input type="url" name="gambar_url" class="form-input" placeholder="https://example.com/foto.jpg">
    </div>

    <button type="submit" name="kirim" class="btn-submit">
      <i class="fas fa-paper-plane"></i> Kirim Testimoni
    </button>
  </form>
</div>

<!-- Testimoni List -->
<div class="testimoni-list">
  <h2 class="list-title">💬 Testimoni Pengguna</h2>
  
  <?php if (mysqli_num_rows($testimoni) == 0): ?>
    <div style="text-align: center; padding: 40px; background: rgba(255,255,255,0.8); border-radius: 16px;">
      <p style="color: #666; font-size: 1.1rem;">Belum ada testimoni yang tersedia.</p>
    </div>
  <?php else: ?>
    <?php while ($t = mysqli_fetch_assoc($testimoni)): ?>
      <div class="testimoni-item">
        <div class="testimoni-header">
          <div class="testimoni-avatar">
            <?= strtoupper(substr($t['nama'], 0, 1)) ?>
          </div>
          <div class="testimoni-info">
            <h4><?= htmlspecialchars($t['nama']) ?></h4>
            <div class="testimoni-date"><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
          </div>
        </div>
        
        <div class="testimoni-content"><?= htmlspecialchars($t['isi']) ?></div>
        
        <?php if (!empty($t['gambar'])): ?>
          <img src="<?= htmlspecialchars($t['gambar']) ?>" alt="Gambar Testimoni" class="testimoni-image">
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
render_user_layout('Testimoni', $content, 'testimoni');
