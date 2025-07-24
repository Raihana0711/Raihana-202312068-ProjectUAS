<?php
/**
 * Login Page for Nun's Dimsum with Rate Limiting Protection
 */

include __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/rate_limiter.php';

// Initialize Rate Limiter
try {
    // Create PDO connection for RateLimiter
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Initialize RateLimiter with custom settings
    // 5 attempts, 15 minutes window, 30 minutes block
    $rateLimiter = new RateLimiter($pdo, 5, 900, 1800);
    
} catch (PDOException $e) {
    error_log("RateLimiter initialization failed: " . $e->getMessage());
    $rateLimiter = null;
}

$error = '';
$clientIP = getClientIP();
$remainingAttempts = null;
$isBlocked = false;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        // Check if IP or username is blocked (if RateLimiter is available)
        if ($rateLimiter) {
            $blockInfo = $rateLimiter->isBlocked($clientIP, $username);
            
            if ($blockInfo['blocked']) {
                $error = $rateLimiter->getBlockedMessage($blockInfo);
                $isBlocked = true;
            } else {
                $remainingAttempts = $rateLimiter->getRemainingAttempts($clientIP, $username);
            }
        }
        
        // Only attempt authentication if not blocked
        if (!$isBlocked) {
            // Use the authenticateUser function
            $user = authenticateUser($username, $password, $conn);
            
            if ($user) {
                // Login berhasil - clear rate limiting records
                if ($rateLimiter) {
                    $rateLimiter->recordSuccessfulLogin($clientIP, $username);
                    logActivity("Successful login: {$username} from IP: {$clientIP}");
                }
                
                if ($user['role'] === 'admin') {
                    $_SESSION['admin'] = $user['username'];
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['username'];
                    redirect_to('admin');
                } else {
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['username'];
                    redirect_to('user');
                }
            } else {
                // Login gagal - record failed attempt
                if ($rateLimiter) {
                    $rateLimiter->recordFailedAttempt($clientIP, $username);
                    $remainingAttempts = $rateLimiter->getRemainingAttempts($clientIP, $username);
                    logActivity("Failed login attempt: {$username} from IP: {$clientIP}");
                    
                    if ($remainingAttempts <= 0) {
                        $error = 'Terlalu banyak percobaan login yang gagal. Akun Anda telah diblokir sementara.';
                    } else {
                        $error = "Username atau password salah! Sisa percobaan: {$remainingAttempts}";
                    }
                } else {
                    $error = 'Username atau password salah!';
                }
            }
        }
    }
} else {
    // Check if already blocked on page load
    if ($rateLimiter) {
        $blockInfo = $rateLimiter->isBlocked($clientIP);
        if ($blockInfo['blocked']) {
            $error = $rateLimiter->getBlockedMessage($blockInfo);
            $isBlocked = true;
        } else {
            $remainingAttempts = $rateLimiter->getRemainingAttempts($clientIP);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #f1f8e8;
            --secondary-green: #dce8c6;
            --accent-green: #aac38a;
            --dark-green: #2e4e2d;
            --primary-pink: #f5b7c2;
            --secondary-pink: #e890a8;
            --light-pink: #fff0f2;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 50%, var(--light-pink) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(245, 183, 194, 0.1) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(46, 78, 45, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--dark-green) 0%, var(--accent-green) 100%);
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100px;
            height: 100px;
            background: rgba(245, 183, 194, 0.2);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 0.3; }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            position: relative;
            z-index: 2;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 16px;
            font-weight: 400;
            position: relative;
            z-index: 2;
        }
        
        .login-body {
            padding: 40px;
            position: relative;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            color: var(--dark-green);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid var(--secondary-green);
            border-radius: 15px;
            font-size: 16px;
            background: var(--primary-green);
            color: var(--dark-green);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-pink);
            background: white;
            box-shadow: 0 0 0 4px rgba(245, 183, 194, 0.2);
            transform: translateY(-2px);
        }
        
        .form-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-green);
            font-size: 16px;
            z-index: 3;
            pointer-events: none;
        }
        
        .demo-info {
            background: var(--light-pink);
            border: 1px solid var(--primary-pink);
            border-radius: 10px;
            padding: 12px 15px;
            margin: 15px 0;
            font-size: 13px;
            color: var(--dark-green);
        }
        
        .demo-info strong {
            color: var(--primary-pink);
        }
        
        .warning-info {
            background: linear-gradient(135deg, #fff4e6, #ffedcc);
            border: 1px solid #f39c12;
            border-radius: 10px;
            padding: 12px 15px;
            margin: 15px 0;
            font-size: 13px;
            color: #d68910;
            animation: pulse-warning 2s ease-in-out infinite;
        }
        
        .warning-info i {
            color: #f39c12;
            margin-right: 8px;
        }
        
        .warning-info strong {
            color: #d68910;
        }
        
        .attempts-count {
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
        }
        
        @keyframes pulse-warning {
            0%, 100% { box-shadow: 0 0 5px rgba(243, 156, 18, 0.3); }
            50% { box-shadow: 0 0 15px rgba(243, 156, 18, 0.5); }
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-pink) 0%, var(--secondary-pink) 100%);
            border: none;
            border-radius: 15px;
            padding: 16px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(245, 183, 194, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-login:disabled {
            background: linear-gradient(135deg, #ccc 0%, #aaa 100%);
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .btn-login:disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: var(--accent-green);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 5px 15px;
        }
        
        .links a:hover {
            color: var(--primary-pink);
            transform: translateY(-2px);
        }
        
        .links a i {
            margin-right: 5px;
        }
        
        .error-alert {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #c33;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                border-radius: 20px;
            }
            
            .login-header {
                padding: 30px 30px 25px;
            }
            
            .login-body {
                padding: 30px 25px;
            }
            
            .logo {
                width: 70px;
                height: 70px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="<?= smart_image_url('nuns.jpg') ?>" alt="Nun's Dimsum Logo" class="logo" onerror="this.src='<?= upload_url('default.svg') ?>'">
            <h1>Nun's Dimsum</h1>
            <p>Masuk ke akun Anda</p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-user form-icon"></i>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="<?= $isBlocked ? 'Akun diblokir sementara' : 'Masukkan username Anda' ?>"
                               <?= $isBlocked ? 'disabled readonly' : 'required' ?>
                               autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="<?= $isBlocked ? 'Akun diblokir sementara' : 'Masukkan password Anda' ?>"
                               <?= $isBlocked ? 'disabled readonly' : 'required' ?>
                               autocomplete="current-password">
                    </div>
                </div>
                
                <?php if (!$isBlocked && $remainingAttempts !== null && $remainingAttempts < 5): ?>
                <div class="warning-info">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Sisa percobaan login: <span class="attempts-count"><?= $remainingAttempts ?></span>
                </div>
                <?php endif; ?>
                
                <div class="demo-info">
                    <i class="fas fa-info-circle" style="color: var(--primary-pink);"></i>
                    <strong>Demo Login:</strong><br>
                    • Admin: <code>admin</code> / <code>admin123</code><br>
                    • User: <code>user</code> / <code>user123</code>
                </div>
                
                <button type="submit" class="btn-login" <?= $isBlocked ? 'disabled' : '' ?>>
                    <?php if ($isBlocked): ?>
                        <i class="fas fa-ban" style="margin-right: 8px;"></i>
                        <span>Diblokir Sementara</span>
                    <?php else: ?>
                        <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                        <span>Masuk Sekarang</span>
                    <?php endif; ?>
                </button>
            </form>
            
            <div class="links">
                <a href="<?= base_url('index.php?page=register') ?>">
                    <i class="fas fa-user-plus"></i>
                    Belum punya akun? Daftar
                </a>
                <br>
                <a href="<?= base_url('index.php?page=home') ?>">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Add loading animation when form is submitted
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-login');
            const btnText = submitBtn.querySelector('span');
            
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.8';
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Memproses...';
        });
        
        // Add input focus animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.form-icon').style.color = 'var(--primary-pink)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.form-icon').style.color = 'var(--accent-green)';
            });
        });
    </script>
</body>
</html>
