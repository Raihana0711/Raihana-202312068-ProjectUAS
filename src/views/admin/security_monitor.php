<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__ . '/../../includes/config.php';
include __DIR__ . '/../../includes/admin_layout.php';
require_once __DIR__ . '/../../includes/rate_limiter.php';
require_once __DIR__ . '/../../includes/security_config.php';

require_admin();
$admin = $_SESSION['admin'];

// Initialize Rate Limiter
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $rateLimiter = initializeRateLimiter($pdo);
    
} catch (PDOException $e) {
    $error = "Failed to initialize Rate Limiter: " . $e->getMessage();
    $rateLimiter = null;
}

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'unblock_ip':
            if (isset($_POST['ip']) && $rateLimiter) {
                $ip = $_POST['ip'];
                $rateLimiter->unblock($ip);
                $success = "IP $ip berhasil di-unblock";
                logSecurityEvent('admin_unblock_ip', ['ip' => $ip, 'admin' => $admin]);
            }
            break;
            
        case 'unblock_user':
            if (isset($_POST['username']) && $rateLimiter) {
                $username = $_POST['username'];
                $rateLimiter->unblock(null, $username);
                $success = "User $username berhasil di-unblock";
                logSecurityEvent('admin_unblock_user', ['username' => $username, 'admin' => $admin]);
            }
            break;
    }
}

// Prepare content
ob_start();
?>
<style>
  .security-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-5px);
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
  }

  .stat-icon.blocked { background: linear-gradient(135deg, #e74c3c, #c0392b); }
  .stat-icon.attempts { background: linear-gradient(135deg, #f39c12, #e67e22); }
  .stat-icon.success { background: linear-gradient(135deg, #27ae60, #229954); }

  .stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: var(--dark-green);
    margin-bottom: 0.5rem;
  }

  .stat-label {
    color: #6c757d;
    font-size: 0.9rem;
  }

  .blocked-list {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
  }

  .blocked-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
    transition: background 0.3s ease;
  }

  .blocked-item:hover {
    background: rgba(168, 230, 207, 0.1);
  }

  .blocked-info h4 {
    margin-bottom: 0.5rem;
    color: var(--dark-green);
  }

  .blocked-details {
    font-size: 0.85rem;
    color: #6c757d;
  }

  .unblock-btn {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
  }

  .unblock-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
  }

  .alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
  }

  .alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
  }

  .alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
  }

  .section-title {
    font-size: 1.5rem;
    color: var(--dark-green);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
  }
</style>

