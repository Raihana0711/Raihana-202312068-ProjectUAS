<?php
include __DIR__ . '/../../includes/config.php';

$message = '';
if (isset($_POST['register'])) {
  $username = sanitize($_POST['username']);
  $password = $_POST['password'];
  
  // Validasi input
  if (empty($username) || empty($password)) {
    $message = '<div class="alert alert-danger">Username dan password harus diisi!</div>';
  } else {
    // Cek apakah username sudah ada
    $check = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
      $message = '<div class="alert alert-danger">Username sudah digunakan!</div>';
    } else {
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $query = "INSERT INTO user (username, password, role) VALUES('$username', '$password_hash', 'user')";
      
      if (mysqli_query($conn, $query)) {
        $message = '<div class="alert alert-success">Registrasi berhasil! Silakan login.</div>';
        // Redirect after 2 seconds
        echo '<script>setTimeout(function(){ window.location.href = "index.php?page=login"; }, 2000);</script>';
      } else {
        $message = '<div class="alert alert-danger">Terjadi kesalahan saat mendaftar.</div>';
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        /* Animated background */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(245, 183, 194, 0.1) 0%, transparent 50%);
            animation: float 8s ease-in-out infinite;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0px, 0px) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(46, 78, 45, 0.15);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--dark-green) 0%, var(--accent-green) 100%);
            color: white;
            padding: 35px 40px 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .register-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 120px;
            height: 120px;
            background: rgba(245, 183, 194, 0.2);
            border-radius: 50%;
            animation: pulse 5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.3); opacity: 0.2; }
        }
        
        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            position: relative;
            z-index: 2;
        }
        
        .register-header h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .register-header p {
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 2;
        }
        
        .register-body {
            padding: 35px 40px 40px;
        }
        
        .form-group {
            margin-bottom: 22px;
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
        
        .btn-register {
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
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(245, 183, 194, 0.4);
        }
        
        .btn-register::before {
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
        
        .btn-register:hover::before {
            width: 300px;
            height: 300px;
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
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
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
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .register-container {
                margin: 10px;
                border-radius: 20px;
            }
            
            .register-header {
                padding: 30px 25px 20px;
            }
            
            .register-body {
                padding: 30px 25px;
            }
            
            .logo {
                width: 60px;
                height: 60px;
            }
            
            .register-header h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="<?= smart_image_url('nuns.jpg') ?>" alt="Nun's Dimsum Logo" class="logo" onerror="this.src='<?= upload_url('default.svg') ?>'">
            <h2>Daftar Akun</h2>
            <p>Bergabunglah dengan Nun's Dimsum</p>
        </div>
        
        <div class="register-body">
            <?php echo $message; ?>
            
            <form method="POST" id="registerForm">
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
                               placeholder="Masukkan username unik Anda"
                               required 
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
                               placeholder="Minimal 6 karakter"
                               required 
                               autocomplete="new-password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-check-circle"></i> Konfirmasi Password
                    </label>
                    <div style="position: relative;">
                        <i class="fas fa-check-circle form-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Ulangi password Anda"
                               required 
                               autocomplete="new-password">
                    </div>
                </div>
                
                <button type="submit" name="register" class="btn-register">
                    <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                    <span>Daftar Sekarang</span>
                </button>
            </form>
            
            <div class="links">
                <a href="<?= base_url('index.php?page=login') ?>">
                    <i class="fas fa-sign-in-alt"></i>
                    Sudah punya akun? Login
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
        // Enhanced form validation and animations
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.querySelector('.btn-register');
            const btnText = submitBtn.querySelector('span');
            
            // Password validation
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Password dan konfirmasi password tidak sama!', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password minimal 6 karakter!', 'error');
                return false;
            }
            
            // Loading animation
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.8';
            btnText.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Memproses...';
        });
        
        // Input focus animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.form-icon').style.color = 'var(--primary-pink)';
                this.parentElement.querySelector('.form-icon').style.transform = 'translateY(-50%) scale(1.1)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.form-icon').style.color = 'var(--accent-green)';
                this.parentElement.querySelector('.form-icon').style.transform = 'translateY(-50%) scale(1)';
            });
        });
        
        // Real-time password strength indicator
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrength(strength);
        });
        
        confirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.style.borderColor = '#dc3545';
                this.style.boxShadow = '0 0 0 4px rgba(220, 53, 69, 0.2)';
            } else if (confirm && password === confirm) {
                this.style.borderColor = '#28a745';
                this.style.boxShadow = '0 0 0 4px rgba(40, 167, 69, 0.2)';
            } else {
                this.style.borderColor = 'var(--secondary-green)';
                this.style.boxShadow = 'none';
            }
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
        
        function updatePasswordStrength(strength) {
            const passwordInput = document.getElementById('password');
            
            if (strength === 0) {
                passwordInput.style.borderColor = 'var(--secondary-green)';
            } else if (strength <= 2) {
                passwordInput.style.borderColor = '#ffc107';
            } else if (strength <= 3) {
                passwordInput.style.borderColor = '#fd7e14';
            } else {
                passwordInput.style.borderColor = '#28a745';
            }
        }
        
        function showAlert(message, type) {
            // Create alert element
            const alert = document.createElement('div');
            alert.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
            alert.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i> ${message}`;
            
            // Insert at the top of form
            const form = document.getElementById('registerForm');
            form.insertBefore(alert, form.firstChild);
            
            // Remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
    </script>
</body>
</html>
