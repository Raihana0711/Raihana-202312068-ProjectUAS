-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Jul 2025 pada 12.43
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_nunsdimsum`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateTransaction` (IN `p_id_user` INT, IN `p_id_pembeli` INT, IN `p_total` INT, IN `p_metode_pembayaran` ENUM('tunai','transfer','ewallet'), IN `p_catatan` TEXT, OUT `p_id_transaksi` INT)   BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  
  START TRANSACTION;
  INSERT INTO transaksi (id_user, id_pembeli, tanggal, total, metode_pembayaran, catatan)
  VALUES (p_id_user, p_id_pembeli, CURDATE(), p_total, p_metode_pembayaran, p_catatan);
  SET p_id_transaksi = LAST_INSERT_ID();
  COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateUser` (IN `p_username` VARCHAR(50), IN `p_password_plain` VARCHAR(100), IN `p_role` ENUM('admin','user'))   BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  
  START TRANSACTION;
  INSERT INTO user (username, password, role) 
  VALUES (p_username, p_password_plain, p_role);
  COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetLaporanPenjualan` (IN `p_tanggal_mulai` DATE, IN `p_tanggal_sampai` DATE)   BEGIN
    DECLARE v_mulai DATE DEFAULT COALESCE(p_tanggal_mulai, '1900-01-01');
    DECLARE v_sampai DATE DEFAULT COALESCE(p_tanggal_sampai, CURDATE());
    
    SELECT 
        DATE(t.tanggal) as tanggal,
        COUNT(t.id_transaksi) as jumlah_transaksi,
        SUM(CASE WHEN t.status = 'selesai' THEN t.total ELSE 0 END) as total_penjualan,
        SUM(CASE WHEN t.status = 'selesai' THEN 1 ELSE 0 END) as transaksi_berhasil,
        GROUP_CONCAT(
            CONCAT(dt.nama_menu, ' (', dt.jumlah, ')') 
            ORDER BY dt.jumlah DESC 
            SEPARATOR ', '
        ) as menu_terjual
    FROM transaksi t
    LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    WHERE t.tanggal BETWEEN v_mulai AND v_sampai
    GROUP BY DATE(t.tanggal)
    ORDER BY tanggal DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStatistikRingkas` (IN `p_tanggal_mulai` DATE, IN `p_tanggal_sampai` DATE)   BEGIN
    DECLARE v_mulai DATE DEFAULT COALESCE(p_tanggal_mulai, '1900-01-01');
    DECLARE v_sampai DATE DEFAULT COALESCE(p_tanggal_sampai, CURDATE());
    
    SELECT 
        COUNT(*) as total_transaksi,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as transaksi_selesai,
        SUM(CASE WHEN status = 'pending' OR status IS NULL THEN 1 ELSE 0 END) as transaksi_pending,
        SUM(CASE WHEN status = 'selesai' THEN total ELSE 0 END) as total_penjualan,
        AVG(CASE WHEN status = 'selesai' THEN total ELSE NULL END) as rata_rata_penjualan
    FROM transaksi
    WHERE tanggal BETWEEN v_mulai AND v_sampai;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserByUsername` (IN `p_username` VARCHAR(50))   BEGIN
  SELECT id_user, username, password, role, created_at
  FROM user 
  WHERE username = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserPassword` (IN `p_user_id` INT, IN `p_new_password_hashed` VARCHAR(255))   BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;
  
  START TRANSACTION;
  UPDATE user 
  SET password = p_new_password_hashed, updated_at = CURRENT_TIMESTAMP
  WHERE id_user = p_user_id;
  
  IF ROW_COUNT() = 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User tidak ditemukan';
  END IF;
  COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `nama_menu`, `harga`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 'Siomay Ayam', 25000, 1, 25000),
(2, 1, 7, 'Es Teh Manis', 15000, 2, 30000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `gambar_menu`
--

CREATE TABLE `gambar_menu` (
  `id_gambar` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nama_file` varchar(100) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Dimsum'),