<div class="security-container">
  <?php if (!empty($success)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($rateLimiter): ?>
    <?php 
    $stats = $rateLimiter->getStatistics();
    $currentTime = date('Y-m-d H:i:s');
    
    // Get blocked IPs
    $blockedIPs = $pdo->query("
        SELECT ip_address, blocked_until, attempts, last_attempt 
        FROM login_attempts_ip 
        WHERE blocked_until > '$currentTime' 
        ORDER BY blocked_until DESC
    ")->fetchAll();
    
    // Get blocked users
    $blockedUsers = $pdo->query("
        SELECT username, blocked_until, attempts, last_attempt 
        FROM login_attempts_user 
        WHERE blocked_until > '$currentTime' 
        ORDER BY blocked_until DESC
    ")->fetchAll();
    ?>

    <!-- Statistics -->
    <h2 class="section-title">
      <i class="fas fa-chart-bar"></i>
      Statistik Keamanan
    </h2>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blocked">
          <i class="fas fa-ban"></i>
        </div>
        <div class="stat-number"><?= $stats['blocked_ips'] ?></div>
        <div class="stat-label">IP Diblokir</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon blocked">
          <i class="fas fa-user-slash"></i>
        </div>
        <div class="stat-number"><?= $stats['blocked_users'] ?></div>
        <div class="stat-label">User Diblokir</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon attempts">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-number"><?= $stats['recent_attempts'] ?></div>
        <div class="stat-label">Percobaan (1 Jam)</div>
      </div>
    </div>

    <!-- Blocked IPs -->
    <h3 class="section-title">
      <i class="fas fa-globe"></i>
      IP Address yang Diblokir
    </h3>

    <div class="blocked-list">
      <?php if (!empty($blockedIPs)): ?>
        <?php foreach ($blockedIPs as $blocked): ?>
          <div class="blocked-item">
            <div class="blocked-info">
              <h4><?= htmlspecialchars($blocked['ip_address']) ?></h4>
              <div class="blocked-details">
                <i class="fas fa-clock"></i> Diblokir sampai: <?= date('d/m/Y H:i', strtotime($blocked['blocked_until'])) ?><br>
                <i class="fas fa-redo"></i> Percobaan: <?= $blocked['attempts'] ?>x | 
                <i class="fas fa-calendar"></i> Terakhir: <?= date('d/m/Y H:i', strtotime($blocked['last_attempt'])) ?>
              </div>
            </div>
            <form method="POST" style="display: inline;">
              <input type="hidden" name="action" value="unblock_ip">
              <input type="hidden" name="ip" value="<?= htmlspecialchars($blocked['ip_address']) ?>">
              <button type="submit" class="unblock-btn" onclick="return confirm('Unblock IP ini?')">
                <i class="fas fa-unlock"></i> Unblock
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-data">
          <i class="fas fa-info-circle"></i> Tidak ada IP yang sedang diblokir
        </div>
      <?php endif; ?>
    </div>

    <!-- Blocked Users -->
    <h3 class="section-title">
      <i class="fas fa-users"></i>
      Username yang Diblokir
    </h3>

    <div class="blocked-list">
      <?php if (!empty($blockedUsers)): ?>
        <?php foreach ($blockedUsers as $blocked): ?>
          <div class="blocked-item">
            <div class="blocked-info">
              <h4><?= htmlspecialchars($blocked['username']) ?></h4>
              <div class="blocked-details">
                <i class="fas fa-clock"></i> Diblokir sampai: <?= date('d/m/Y H:i', strtotime($blocked['blocked_until'])) ?><br>
                <i class="fas fa-redo"></i> Percobaan: <?= $blocked['attempts'] ?>x | 
                <i class="fas fa-calendar"></i> Terakhir: <?= date('d/m/Y H:i', strtotime($blocked['last_attempt'])) ?>
              </div>
            </div>
            <form method="POST" style="display: inline;">
              <input type="hidden" name="action" value="unblock_user">
              <input type="hidden" name="username" value="<?= htmlspecialchars($blocked['username']) ?>">
              <button type="submit" class="unblock-btn" onclick="return confirm('Unblock user ini?')">
                <i class="fas fa-unlock"></i> Unblock
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-data">
          <i class="fas fa-info-circle"></i> Tidak ada user yang sedang diblokir
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="alert alert-danger">
      <i class="fas fa-exclamation-triangle"></i> 
      Rate Limiter tidak dapat diinisialisasi. Periksa koneksi database.
    </div>
  <?php endif; ?>

  <!-- Configuration Info -->
  <div class="blocked-list">
    <h3 class="section-title">
      <i class="fas fa-cog"></i>
      Konfigurasi Rate Limiting
    </h3>
    <div style="padding: 1rem;">
      <p><strong>Maximum Attempts:</strong> <?= RATE_LIMIT_MAX_ATTEMPTS ?> percobaan</p>
      <p><strong>Time Window:</strong> <?= RATE_LIMIT_TIME_WINDOW / 60 ?> menit</p>
      <p><strong>Block Duration:</strong> <?= RATE_LIMIT_BLOCK_DURATION / 60 ?> menit</p>
      <p><strong>Session Timeout:</strong> <?= SESSION_TIMEOUT / 60 ?> menit</p>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
render_admin_layout('Security Monitor', $content, 'security');
?>
