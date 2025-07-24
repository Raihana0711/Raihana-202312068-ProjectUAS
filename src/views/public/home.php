<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Selamat Datang</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #c0d9ae, #dce8c6);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .logo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin-bottom: 30px;
            object-fit: cover;
            border: 8px solid #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .welcome-container {
            text-align: center;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        h1 {
            color: #2e4e2d;
            margin-bottom: 20px;
            font-size: 48px;
            font-weight: bold;
        }
        
        .tagline {
            font-size: 20px;
            color: #3a5a40;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            background: #f5b7c2;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #e890a8;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: #aac38a;
        }
        
        .btn-secondary:hover {
            background: #8fa670;
        }
        
        .features {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1000px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 280px;
            text-align: center;
        }
        
        .feature-card h3 {
            color: #2e4e2d;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <img src="assets/images/logo/nuns.jpg" class="logo" alt="<?= APP_NAME ?> Logo">
    
    <div class="welcome-container">
        <h1>Selamat Datang di <?= APP_NAME ?></h1>
        <div class="tagline">
            🥟 Nikmati kelezatan dimsum autentik dengan cita rasa yang tak terlupakan! 🥟<br>
            Dibuat dengan cinta dan bahan berkualitas terbaik untuk kebahagiaan Anda.
        </div>
        
        <div class="action-buttons">
            <a href="index.php?page=login" class="btn">Masuk ke Akun</a>
            <a href="index.php?page=register" class="btn btn-secondary">Daftar Sekarang</a>
        </div>
    </div>
    
    <div class="features">
        <div class="feature-card">
            <h3>🍜 Menu Beragam</h3>
            <p>Berbagai varian dimsum lezat dengan cita rasa yang autentik dan menggugah selera</p>
        </div>
        
        <div class="feature-card">
            <h3>📱 Mudah Digunakan</h3>
            <p>Interface yang user-friendly untuk pengalaman berbelanja yang menyenangkan</p>
        </div>
        
        <div class="feature-card">
            <h3>💝 Kualitas Terbaik</h3>
            <p>Menggunakan bahan-bahan segar dan berkualitas tinggi untuk kepuasan pelanggan</p>
        </div>
    </div>
</body>
</html>