(2, 'Minuman'),
(3, 'Makanan Lain');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total` int(11) NOT NULL,
  `jumlah_item` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts_ip`
--

CREATE TABLE `login_attempts_ip` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `first_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts_user`
--

CREATE TABLE `login_attempts_user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `first_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `gambar` varchar(100) DEFAULT NULL,
  `id_kategori` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `deskripsi`, `harga`, `gambar`, `id_kategori`, `created_at`) VALUES
(1, 'Gyoza', 'Gyoza jepang isi ayam lembut dan juicy', 25000, 'https://images.unsplash.com/photo-1563379091339-03246963d29a?w=500&amp;h=400&amp;fit=crop', 1, '2025-07-22 09:50:27'),
(2, 'Dimsum Party', 'Dimsum ayam 6 pcs bisa req ucapan', 100000, 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=500&amp;h=400&amp;fit=crop', 1, '2025-07-22 09:50:27'),
(3, 'Dimsum Mix', 'Dimsum ayam dengan berbagai rasa', 35000, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=500&amp;h=400&amp;fit=crop', 1, '2025-07-22 09:50:27'),
(4, 'Dimsum Rumput Laut', 'Dimsum ayam dengan toping rumput laut', 20000, 'https://images.unsplash.com/photo-1601314002957-db408ac96ec1?w=500&amp;h=400&amp;fit=crop', 1, '2025-07-22 09:50:27'),
(5, 'Dimsum Mentai', 'Dimsum creamy dengan saos mentai gurih', 28000, 'https://images.unsplash.com/photo-1597318801137-d2d3da58cdde?w=500&amp;h=400&amp;fit=crop', 2, '2025-07-22 09:50:27'),
(6, 'Dimsum lava', 'Dimsum ayam dengan saos lava', 25000, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=500&amp;amp;h=400&amp;amp;fit=crop', 2, '2025-07-22 09:50:27'),
(7, 'Dimsum Jamur', 'Dimsum ayam dengan toping jamur di atasnya', 18000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=500&amp;h=400&amp;fit=crop', 2, '2025-07-22 09:50:27'),
(8, 'Dimsum Original', 'Dimsum ayam dengan rasa original gurih', 15000, 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxITEhUTEhMWFhUXFxYXGBgXFRUYFhUXFxcWFxgaG', 2, '2025-07-22 09:50:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'user (Order dari Keranjang)', '-', '-', '2025-07-24 05:10:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `testimoni`
--

CREATE TABLE `testimoni` (
  `id_testimoni` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  `gambar` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `rating` int(11) DEFAULT 5 CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `testimoni`
--

INSERT INTO `testimoni` (`id_testimoni`, `nama`, `isi`, `tanggal`, `gambar`, `status`, `rating`, `created_at`) VALUES
(1, 'Maria Garcia', 'Tempatnya nyaman, cocok untuk keluarga', '2024-02-01', NULL, 'aktif', 5, '2025-07-22 09:50:27'),
(2, 'John Doe', 'Dimsum terenak di kota ini!', '2024-01-15', NULL, 'aktif', 5, '2025-07-22 09:50:27'),
(3, 'Siti Aminah', 'Pelayanan ramah dan makanan lezat', '2024-02-10', NULL, 'aktif', 4, '2025-07-22 09:50:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_pembeli` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `total` int(11) NOT NULL,
  `status` enum('pending','diproses','selesai','dibatalkan') DEFAULT 'pending',
  `metode_pembayaran` enum('tunai','transfer','ewallet') DEFAULT 'tunai',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_pembeli`, `tanggal`, `total`, `status`, `metode_pembayaran`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025-07-24', 55000, 'selesai', 'tunai', 'Pesanan dibuat dari keranjang', '2025-07-24 05:10:55', '2025-07-24 05:12:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2a$12$5Vga3zqU0UN9q7AXadCroeZAvbMILToGUBaSUezqAOP83LcwbR.Py', 'admin', '2025-07-22 09:50:27', '2025-07-22 09:50:27'),
(2, 'user', '$2a$12$OYZ9g2/YgdGS5PqmgzSFnuuQTsRri0GrBwXXWieulu2R0qJZfMGyi', 'user', '2025-07-22 09:50:27', '2025-07-22 09:50:27');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_menu_terlaris`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_menu_terlaris` (
`id_menu` int(11)
,`nama_menu` varchar(100)
,`harga` int(11)
,`nama_kategori` varchar(50)
,`total_terjual` decimal(32,0)
,`total_pendapatan` decimal(32,0)
,`jumlah_order` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_transaksi_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_transaksi_harian` (
`tanggal_transaksi` date
,`jumlah_transaksi` bigint(21)
,`transaksi_selesai` decimal(22,0)
,`total_penjualan` decimal(32,0)
,`rata_rata_penjualan` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_menu_terlaris`
--
DROP TABLE IF EXISTS `v_menu_terlaris`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_menu_terlaris`  AS SELECT `m`.`id_menu` AS `id_menu`, `m`.`nama_menu` AS `nama_menu`, `m`.`harga` AS `harga`, `k`.`nama_kategori` AS `nama_kategori`, coalesce(sum(`dt`.`jumlah`),0) AS `total_terjual`, coalesce(sum(`dt`.`subtotal`),0) AS `total_pendapatan`, count(distinct `dt`.`id_transaksi`) AS `jumlah_order` FROM (((`menu` `m` left join `kategori` `k` on(`m`.`id_kategori` = `k`.`id_kategori`)) left join `detail_transaksi` `dt` on(`m`.`id_menu` = `dt`.`id_menu`)) left join `transaksi` `t` on(`dt`.`id_transaksi` = `t`.`id_transaksi` and `t`.`status` = 'selesai')) GROUP BY `m`.`id_menu`, `m`.`nama_menu`, `m`.`harga`, `k`.`nama_kategori` ORDER BY coalesce(sum(`dt`.`jumlah`),0) DESC ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_transaksi_harian`
--
DROP TABLE IF EXISTS `v_transaksi_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_transaksi_harian`  AS SELECT cast(`t`.`tanggal` as date) AS `tanggal_transaksi`, count(0) AS `jumlah_transaksi`, sum(case when `t`.`status` = 'selesai' then 1 else 0 end) AS `transaksi_selesai`, sum(case when `t`.`status` = 'selesai' then `t`.`total` else 0 end) AS `total_penjualan`, avg(case when `t`.`status` = 'selesai' then `t`.`total` else NULL end) AS `rata_rata_penjualan` FROM `transaksi` AS `t` GROUP BY cast(`t`.`tanggal` as date) ORDER BY cast(`t`.`tanggal` as date) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `idx_detail_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `gambar_menu`
--
ALTER TABLE `gambar_menu`
  ADD PRIMARY KEY (`id_gambar`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `unique_user_menu` (`id_user`,`id_menu`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `idx_keranjang_user` (`id_user`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `login_attempts_ip`
--
ALTER TABLE `login_attempts_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_blocked` (`blocked_until`);

--
-- Indeks untuk tabel `login_attempts_user`
--
ALTER TABLE `login_attempts_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_blocked` (`blocked_until`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `idx_menu_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indeks untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id_testimoni`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pembeli` (`id_pembeli`),
  ADD KEY `idx_transaksi_user` (`id_user`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`),
  ADD KEY `idx_transaksi_status` (`status`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `gambar_menu`
--
ALTER TABLE `gambar_menu`
  MODIFY `id_gambar` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `login_attempts_ip`
--
ALTER TABLE `login_attempts_ip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `login_attempts_user`
--
ALTER TABLE `login_attempts_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  MODIFY `id_pembeli` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id_testimoni` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Ketidakleluasaan untuk tabel `gambar_menu`
--
ALTER TABLE `gambar_menu`
  ADD CONSTRAINT `gambar_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Ketidakleluasaan untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
