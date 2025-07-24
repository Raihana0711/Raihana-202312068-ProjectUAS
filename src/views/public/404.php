<!DOCTYPE html>
<html>
<head>
    <title>Halaman Tidak Ditemukan - <?= APP_NAME ?></title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #c0d9ae, #dce8c6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }
        
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
        }
        
        h1 {
            font-size: 120px;
            color: #f5b7c2;
            margin: 0;
            font-weight: bold;
        }
        
        h2 {
            color: #2e4e2d;
            margin: 20px 0;
            font-size: 32px;
        }
        
        p {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
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
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>Oops! Halaman Tidak Ditemukan</h2>
        <p>Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman telah dipindahkan atau alamat URL salah.</p>
        <a href="<?= base_url('index.php?page=home') ?>" class="btn">Kembali ke Beranda</a>
    </div>
</body>
</html>
