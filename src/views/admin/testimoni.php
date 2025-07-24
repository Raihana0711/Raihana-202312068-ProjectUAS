<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../../../index.php?page=login");
  exit;
}

$testimoni = mysqli_query($conn, "SELECT * FROM testimoni ORDER BY id_testimoni DESC");

// Prepare content
ob_start();
?>
<style>
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

  .testimonial-image {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(168, 230, 207, 0.2);
  }

  .testimonial-text {
    max-width: 350px;
    text-align: left;
    line-height: 1.6;
    color: #4a5568;
    font-style: italic;
    position: relative;
    padding-left: 1rem;
  }

  .testimonial-text::before {
    content: '"';
    position: absolute;
    left: 0;
    top: -0.5rem;
    font-size: 2rem;
    color: #a8e6cf;
    font-family: serif;
  }

  .customer-name {
    font-weight: 600;
    color: #2d3748;
    font-size: 1rem;
  }

  .date-badge {
    background: linear-gradient(135deg, rgba(168, 230, 207, 0.3) 0%, rgba(245, 183, 194, 0.3) 100%);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #2e4e2d;
    border: 1px solid rgba(168, 230, 207, 0.5);
    display: inline-block;
  }

  .image-placeholder {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #718096;
    font-size: 1.5rem;
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
    .table-container {
      overflow-x: auto;
    }
    
    .testimonial-text {
      max-width: 250px;
    }

    .table td {
      padding: 0.8rem 0.5rem;
      font-size: 0.9rem;
    }
  }
</style>

<div class="table-container">
  <?php if (mysqli_num_rows($testimoni) > 0): ?>
  <table class="table">
    <thead>
      <tr>
        <th><i class="fas fa-hashtag"></i> No</th>
        <th><i class="fas fa-user"></i> Nama Pelanggan</th>
        <th><i class="fas fa-quote-left"></i> Testimoni</th>
        <th><i class="fas fa-calendar"></i> Tanggal</th>
        <th><i class="fas fa-image"></i> Foto</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      mysqli_data_seek($testimoni, 0);
      while ($d = mysqli_fetch_assoc($testimoni)): 
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td>
          <span class="customer-name"><?= htmlspecialchars($d['nama']) ?></span>
        </td>
        <td>
          <div class="testimonial-text">
            <?= nl2br(htmlspecialchars($d['isi'])) ?>
          </div>
        </td>
        <td>
          <span class="date-badge">
            <i class="fas fa-calendar-alt"></i>
            <?= date('d/m/Y', strtotime($d['tanggal'])) ?>
          </span>
        </td>
        <td>
          <?php if (!empty($d['gambar'])): ?>
            <img src="<?= smart_image_url($d['gambar']) ?>" alt="Foto Testimoni" 
                 class="testimonial-image" onerror="this.src='<?= upload_url('default.svg') ?>'">
          <?php else: ?>
            <div class="image-placeholder">
              <i class="fas fa-image"></i>
            </div>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state">
    <i class="fas fa-comments"></i>
    <h3>Belum Ada Testimoni</h3>
    <p>Belum ada testimoni dari pelanggan yang tersedia</p>
  </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('💬 Data Testimoni', $content, 'testimoni');
?>
