<!DOCTYPE html>
<html>
<head>
  <title>Daftar Menu Dimsum</title>
  <style>
    body {
      font-family: sans-serif;
      background: #c0d9ae;
      margin: 0;
      padding: 20px;
    }
    .logo-container {
      text-align: center;
      margin-bottom: 10px;
    }
    .logo-container img {
      width: 120px;
      height: auto;
      border-radius: 50%;
      border: 3px solid #f5b7c2;
    }
    .logo-text {
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      color: #333;
      margin-bottom: 30px;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #222;
    }
    .menu-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }
    .menu-card {
      background: #fff;
      border-radius: 10px;
      width: 250px;
      padding: 15px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      border: 2px solid #f5b7c2;
      transition: 0.3s;
    }
    .menu-card:hover {
      box-shadow: 0 0 12px #f5b7c2;
    }
    .menu-card img {
      width: 200px;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
      box-shadow: 0 0 6px #f5b7c2;
    }
    .menu-card h3 {
      margin: 0;
      font-size: 18px;
      text-align: center;
      color: #2e4e2d;
    }
    .menu-card p {
      font-size: 14px;
      color: #444;
      text-align: center;
      min-height: 50px;
    }
    .harga {
      font-weight: bold;
      color: #d35400;
      margin: 5px 0 10px;
    }
    .btn {
      background: #f5b7c2;
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 6px;
      font-weight: bold;
      transition: 0.3s;
    }
    .btn:hover {
      background: #e890a8;
    }
  </style>
</head>
<body>

  <!-- LOGO -->
  <div class="logo-container">
    <img src="../../../public/uploads/nuns.jpg" alt="Logo Dimsum">
  </div>
  <div class="logo-text">Nun's Dimsum</div>

  <h2>Daftar Menu Dimsum</h2>

  <div class="menu-container">

    <!-- 1 -->
    <div class="menu-card">
      <img src="../../../public/uploads/ori.jpg" alt="Dimsum Original">
      <h3>Dimsum Original</h3>
      <p>Dimsum ayam dengan rasa original gurih</p>
      <div class="harga">Rp 10.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 2 -->
    <div class="menu-card">
      <img src="../../../public/uploads/jamur.jpg" alt="Dimsum Jamur">
      <h3>Dimsum Jamur</h3>
      <p>Dimsum isi ayam dan jamur kancing lezat</p>
      <div class="harga">Rp 11.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 3 -->
    <div class="menu-card">
      <img src="../../../public/uploads/lava.jpg" alt="Dimsum Lava">
      <h3>Dimsum Lava</h3>
      <p>Dimsum pedas dengan saus lava spesial</p>
      <div class="harga">Rp 12.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 4 -->
    <div class="menu-card">
      <img src="../../../public/uploads/mentai.jpg" alt="Dimsum Mentai">
      <h3>Dimsum Mentai</h3>
      <p>Dimsum creamy dengan topping mentai</p>
      <div class="harga">Rp 13.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 5 -->
    <div class="menu-card">
      <img src="../../../public/uploads/moza.jpg" alt="Dimsum Moza">
      <h3>Dimsum Moza</h3>
      <p>Dimsum lumer dengan keju mozzarella</p>
      <div class="harga">Rp 14.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 6 -->
    <div class="menu-card">
      <img src="../../../public/uploads/rumputlaut.jpg" alt="Dimsum Rumput Laut">
      <h3>Dimsum Rumput Laut</h3>
      <p>Dimsum dengan balutan rumput laut</p>
      <div class="harga">Rp 12.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 7 -->
    <div class="menu-card">
      <img src="../../../public/uploads/mix.jpg" alt="Dimsum Mix">
      <h3>Dimsum Mix</h3>
      <p>Kombinasi berbagai varian dimsum favorit</p>
      <div class="harga">Rp 15.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 8 -->
    <div class="menu-card">
      <img src="../../../public/uploads/party.jpg" alt="Dimsum Party">
      <h3>Dimsum Party</h3>
      <p>Paket 16 pcs dimsum berbagai rasa</p>
      <div class="harga">Rp 100.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

    <!-- 9 -->
    <div class="menu-card">
      <img src="../../../public/uploads/gyoza.jpg" alt="Gyoza">
      <h3>Gyoza</h3>
      <p>Gyoza Jepang isi ayam lembut dan juicy</p>
      <div class="harga">Rp 10.000</div>
      <a class="btn" href="beli.php">Beli Sekarang</a>
    </div>

  </div>

</body>
</html>
