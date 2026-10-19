-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Okt 2026 pada 20.15
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
-- Database: `ggmart`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alokasi_stok`
--

CREATE TABLE `alokasi_stok` (
  `kode_transaksi` varchar(15) NOT NULL,
  `id_mutasi` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_pokok` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alokasi_stok`
--

INSERT INTO `alokasi_stok` (`kode_transaksi`, `id_mutasi`, `jumlah`, `harga_pokok`) VALUES
('GG-0423BF783EA9', 76, 1, 45500.00),
('GG-08F3C54E61D1', 77, 2, 31500.00),
('GG-1F2166A235D4', 76, 1, 45500.00),
('GG-28318AA3CF53', 76, 1, 45500.00),
('GG-3A1F362BBB26', 77, 1, 31500.00),
('GG-3B6DBC7919F6', 77, 1, 31500.00),
('GG-4C794F2A8E80', 78, 1, 31500.00),
('GG-5000AB552B3B', 77, 1, 31500.00),
('GG-5000AB552B3B', 78, 1, 31500.00),
('GG-54E11921AA52', 76, 1, 45500.00),
('GG-54E11921AA52', 77, 1, 31500.00),
('GG-64AC5C9D9F7B', 78, 3, 31500.00),
('GG-757FE875C94F', 78, 3, 31500.00),
('GG-88BDD71C994D', 77, 1, 31500.00),
('GG-942561BB0503', 78, 1, 31500.00),
('GG-9542E5026F3B', 76, 1, 45500.00),
('GG-A985CD63D2A0', 78, 3, 31500.00),
('GG-B1D502F9E68A', 76, 1, 45500.00),
('GG-B1D502F9E68A', 77, 1, 31500.00),
('GG-B2D74B37361B', 77, 1, 31500.00),
('GG-B2D74B37361B', 78, 2, 31500.00),
('GG-B4E733AED04D', 78, 1, 31500.00),
('GG-C673285D062A', 78, 1, 31500.00),
('GG-E7C53E868EAE', 77, 1, 31500.00),
('GG-EAD64FEE545C', 77, 1, 31500.00),
('GG-F66DE86C0F47', 77, 2, 31500.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `kode_transaksi` varchar(15) NOT NULL,
  `kode_produk` varchar(15) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 1,
  `harga_satuan` decimal(12,2) DEFAULT NULL,
  `harga_pokok` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `kode_transaksi`, `kode_produk`, `jumlah`, `harga_satuan`, `harga_pokok`) VALUES
(93, 'GG-0423BF783EA9', 'PRD_00016875477', 1, 65000.00, 45500.00),
(94, 'GG-F66DE86C0F47', 'PRD_00877921510', 2, 45000.00, 31500.00),
(95, 'GG-64AC5C9D9F7B', 'PRD_02053183769', 3, 45000.00, 31500.00),
(96, 'GG-9542E5026F3B', 'PRD_00016875477', 1, 65000.00, 45500.00),
(97, 'GG-08F3C54E61D1', 'PRD_00877921510', 2, 45000.00, 31500.00),
(98, 'GG-A985CD63D2A0', 'PRD_02053183769', 3, 45000.00, 31500.00),
(99, 'GG-28318AA3CF53', 'PRD_00016875477', 1, 65000.00, 45500.00),
(100, 'GG-7CDDC6D37F3E', 'PRD_00877921510', 2, 45000.00, 0.00),
(101, 'GG-757FE875C94F', 'PRD_02053183769', 3, 45000.00, 31500.00),
(102, 'GG-73D4D10DCAA1', 'PRD_00016875477', 1, 65000.00, 0.00),
(105, 'GG-9D68EBB6D777', 'PRD_00016875477', 1, 65000.00, 0.00),
(106, 'GG-9D68EBB6D777', 'PRD_00877921510', 1, 45000.00, 0.00),
(112, 'GG-BC4714506D05', 'PRD_02053183769', 1, 45000.00, 0.00),
(113, 'GG-88BDD71C994D', 'PRD_00877921510', 1, 45000.00, 31500.00),
(114, 'GG-5000AB552B3B', 'PRD_00877921510', 1, 45000.00, 31500.00),
(115, 'GG-5000AB552B3B', 'PRD_02053183769', 1, 45000.00, 31500.00),
(116, 'GG-3B6DBC7919F6', 'PRD_00877921510', 1, 45000.00, 31500.00),
(122, 'GG-C673285D062A', 'PRD_02053183769', 1, 45000.00, 31500.00),
(125, 'GG-B2D74B37361B', 'PRD_00877921510', 1, 45000.00, 31500.00),
(126, 'GG-B2D74B37361B', 'PRD_02053183769', 2, 45000.00, 31500.00),
(127, 'GG-3A1F362BBB26', 'PRD_00877921510', 1, 45000.00, 31500.00),
(128, 'GG-4C794F2A8E80', 'PRD_02053183769', 1, 45000.00, 31500.00),
(129, 'GG-1F2166A235D4', 'PRD_00016875477', 1, 65000.00, 45500.00),
(130, 'GG-EAD64FEE545C', 'PRD_00877921510', 1, 45000.00, 31500.00),
(131, 'GG-E7C53E868EAE', 'PRD_00877921510', 1, 45000.00, 31500.00),
(132, 'GG-B4E733AED04D', 'PRD_02053183769', 1, 45000.00, 31500.00),
(150, 'GG-942561BB0503', 'PRD_02053183769', 1, 45000.00, 31500.00),
(151, 'GG-B1D502F9E68A', 'PRD_00016875477', 1, 65000.00, 45500.00),
(152, 'GG-B1D502F9E68A', 'PRD_00877921510', 1, 45000.00, 31500.00),
(153, 'GG-54E11921AA52', 'PRD_00016875477', 1, 65000.00, 45500.00),
(154, 'GG-54E11921AA52', 'PRD_00877921510', 1, 45000.00, 31500.00),
(173, 'GG-5805602CC18C', 'PRD_60274096744', 1, 60000.00, 0.00),
(174, 'GG-968560FAED70', 'PRD_09992327462', 1, 21000.00, 0.00),
(175, 'GG-968560FAED70', 'PRD_11650264519', 1, 40000.00, 0.00),
(176, 'GG-968560FAED70', 'PRD_66878007736', 1, 10000.00, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(76, 'Beras & Pangan Pokok', 'Produk beras lokal, jagung, kacang, dan bahan pokok lainnya.'),
(77, 'Minyak & Bumbu Dapur', 'Minyak lokal, minyak kelapa, rempah-rempah, garam, gula, dan bumbu.'),
(78, 'Kopi & Minuman', 'Kopi lokal NTT, teh herbal, minuman tradisional, dan olahan minuman lainnya.'),
(79, 'Madu & Herbal', 'Madu hutan, jahe, minyak kemiri, dan produk kesehatan tradisional.'),
(80, 'Camilan & Snack', 'Kerupuk, abon ikan, keripik singkong, dan makanan ringan.'),
(82, 'Kerajinan & Tenun', 'Tenun ikat, souvenir lokal, dan kerajinan tangan khas NTT.\n\n\nxxdnfsaj'),
(84, 'Sayur & Produk Segar', 'Sayuran segar dari petani lokal.'),
(85, 'Frozen Food', 'Makanan beku dan olahan penyimpanan dingin.');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `kode_produk` varchar(15) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mutasi_stok`
--

CREATE TABLE `mutasi_stok` (
  `id_mutasi` int(11) NOT NULL,
  `kode_produk` varchar(15) DEFAULT NULL,
  `type` enum('masuk','keluar') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_pokok` decimal(12,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `sisa_stok` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `kode_transaksi` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mutasi_stok`
--

INSERT INTO `mutasi_stok` (`id_mutasi`, `kode_produk`, `type`, `jumlah`, `harga_pokok`, `keterangan`, `sisa_stok`, `tanggal`, `kode_transaksi`) VALUES
(76, 'PRD_00016875477', 'masuk', 30, 45500.00, 'Penerimaan persediaan', 24, '2026-09-02 08:00:00', NULL),
(77, 'PRD_00877921510', 'masuk', 30, 31500.00, 'Penerimaan persediaan', 17, '2026-09-02 08:00:00', NULL),
(78, 'PRD_02053183769', 'masuk', 30, 31500.00, 'Penerimaan persediaan', 14, '2026-09-02 08:00:00', NULL),
(79, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-0423BF783EA9', NULL, '2026-10-12 09:15:00', 'GG-0423BF783EA9'),
(80, 'PRD_00877921510', 'keluar', 2, 31500.00, 'Penjualan GG-F66DE86C0F47', NULL, '2026-10-11 10:15:00', 'GG-F66DE86C0F47'),
(81, 'PRD_02053183769', 'keluar', 3, 31500.00, 'Penjualan GG-64AC5C9D9F7B', NULL, '2026-10-10 11:15:00', 'GG-64AC5C9D9F7B'),
(82, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-9542E5026F3B', NULL, '2026-10-09 12:15:00', 'GG-9542E5026F3B'),
(83, 'PRD_00877921510', 'keluar', 2, 31500.00, 'Penjualan GG-08F3C54E61D1', NULL, '2026-10-07 13:15:00', 'GG-08F3C54E61D1'),
(84, 'PRD_02053183769', 'keluar', 3, 31500.00, 'Penjualan GG-A985CD63D2A0', NULL, '2026-09-30 14:15:00', 'GG-A985CD63D2A0'),
(85, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-28318AA3CF53', NULL, '2026-09-09 15:15:00', 'GG-28318AA3CF53'),
(90, 'PRD_02053183769', 'keluar', 3, 31500.00, 'Penjualan GG-757FE875C94F', NULL, '2026-10-12 19:06:35', 'GG-757FE875C94F'),
(98, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-88BDD71C994D', NULL, '2026-10-12 19:41:55', 'GG-88BDD71C994D'),
(99, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-5000AB552B3B', NULL, '2026-10-12 19:44:43', 'GG-5000AB552B3B'),
(100, 'PRD_02053183769', 'keluar', 1, 31500.00, 'Penjualan GG-5000AB552B3B', NULL, '2026-10-12 19:44:43', 'GG-5000AB552B3B'),
(101, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-3B6DBC7919F6', NULL, '2026-10-12 19:45:22', 'GG-3B6DBC7919F6'),
(114, 'PRD_02053183769', 'keluar', 1, 31500.00, 'Penjualan GG-C673285D062A', NULL, '2026-10-12 20:14:37', 'GG-C673285D062A'),
(120, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-B2D74B37361B', NULL, '2026-10-12 20:28:28', 'GG-B2D74B37361B'),
(121, 'PRD_02053183769', 'keluar', 2, 31500.00, 'Penjualan GG-B2D74B37361B', NULL, '2026-10-12 20:28:28', 'GG-B2D74B37361B'),
(122, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-3A1F362BBB26', NULL, '2026-10-12 20:29:44', 'GG-3A1F362BBB26'),
(123, 'PRD_02053183769', 'keluar', 1, 31500.00, 'Penjualan GG-4C794F2A8E80', NULL, '2026-10-12 20:30:25', 'GG-4C794F2A8E80'),
(124, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-1F2166A235D4', NULL, '2026-10-12 20:30:47', 'GG-1F2166A235D4'),
(125, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-EAD64FEE545C', NULL, '2026-10-12 20:31:11', 'GG-EAD64FEE545C'),
(126, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-E7C53E868EAE', NULL, '2026-10-12 20:31:19', 'GG-E7C53E868EAE'),
(156, 'PRD_02053183769', 'keluar', 1, 31500.00, 'Penjualan GG-B4E733AED04D', NULL, '2026-10-12 21:54:29', 'GG-B4E733AED04D'),
(157, 'PRD_02053183769', 'keluar', 1, 31500.00, 'Penjualan GG-942561BB0503', NULL, '2026-10-12 21:55:54', 'GG-942561BB0503'),
(158, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-B1D502F9E68A', NULL, '2026-10-12 21:56:37', 'GG-B1D502F9E68A'),
(159, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-B1D502F9E68A', NULL, '2026-10-12 21:56:37', 'GG-B1D502F9E68A'),
(160, 'PRD_00016875477', 'masuk', 12, 48500.00, 'Penerimaan persediaan 12 Oktober 2026', 12, '2026-10-12 22:09:39', NULL),
(161, 'PRD_00877921510', 'masuk', 12, 33500.00, 'Penerimaan persediaan 12 Oktober 2026', 12, '2026-10-12 22:09:39', NULL),
(162, 'PRD_02053183769', 'masuk', 12, 32000.00, 'Penerimaan persediaan 12 Oktober 2026', 12, '2026-10-12 22:09:39', NULL),
(163, 'PRD_02638121216', 'masuk', 24, 5600.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(164, 'PRD_03566984797', 'masuk', 40, 7200.00, 'Penerimaan persediaan 12 Oktober 2026', 40, '2026-10-12 22:09:39', NULL),
(165, 'PRD_05304045813', 'masuk', 18, 22500.00, 'Penerimaan persediaan 12 Oktober 2026', 18, '2026-10-12 22:09:39', NULL),
(166, 'PRD_06662692105', 'masuk', 10, 58000.00, 'Penerimaan persediaan 12 Oktober 2026', 10, '2026-10-12 22:09:39', NULL),
(167, 'PRD_07627479596', 'masuk', 18, 26000.00, 'Penerimaan persediaan 12 Oktober 2026', 18, '2026-10-12 22:09:39', NULL),
(168, 'PRD_07639457476', 'masuk', 24, 5500.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(169, 'PRD_08361677758', 'masuk', 24, 9900.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(170, 'PRD_08959734393', 'masuk', 24, 5200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(171, 'PRD_09433004187', 'masuk', 24, 5200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(172, 'PRD_09992327462', 'masuk', 20, 16500.00, 'Penerimaan persediaan 12 Oktober 2026', 20, '2026-10-12 22:09:39', NULL),
(173, 'PRD_10996118655', 'masuk', 12, 31000.00, 'Penerimaan persediaan 12 Oktober 2026', 12, '2026-10-12 22:09:39', NULL),
(174, 'PRD_11650264519', 'masuk', 15, 29500.00, 'Penerimaan persediaan 12 Oktober 2026', 15, '2026-10-12 22:09:39', NULL),
(175, 'PRD_11779732260', 'masuk', 20, 4100.00, 'Penerimaan persediaan 12 Oktober 2026', 20, '2026-10-12 22:09:39', NULL),
(176, 'PRD_12267226394', 'masuk', 24, 5300.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(177, 'PRD_12761562947', 'masuk', 24, 7000.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(178, 'PRD_13359265350', 'masuk', 24, 4200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(179, 'PRD_13699127602', 'masuk', 24, 6000.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(180, 'PRD_13763803432', 'masuk', 20, 15000.00, 'Penerimaan persediaan 12 Oktober 2026', 20, '2026-10-12 22:09:39', NULL),
(181, 'PRD_14080726422', 'masuk', 48, 3800.00, 'Penerimaan persediaan 12 Oktober 2026', 48, '2026-10-12 22:09:39', NULL),
(182, 'PRD_14954711260', 'masuk', 24, 4300.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(183, 'PRD_15650276204', 'masuk', 40, 2200.00, 'Penerimaan persediaan 12 Oktober 2026', 40, '2026-10-12 22:09:39', NULL),
(184, 'PRD_16805975959', 'masuk', 40, 2500.00, 'Penerimaan persediaan 12 Oktober 2026', 40, '2026-10-12 22:09:39', NULL),
(185, 'PRD_17324769388', 'masuk', 20, 4100.00, 'Penerimaan persediaan 12 Oktober 2026', 20, '2026-10-12 22:09:39', NULL),
(186, 'PRD_18129502243', 'masuk', 30, 2300.00, 'Penerimaan persediaan 12 Oktober 2026', 30, '2026-10-12 22:09:39', NULL),
(187, 'PRD_18617013290', 'masuk', 30, 2300.00, 'Penerimaan persediaan 12 Oktober 2026', 30, '2026-10-12 22:09:39', NULL),
(188, 'PRD_18639457606', 'masuk', 144, 350.00, 'Penerimaan persediaan 12 Oktober 2026', 144, '2026-10-12 22:09:39', NULL),
(189, 'PRD_18974646805', 'masuk', 50, 1400.00, 'Penerimaan persediaan 12 Oktober 2026', 50, '2026-10-12 22:09:39', NULL),
(190, 'PRD_19493878708', 'masuk', 50, 1800.00, 'Penerimaan persediaan 12 Oktober 2026', 50, '2026-10-12 22:09:39', NULL),
(191, 'PRD_20037601647', 'masuk', 40, 1700.00, 'Penerimaan persediaan 12 Oktober 2026', 40, '2026-10-12 22:09:39', NULL),
(192, 'PRD_20669949571', 'masuk', 40, 1750.00, 'Penerimaan persediaan 12 Oktober 2026', 40, '2026-10-12 22:09:39', NULL),
(193, 'PRD_20715061932', 'masuk', 48, 2400.00, 'Penerimaan persediaan 12 Oktober 2026', 48, '2026-10-12 22:09:39', NULL),
(194, 'PRD_21948382265', 'masuk', 48, 3600.00, 'Penerimaan persediaan 12 Oktober 2026', 48, '2026-10-12 22:09:39', NULL),
(195, 'PRD_23259949343', 'masuk', 24, 3900.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(196, 'PRD_23765443715', 'masuk', 24, 4800.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(197, 'PRD_24254357879', 'masuk', 24, 8200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(198, 'PRD_25312761177', 'masuk', 24, 5500.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(199, 'PRD_25863427374', 'masuk', 24, 5600.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(200, 'PRD_27131014176', 'masuk', 24, 6200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(201, 'PRD_27464033182', 'masuk', 24, 7200.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(202, 'PRD_34331235266', 'masuk', 24, 14500.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(203, 'PRD_40477704712', 'masuk', 15, 43500.00, 'Penerimaan persediaan 12 Oktober 2026', 15, '2026-10-12 22:09:39', NULL),
(204, 'PRD_55071163152', 'masuk', 30, 7800.00, 'Penerimaan persediaan 12 Oktober 2026', 30, '2026-10-12 22:09:39', NULL),
(205, 'PRD_56095135699', 'masuk', 24, 12000.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(206, 'PRD_58480927407', 'masuk', 24, 12500.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(207, 'PRD_60274096744', 'masuk', 12, 47000.00, 'Penerimaan persediaan 12 Oktober 2026', 12, '2026-10-12 22:09:39', NULL),
(208, 'PRD_61762411140', 'masuk', 24, 20500.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:39', NULL),
(209, 'PRD_65671504693', 'masuk', 20, 24500.00, 'Penerimaan persediaan 12 Oktober 2026', 20, '2026-10-12 22:09:39', NULL),
(210, 'PRD_66878007736', 'masuk', 30, 8000.00, 'Penerimaan persediaan 12 Oktober 2026', 30, '2026-10-12 22:09:39', NULL),
(211, 'PRD_70956539291', 'masuk', 10, 38000.00, 'Penerimaan persediaan 12 Oktober 2026', 10, '2026-10-12 22:09:40', NULL),
(212, 'PRD_90212598279', 'masuk', 48, 3600.00, 'Penerimaan persediaan 12 Oktober 2026', 48, '2026-10-12 22:09:40', NULL),
(213, 'PRD_96761227467', 'masuk', 24, 11000.00, 'Penerimaan persediaan 12 Oktober 2026', 24, '2026-10-12 22:09:40', NULL),
(214, 'PRD_97964235986', 'masuk', 30, 9500.00, 'Penerimaan persediaan 12 Oktober 2026', 30, '2026-10-12 22:09:40', NULL),
(215, 'PRD_00016875477', 'keluar', 1, 45500.00, 'Penjualan GG-54E11921AA52', NULL, '2026-10-13 00:14:06', 'GG-54E11921AA52'),
(216, 'PRD_00877921510', 'keluar', 1, 31500.00, 'Penjualan GG-54E11921AA52', NULL, '2026-10-13 00:14:06', 'GG-54E11921AA52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_riwayat` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `peran` varchar(20) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` varchar(500) NOT NULL,
  `tautan` varchar(250) NOT NULL,
  `popup` tinyint(4) NOT NULL DEFAULT 1,
  `dibaca_pada` datetime DEFAULT NULL,
  `tanggal` datetime(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_kirim`
--

CREATE TABLE `notifikasi_kirim` (
  `id_notifikasi` bigint(20) UNSIGNED NOT NULL,
  `perangkat` char(36) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `nama` varchar(80) NOT NULL,
  `nilai` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`nama`, `nilai`) VALUES
('bank_nama', 'BRI'),
('bank_pemilik', 'STIVEN MELKIANUS ADU'),
('bank_rekening', '003901139474509');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin','kasir','pimpinan') NOT NULL,
  `tanggal_dibuat` datetime DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verify_token` varchar(255) DEFAULT NULL,
  `token_expired` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expired` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama`, `email`, `no_hp`, `alamat`, `password`, `role`, `tanggal_dibuat`, `is_verified`, `verify_token`, `token_expired`, `reset_token`, `reset_expired`) VALUES
(1, 'admin', 'admin@gmail.com', '081338609228', 'Jln Nasution II, Kayu Puith, Kupang NTT', '$2y$10$.XpEs.z7h5xt68V8cua9o.c6iu5fBbfZvd6giRTExPT6GWS7.5zlS', 'admin', '2025-10-21 20:01:46', 1, NULL, '2026-05-22 15:58:20', NULL, NULL),
(12, 'pimpinan', 'pimpinan@gmail.com', '081338609228', 'Jln A.H Nasution 3', '$2y$10$Qb6CXUHFwA2DcPTKb7bq9OeEjZUpLbTdlVxLjd.9.wfWRknXv4bzq', 'pimpinan', '2026-06-01 23:07:02', 1, NULL, '2026-06-02 23:07:02', NULL, NULL),
(22, 'pelanggan', 'pelanggan@gmail.com', '085829230787', 'Jl Perintis Kemerdekaan 1', '$2y$10$SoOgXtN1GUDpg5szQJmT.OM97MleZeG8ZgsKIx78vHU33or.bxiqu', 'pelanggan', '2026-08-21 23:39:59', 1, NULL, NULL, NULL, NULL),
(23, 'kasir', 'kasir@gmail.com', '085000000000', NULL, '$2y$10$qa.LLO4k79KSjBR8nCmaU.sG/qTETrxYsXZSzbYpNviDdcoOutGCy', 'kasir', '2026-08-31 22:43:52', 1, '6dfccd52c5e1be32a179e654d6da7d55006e6cdaf0c12349f8d6c5dddc050932', '2026-09-01 22:43:52', NULL, NULL),
(24, 'Stiven Melkianus Adu', 'stivenadu01@gmail.com', '+6281000000000', NULL, '$2y$10$NTerGumVRfrUB3/XLyzpie2xVSVxYMvDCob.S.wpG2rg2IwhOIKD2', 'pelanggan', '2026-10-13 00:57:35', 1, NULL, '2026-10-14 00:57:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `kode_produk` varchar(15) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `satuan_dasar` varchar(10) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `asal_produk` varchar(100) DEFAULT NULL,
  `tagline` varchar(100) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `terjual` int(11) DEFAULT 0,
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`kode_produk`, `id_kategori`, `nama_produk`, `harga_jual`, `deskripsi`, `satuan_dasar`, `gambar`, `asal_produk`, `tagline`, `stok`, `terjual`, `tanggal_dibuat`) VALUES
('PRD_00016875477', 80, 'CNC COOKIES NASTAR', 65000.00, '', 'Toples', '/produk/PRD_00016875477.png', 'CNC COOKIES, PENFUI KUPANG NTT', 'Nastar CNC renyah, rasa kelapa asli Kupang.', 36, 6, '2025-12-19 12:53:23'),
('PRD_00877921510', 80, 'CNC COOKIES SAGU KEJU', 45000.00, '', 'Toples', '/produk/PRD_00877921510.png', 'CNC COOKIES, PENFUI KUPANG NTT', 'Rasa gurih keju dengan sentuhan sagu alami, langsung dari Kupang.', 29, 13, '2025-12-19 12:54:49'),
('PRD_02053183769', 80, 'Kue Coklat Spekuk CNC COOKIES', 45000.00, '', 'Toples', '/produk/PRD_02053183769.png', 'CNC COOKIES, PENFUI KUPANG NTT', 'Spekuk coklat renyah, rasa asli Kupang, cocok ngemil santai.', 26, 16, '2025-12-19 12:56:47'),
('PRD_02638121216', 78, 'Coca Cola 330 ML', 7000.00, '', 'Kaleng', '/produk/PRD_02638121216.webp', '', NULL, 24, 0, '2025-12-28 14:17:18'),
('PRD_03566984797', 80, 'Jagung Pulut Pedas', 10000.00, 'Nikmati kelezatan camilan autentik khas Nusa Tenggara Timur dengan Jagung Pulut Pedas. Terbuat dari jagung pulut (jagung ketan) pilihan yang terkenal dengan teksturnya yang unik—lebih empuk, padat, dan sedikit kenyal saat dikunyah, sangat berbeda dari jagung biasa.', 'Pcs', '/produk/PRD_03566984797.png', 'Klasis Kota Kupang Barat', 'Jagung pulut pedas, empuk kenyal, cita rasa khas NTT.', 40, 0, '2025-12-19 12:59:18'),
('PRD_05304045813', 79, 'Minyak Kemiri', 30000.00, '', 'Botol', '/produk/PRD_05304045813.png', 'Pemuda Amanuban Tengah Selatan', 'Minyak kemiri Pemuda Amanuban, wangi alami, cocok untuk masak sehari-hari.', 18, 0, '2025-12-19 13:02:12'),
('PRD_06662692105', 78, 'Minuman Perjamuan Perjanjian Baru', 75000.00, '', 'Botol', '/produk/PRD_06662692105.png', 'SINODE GMIT', 'Minuman Perjamuan Perjanjian baru, dari SINODE GMIT.', 10, 0, '2025-12-19 13:04:28'),
('PRD_07627479596', 79, 'Minyak Kayu Putih Tuamese', 35000.00, '', 'Botol', '/produk/PRD_07627479596.png', 'GMIT EBENHAEZER TUAMESE', 'Minyak kayu putih Tuamese, wangi alami, segar dari Kupang.', 18, 0, '2025-12-19 13:06:04'),
('PRD_07639457476', 78, 'Pulpy orange 300 ML', 7000.00, '', 'Botol', '/produk/PRD_07639457476.webp', '', NULL, 24, 0, '2025-12-28 16:32:43'),
('PRD_08361677758', 78, 'Bear Brand 189 ML', 11500.00, '', 'Pcs', '/produk/PRD_08361677758.png', '', NULL, 24, 0, '2025-12-28 16:33:56'),
('PRD_08959734393', 78, 'Fruit Tea Blaccurent 350 ML', 7000.00, '', 'Pcs', '/produk/PRD_08959734393.jpg', '', NULL, 24, 0, '2025-12-28 16:34:55'),
('PRD_09433004187', 78, 'Fruit Tea Strobery 350 ML', 7000.00, '', 'Botol', '/produk/PRD_09433004187.jpg', '', NULL, 24, 0, '2025-12-28 16:35:43'),
('PRD_09992327462', 79, 'Gula Sabu Putih 350ML', 21000.00, 'Kegunaan:\r\n-Mengatasi sakit lambung\r\n-Mengatasi anemia\r\n-Mengatasi nafsu makan yang kurang normal\r\n-Dapat meresahkan lambung, kronis dll', 'Botol', '/produk/PRD_09992327462.png', 'Air Nona, Kota Raja Kota Kupang', 'Gula Sabu alami, bantu atasi lambung dan tambah selera makan.', 19, 0, '2025-12-19 13:10:01'),
('PRD_10996118655', 79, 'Madu Batu', 40000.00, '', 'Botol', '/produk/PRD_10996118655.png', 'Klasis Semau', 'Madu Batu Klasis Semau, manis alami khas Kupang.', 12, 0, '2025-12-19 13:11:41'),
('PRD_11650264519', 79, 'Madu Pohon', 40000.00, '', 'Botol', '/produk/PRD_11650264519.png', 'Amfoang Selatan', 'Rasa manis alami khas Amfoang', 14, 0, '2025-12-19 13:12:46'),
('PRD_11779732260', 78, 'Dancow Coklat', 5000.00, '', 'Sachet', '/produk/PRD_11779732260.jpg', '', NULL, 20, 0, '2025-12-28 16:49:39'),
('PRD_12267226394', 78, 'Teh Botol Sosro 350 ML', 7000.00, '', 'Botol', '/produk/PRD_12267226394.webp', '', NULL, 24, 0, '2025-12-28 16:40:26'),
('PRD_12761562947', 78, 'Buavita 245 ML', 8500.00, '', 'Pcs', '/produk/PRD_12761562947.jpg', '', NULL, 24, 0, '2025-12-28 16:41:16'),
('PRD_13359265350', 78, 'Nipis Madu 330 ML', 5500.00, '', 'Botol', '/produk/PRD_13359265350.png', '', NULL, 24, 0, '2025-12-28 16:42:15'),
('PRD_13699127602', 78, 'Good Day 250 ML', 7500.00, '', 'Botol', '/produk/PRD_13699127602.jpg', '', NULL, 24, 0, '2025-12-28 16:42:49'),
('PRD_13763803432', 77, 'Kunyit Asam Instan', 20000.00, '', 'Pcs', '/produk/PRD_13763803432.png', 'Jemaat Fatubena', 'Kunyit Asam Instan Jemaat Fatubena, rasa tradisional siap saji.', 20, 0, '2025-12-19 11:09:25'),
('PRD_14080726422', 78, 'Floridina Jeruk 350 ML', 5000.00, '', 'Botol', '/produk/PRD_14080726422.jpg', '', NULL, 48, 0, '2025-12-28 16:43:28'),
('PRD_14954711260', 78, 'Golda Coffee 200 ML', 5500.00, '', 'Botol', '/produk/PRD_14954711260.jpg', '', NULL, 24, 0, '2025-12-28 16:44:55'),
('PRD_15650276204', 78, 'Chocolatos Drink', 3000.00, '', 'Shachet', '/produk/PRD_15650276204.jpg', '', NULL, 40, 0, '2025-12-28 16:46:05'),
('PRD_16805975959', 78, 'Good Day', 3500.00, '', 'Sachet', '/produk/PRD_16805975959.png', '', NULL, 40, 0, '2025-12-28 16:48:00'),
('PRD_17324769388', 78, 'Dancow Putih', 5000.00, '', 'Sachet', '/produk/PRD_17324769388.jpg', '', NULL, 20, 0, '2025-12-28 16:48:52'),
('PRD_18129502243', 78, 'Energen Coklat', 3000.00, '', 'Sachet', '/produk/PRD_18129502243.jpg', '', NULL, 30, 0, '2025-12-28 16:50:12'),
('PRD_18617013290', 78, 'Energen Vanila', 3000.00, '', 'Sachet', '/produk/PRD_18617013290.jpg', '', NULL, 30, 0, '2025-12-28 16:51:01'),
('PRD_18639457606', 78, 'Aguamor', 500.00, '', 'Gelas', '/produk/PRD_18639457606.png', '', '', 144, 0, '2025-12-28 14:04:23'),
('PRD_18974646805', 78, 'Top Coffee Susu', 2000.00, '', 'Sachet', '/produk/PRD_18974646805.jpg', '', NULL, 50, 0, '2025-12-28 16:51:37'),
('PRD_19493878708', 78, 'Kapal Api', 2500.00, '', 'Sachet', '/produk/PRD_19493878708.webp', '', NULL, 50, 0, '2025-12-28 16:52:30'),
('PRD_20037601647', 78, 'Top Coffee Gula Aren', 2500.00, 'Top Coffee Gula Aren adalah varian kopi instan 3-in-1 dari Wings Food yang memadukan kekuatan rasa kopi tubruk asli, kelembutan susu, dan legitnya gula aren alami. Produk inovatif ini dirancang bagi generasi modern yang menyukai tren kopi susu ala kafe, namun menginginkan kepraktisan dan harga yang ekonomis dalam format sachet.Berikut adalah rincian deskripsi produk lengkap untuk kebutuhan penjualan, katalog, atau promosi:🌟 Keunggulan ProdukKombinasi Karakter Kuat: Menggunakan racikan biji kopi Robusta dan Arabika pilihan untuk menghasilkan aroma harum yang kompleks dan rasa kopi yang mantap.Sentuhan Gula Aren Asli: Memberikan sensasi rasa manis yang legit, lembut di tenggorokan, khas, dan tidak membuat enek.Ampas Kopi yang Halus: Memiliki tekstur serbuk kopi tubruk mikro yang halus, sehingga sensasi strong kopinya langsung terasa tanpa perlu menunggu ampas turun lama.Fleksibel Disajikan: Sangat nikmat diseduh hangat sebagai teman beraktivitas maupun disajikan dingin dengan es batu untuk kesegaran maksimal.📝 Spesifikasi & KomposisiJenis Produk: Kopi Instan Susu + Gula Aren.Komposisi: Gula, krimer nabati, kopi bubuk instan (9,6%), susu skim bubuk, perisa sintetik, garam, serta pemanis buatan (Asesulfam-K, Sukralosa).Berat per Sachet: 22 gram.Informasi Nilai Gizi: Mengandung sekitar 100 kalori, 2,5 gram lemak, dan 18 gram karbohidrat per sachet.Kemasan di Pasaran: Tersedia dalam bentuk pouch (isi 6+3 sachet) maupun kemasan renteng (isi 15 sachet).☕ Cara Penyajian ResmiSajian Panas: Tuangkan 1 sachet ke dalam cangkir, seduh dengan 150 ml air panas, lalu aduk rata.Sajian Dingin (Es Kopi): Tuangkan 1 sachet ke dalam gelas, seduh dengan 60 ml air panas hingga larut, kemudian tambahkan sekitar 90 gram es batu.', 'Sachet', '/produk/PRD_20037601647.jpg', '', NULL, 40, 0, '2025-12-28 16:53:23'),
('PRD_20669949571', 78, 'Top White Coffee', 2500.00, 'Top White Coffee adalah varian kopi instan siap seduh dari Wings Corp yang memadukan kelembutan krimer nabati dengan karakter rasa kopi yang kuat dan harum. Produk ini dirancang khusus untuk para pencinta kopi yang menginginkan sensasi white coffee premium dengan harga ekonomis dan kemasan yang praktis.Berikut adalah rincian deskripsi produk untuk kebutuhan penjualan, katalog, atau promosi Anda:🌟 Keunggulan ProdukPerpaduan Biji Kopi Premium: Mengombinasikan biji kopi Robusta dan Arabika pilihan untuk menciptakan rasa yang seimbang, aroma harum, dan karakter yang kompleks.Tekstur Lembut & Creamy: Menggunakan krimer berkualitas tinggi yang menghasilkan konsistensi lembut di mulut tanpa menutupi rasa asli kopinya.Ramah di Lambung: Ciri khas proses white coffee yang menggunakan suhu pemanggangan lebih rendah membuat tingkat keasamannya cenderung lebih ramah bagi lambung.Praktis & Ekonomis: Dikemas dalam bentuk sachet siap seduh yang sudah mencakup kopi, gula, dan krimer.\r\n\r\n\r\nznfsif', 'Sachet', '/produk/PRD_20669949571.png', '', NULL, 40, 0, '2025-12-28 16:54:27'),
('PRD_20715061932', 78, 'Aqua 330 ML', 3000.00, '', 'Botol', '/produk/PRD_20715061932.jpg', '', NULL, 48, 0, '2025-12-28 14:07:51'),
('PRD_21948382265', 78, 'Teh Pucuk 350 ML', 5000.00, '', 'Botol', '/produk/PRD_21948382265.jpg', '', NULL, 48, 0, '2025-12-28 14:09:54'),
('PRD_23259949343', 78, 'Teh Kotak 300 ML', 5000.00, '', 'Pcs', '/produk/PRD_23259949343.png', '', NULL, 24, 0, '2025-12-28 14:12:05'),
('PRD_23765443715', 78, 'Sari Kacang Hijau 250 ML', 6000.00, '', 'Pcs', '/produk/PRD_23765443715.jpg', '', NULL, 24, 0, '2025-12-28 14:12:56'),
('PRD_24254357879', 78, 'You C 1000 ORANGE', 10000.00, '', 'Botol', '/produk/PRD_24254357879.webp', '', NULL, 24, 0, '2025-12-28 14:13:45'),
('PRD_25312761177', 78, 'Fanta 330 ML', 7000.00, '', 'Kaleng', '/produk/PRD_25312761177.jpg', '', NULL, 24, 0, '2025-12-28 14:15:31'),
('PRD_25863427374', 78, 'Sprite 330 ML', 7000.00, '', 'Kaleng', '/produk/PRD_25863427374.jpg', '', NULL, 24, 0, '2025-12-28 14:16:26'),
('PRD_27131014176', 78, 'Pocari Sweat 350 ML', 7500.00, '', 'Botol', '/produk/PRD_27131014176.webp', '', NULL, 24, 0, '2025-12-28 14:18:33'),
('PRD_27464033182', 78, 'Pocari Sweat 500 ML', 8500.00, '', 'Botol', '/produk/PRD_27464033182.webp', '', NULL, 24, 0, '2025-12-28 14:19:06'),
('PRD_34331235266', 77, 'Kunyit Instan', 20000.00, 'Kunyit Instan dari Jemaat Fatubena adalah bubuk kunyit siap seduh alami yang diolah secara higienis tanpa bahan pengawet kimia. Produk ini merupakan hasil produksi lokal yang tidak hanya menawarkan kepraktisan untuk kesehatan dan bumbu dapur, tetapi juga mendukung perekonomian komunitas usaha mikro di daerah Fatubena.Berikut adalah draf deskripsi produk yang dioptimalkan untuk halaman penjualan toko online Anda:🌟 Keunggulan ProdukAlami & Higienis: Terbuat dari rimpang kunyit pilihan yang diproses bersih tanpa campuran zat kimia berbahaya.Multifungsi: Dapat diseduh langsung sebagai minuman herbal (jamu) hangat penambah daya tahan tubuh, maupun digunakan sebagai bumbu praktis untuk masakan dapur.Mendukung Produk Lokal: Setiap pembelian produk ini berkontribusi langsung pada pemberdayaan ekonomi Jemaat Fatubena.📝 Spesifikasi ProdukNama Produk: Kunyit Instan Jemaat FatubenaKode Produk: PRD_34331235266Kategori: Minyak & Bumbu DapurHarga: Rp 20.000Status Stok: 1 unit tersedia (Sangat Terbatas)Asal / Produsen: Jemaat Fatubena☕ Cara PenggunaanUntuk Minuman Kesehatan: Ambil 1-2 sendok teh bubuk kunyit instan, seduh dengan segelas air hangat. Bisa ditambahkan madu atau lemon sesuai selera.Untuk Bumbu Masak: Masukkan secukupnya ke dalam masakan seperti soto, gulai, ayam ungkep, atau hidangan lainnya sesuai takaran resep.', 'PCS', '/produk/PRD_34331235266.png', 'Jemaat Fatubena', 'Kunyit Instan siap seduh dan alami. tanpa bahan kimia.', 24, 0, '2025-12-19 11:03:55'),
('PRD_40477704712', 78, 'Kopi Meto', 55000.00, '', 'Pcs', '/produk/PRD_40477704712.png', 'Kelompok Kofi Metode Bitobe(Sonan, Bioba)', 'Aroma khas Bitobe, rasa kuat alami tiap cangkir.', 15, 0, '2025-12-19 11:14:09'),
('PRD_55071163152', 77, 'Gula Semut', 10000.00, '', 'Pcs', '/produk/PRD_55071163152.png', 'Imanuel Rote Barat Laut', 'Manis alami gula semut Imanuel, rasa otentik Rote.', 30, 0, '2025-12-19 14:25:09'),
('PRD_56095135699', 77, 'Garam Yodium Oebelo Tanah Merah', 15000.00, '', 'Bakun', '/produk/PRD_56095135699.png', 'Klasis Kupang Tengah', 'Garam Yodium Oebelo, rasa alami dari Tanah Merah Kupang.', 24, 0, '2025-12-19 14:26:51'),
('PRD_58480927407', 76, 'Tepung Mokaf Original', 15000.00, '', 'Pcs', '/produk/PRD_58480927407.png', 'Jemaat Sopo, Klasis Amanuban Tengah Uatar', 'Tepung Mokaf asli Sopo, tekstur halus, rasa otentik.', 24, 0, '2025-12-19 14:30:50'),
('PRD_60274096744', 78, 'Kopi Amfoang', 60000.00, '', 'Pcs', '/produk/PRD_60274096744.png', 'Istana Hijau Lelogama', 'Kopi Amfoang dari Istana Hijau, rasa alami yang memikat.', 11, 0, '2025-12-19 14:33:49'),
('PRD_61762411140', 77, 'Kecap Manis Sedaap 550 ML', 25000.00, 'Kecap manis kedelai hitam Special dalam kemasan isi ulang 550 ml. Dijual per pouch.', 'Pouch', '/produk/PRD_61762411140.png', '', NULL, 24, 0, '2025-12-19 14:36:18'),
('PRD_65671504693', 79, 'Gula Lempeng Tuamese 1KG', 30000.00, '', 'Pcs', '/produk/PRD_65671504693.png', '-', 'Gula lempeng Tuamese, manis pas, serbaguna untuk masak.', 20, 0, '2025-12-19 14:42:49'),
('PRD_66878007736', 79, 'Gula Lempeng Rote', 10000.00, '', 'Pcs', '/produk/PRD_66878007736.png', 'Imanuel Ndau / RBL', 'Manis alami Gula Lempeng Rote, rasa khas pulau sejuk.', 29, 0, '2025-12-19 14:44:49'),
('PRD_70956539291', 79, 'Nu Koennu Minyak Herbal', 50000.00, '...', 'Botol', '/produk/PRD_70956539291.png', 'Napago Farm Kupang, NTT', 'Minyak herbal Napago Farm, alami, menenangkan tubuh secara lembut.', 10, 0, '2025-12-19 14:51:37'),
('PRD_90212598279', 78, 'Aqua 600 ML', 5000.00, '', 'Botol', '/produk/PRD_90212598279.webp', '', NULL, 48, 0, '2025-12-28 14:08:45'),
('PRD_96761227467', 80, 'Cokelat KELOR-S', 15000.00, '', 'Pcs', '/produk/PRD_96761227467.png', 'Niki Niki Amanuban Tengah, TTS', 'Rasa alami khas Niki niki Amanuban Tengah.', 24, 0, '2025-12-19 12:47:58'),
('PRD_97964235986', 80, 'Kacang Telur', 12500.00, '', 'Pcs', '/produk/PRD_97964235986.png', 'HomeMade Moms, Penfui Kupang NTT', 'Kacang telur Homemade Moms, gurih alami rasa tradisional Kupang.', 30, 0, '2025-12-19 12:49:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservasi_stok`
--

CREATE TABLE `reservasi_stok` (
  `kode_transaksi` varchar(15) NOT NULL,
  `kode_produk` varchar(15) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reservasi_stok`
--

INSERT INTO `reservasi_stok` (`kode_transaksi`, `kode_produk`, `jumlah`) VALUES
('GG-5805602CC18C', 'PRD_60274096744', 1),
('GG-968560FAED70', 'PRD_09992327462', 1),
('GG-968560FAED70', 'PRD_11650264519', 1),
('GG-968560FAED70', 'PRD_66878007736', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pesanan`
--

CREATE TABLE `riwayat_pesanan` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(15) NOT NULL,
  `kategori` enum('pesanan','pembayaran') NOT NULL DEFAULT 'pesanan',
  `peristiwa` varchar(50) NOT NULL,
  `status_bayar` varchar(30) DEFAULT NULL,
  `catatan` varchar(500) DEFAULT NULL,
  `petugas` int(11) DEFAULT NULL,
  `kunci_peristiwa` varchar(100) DEFAULT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_pesanan`
--

INSERT INTO `riwayat_pesanan` (`id`, `kode_transaksi`, `kategori`, `peristiwa`, `status_bayar`, `catatan`, `petugas`, `kunci_peristiwa`, `tanggal`) VALUES
(1, 'GG-0423BF783EA9', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:2', '2026-10-12 09:15:00'),
(2, 'GG-F66DE86C0F47', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:3', '2026-10-11 10:15:00'),
(3, 'GG-64AC5C9D9F7B', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:4', '2026-10-10 11:15:00'),
(4, 'GG-9542E5026F3B', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:5', '2026-10-09 12:15:00'),
(5, 'GG-08F3C54E61D1', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:6', '2026-10-07 13:15:00'),
(6, 'GG-A985CD63D2A0', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:7', '2026-09-30 14:15:00'),
(7, 'GG-28318AA3CF53', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:8', '2026-09-09 15:15:00'),
(8, 'GG-7CDDC6D37F3E', 'pembayaran', 'pembayaran_belum_bayar', 'belum_bayar', 'Menunggu pembayaran.', NULL, 'pembayaran:9', '2026-10-12 09:15:00'),
(9, 'GG-757FE875C94F', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran diterima.', NULL, 'pembayaran:10', '2026-10-12 10:15:00'),
(10, 'GG-73D4D10DCAA1', 'pembayaran', 'pembayaran_belum_bayar', 'belum_bayar', 'Menunggu pembayaran.', NULL, 'pembayaran:11', '2026-10-10 11:15:00'),
(11, 'GG-73D4D10DCAA1', 'pesanan', 'dibatalkan', 'belum_bayar', 'Pesanan dibatalkan.', NULL, 'pembayaran:12', '2026-10-12 18:49:34'),
(12, 'GG-9D68EBB6D777', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:27', '2026-10-12 18:58:14'),
(13, 'GG-7CDDC6D37F3E', 'pesanan', 'dibatalkan', 'belum_bayar', 'Pesanan dibatalkan.', 22, 'pembayaran:31', '2026-10-12 19:09:16'),
(14, 'GG-BC4714506D05', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:41', '2026-10-12 19:38:30'),
(15, 'GG-88BDD71C994D', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:42', '2026-10-12 19:40:09'),
(16, 'GG-88BDD71C994D', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:43', '2026-10-12 19:41:42'),
(17, 'GG-BC4714506D05', 'pembayaran', 'pembayaran_kedaluwarsa', 'kedaluwarsa', 'Pembayaran QRIS berakhir: cancel', NULL, 'pembayaran:44', '2026-10-12 19:44:30'),
(18, 'GG-9D68EBB6D777', 'pembayaran', 'pembayaran_kedaluwarsa', 'kedaluwarsa', 'Pembayaran QRIS berakhir: cancel', NULL, 'pembayaran:45', '2026-10-12 19:44:38'),
(19, 'GG-5000AB552B3B', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:46', '2026-10-12 19:44:43'),
(20, 'GG-5000AB552B3B', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran tunai diterima.', 1, 'pembayaran:47', '2026-10-12 19:44:43'),
(21, 'GG-3B6DBC7919F6', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:48', '2026-10-12 19:45:00'),
(22, 'GG-3B6DBC7919F6', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:49', '2026-10-12 19:45:20'),
(23, 'GG-C673285D062A', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:63', '2026-10-12 20:10:43'),
(24, 'GG-C673285D062A', 'pembayaran', 'pembayaran_menunggu_verifikasi', 'menunggu_verifikasi', 'Bukti transfer diunggah pelanggan.', NULL, 'pembayaran:64', '2026-10-12 20:11:17'),
(25, 'GG-C673285D062A', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Transfer telah diterima dan diverifikasi admin.', 1, 'pembayaran:65', '2026-10-12 20:12:25'),
(26, 'GG-B2D74B37361B', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:68', '2026-10-12 20:25:08'),
(27, 'GG-B2D74B37361B', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:69', '2026-10-12 20:26:10'),
(28, 'GG-3A1F362BBB26', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:70', '2026-10-12 20:29:44'),
(29, 'GG-3A1F362BBB26', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran tunai diterima.', 1, 'pembayaran:71', '2026-10-12 20:29:44'),
(30, 'GG-4C794F2A8E80', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:72', '2026-10-12 20:30:04'),
(31, 'GG-4C794F2A8E80', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:73', '2026-10-12 20:30:25'),
(32, 'GG-1F2166A235D4', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:74', '2026-10-12 20:30:47'),
(33, 'GG-1F2166A235D4', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran tunai diterima.', 1, 'pembayaran:75', '2026-10-12 20:30:47'),
(34, 'GG-EAD64FEE545C', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:76', '2026-10-12 20:30:52'),
(35, 'GG-EAD64FEE545C', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:77', '2026-10-12 20:31:11'),
(36, 'GG-E7C53E868EAE', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:78', '2026-10-12 20:31:19'),
(37, 'GG-E7C53E868EAE', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran tunai diterima.', 1, 'pembayaran:79', '2026-10-12 20:31:19'),
(38, 'GG-B4E733AED04D', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:80', '2026-10-12 20:43:32'),
(39, 'GG-B4E733AED04D', 'pembayaran', 'pembayaran_menunggu_verifikasi', 'menunggu_verifikasi', 'Bukti transfer diunggah pelanggan.', NULL, 'pembayaran:81', '2026-10-12 20:51:10'),
(40, 'GG-B4E733AED04D', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Transfer telah diterima dan diverifikasi admin.', 1, 'pembayaran:82', '2026-10-12 20:51:59'),
(64, 'GG-28318AA3CF53', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-28318AA3CF53', '2026-09-09 15:15:00'),
(65, 'GG-A985CD63D2A0', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-A985CD63D2A0', '2026-09-30 14:15:00'),
(66, 'GG-08F3C54E61D1', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-08F3C54E61D1', '2026-10-07 13:15:00'),
(67, 'GG-9542E5026F3B', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-9542E5026F3B', '2026-10-09 12:15:00'),
(68, 'GG-64AC5C9D9F7B', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-64AC5C9D9F7B', '2026-10-10 11:15:00'),
(69, 'GG-73D4D10DCAA1', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-73D4D10DCAA1', '2026-10-10 11:15:00'),
(70, 'GG-F66DE86C0F47', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-F66DE86C0F47', '2026-10-11 10:15:00'),
(71, 'GG-0423BF783EA9', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-0423BF783EA9', '2026-10-12 09:15:00'),
(72, 'GG-7CDDC6D37F3E', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-7CDDC6D37F3E', '2026-10-12 09:15:00'),
(73, 'GG-757FE875C94F', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'checkout:GG-757FE875C94F', '2026-10-12 10:15:00'),
(79, 'GG-B2D74B37361B', 'pesanan', 'dikirim', NULL, 'Barang diserahkan kepada pengantar.', NULL, 'dikirim:GG-B2D74B37361B', '2026-10-12 20:27:11'),
(80, 'GG-0423BF783EA9', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-0423BF783EA9', '2026-10-12 09:15:00'),
(81, 'GG-08F3C54E61D1', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-08F3C54E61D1', '2026-10-07 13:15:00'),
(82, 'GG-1F2166A235D4', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-1F2166A235D4', '2026-10-12 20:30:47'),
(83, 'GG-28318AA3CF53', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-28318AA3CF53', '2026-09-09 15:15:00'),
(84, 'GG-3A1F362BBB26', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-3A1F362BBB26', '2026-10-12 20:29:44'),
(85, 'GG-3B6DBC7919F6', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-3B6DBC7919F6', '2026-10-12 19:45:22'),
(86, 'GG-4C794F2A8E80', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-4C794F2A8E80', '2026-10-12 20:30:25'),
(87, 'GG-5000AB552B3B', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-5000AB552B3B', '2026-10-12 19:44:43'),
(88, 'GG-64AC5C9D9F7B', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-64AC5C9D9F7B', '2026-10-10 11:15:00'),
(89, 'GG-757FE875C94F', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-757FE875C94F', '2026-10-12 19:06:35'),
(90, 'GG-88BDD71C994D', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-88BDD71C994D', '2026-10-12 19:41:55'),
(91, 'GG-9542E5026F3B', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-9542E5026F3B', '2026-10-09 12:15:00'),
(92, 'GG-A985CD63D2A0', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-A985CD63D2A0', '2026-09-30 14:15:00'),
(93, 'GG-B2D74B37361B', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-B2D74B37361B', '2026-10-12 20:28:28'),
(94, 'GG-C673285D062A', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-C673285D062A', '2026-10-12 20:14:37'),
(95, 'GG-E7C53E868EAE', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-E7C53E868EAE', '2026-10-12 20:31:19'),
(96, 'GG-EAD64FEE545C', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-EAD64FEE545C', '2026-10-12 20:31:11'),
(97, 'GG-F66DE86C0F47', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', NULL, 'selesai:GG-F66DE86C0F47', '2026-10-11 10:15:00'),
(204, 'GG-B4E733AED04D', 'pesanan', 'dikirim', NULL, 'Barang diserahkan kepada pengantar.', 1, NULL, '2026-10-12 21:54:18'),
(205, 'GG-B4E733AED04D', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', 1, NULL, '2026-10-12 21:54:29'),
(206, 'GG-942561BB0503', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:130', '2026-10-12 21:55:28'),
(207, 'GG-942561BB0503', 'pembayaran', 'pembayaran_menunggu_verifikasi', 'menunggu_verifikasi', 'Bukti transfer diunggah. Menunggu pemeriksaan admin.', 1, 'pembayaran:131', '2026-10-12 21:55:50'),
(208, 'GG-942561BB0503', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Transfer telah diterima dan diverifikasi admin.', 1, 'pembayaran:132', '2026-10-12 21:55:54'),
(209, 'GG-942561BB0503', 'pesanan', 'diproses', NULL, 'Pembayaran diterima. Pesanan mulai disiapkan.', 1, NULL, '2026-10-12 21:55:54'),
(210, 'GG-942561BB0503', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', 1, NULL, '2026-10-12 21:55:54'),
(211, 'GG-B1D502F9E68A', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:133', '2026-10-12 21:56:09'),
(212, 'GG-B1D502F9E68A', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:134', '2026-10-12 21:56:37'),
(213, 'GG-B1D502F9E68A', 'pesanan', 'diproses', NULL, 'Pembayaran diterima. Pesanan mulai disiapkan.', NULL, NULL, '2026-10-12 21:56:37'),
(214, 'GG-B1D502F9E68A', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', 1, NULL, '2026-10-12 21:56:37'),
(215, 'GG-54E11921AA52', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', NULL, 'pembayaran:135', '2026-10-12 23:47:59'),
(216, 'GG-54E11921AA52', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Pembayaran QRIS diterima dan diverifikasi admin.', NULL, 'pembayaran:136', '2026-10-12 23:48:17'),
(217, 'GG-54E11921AA52', 'pesanan', 'diproses', NULL, 'Pembayaran diterima. Pesanan mulai disiapkan.', NULL, NULL, '2026-10-12 23:48:17'),
(218, 'GG-54E11921AA52', 'pesanan', 'dikirim', NULL, 'Barang diserahkan kepada pengantar.', 1, NULL, '2026-10-13 00:13:55'),
(219, 'GG-54E11921AA52', 'pesanan', 'selesai', NULL, 'Barang diterima. Pesanan selesai.', 1, NULL, '2026-10-13 00:14:06'),
(362, 'GG-5805602CC18C', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', 22, 'checkout:GG-5805602CC18C', '2026-10-13 00:49:13'),
(363, 'GG-5805602CC18C', 'pembayaran', 'pembayaran_menunggu_verifikasi', 'menunggu_verifikasi', 'Bukti transfer diunggah. Menunggu pemeriksaan admin.', 22, 'bukti:1575acab2a43a3427398bd37d5e0ea47ff37831a.png', '2026-10-13 00:49:30'),
(364, 'GG-5805602CC18C', 'pembayaran', 'pembayaran_lunas', 'lunas', 'Transfer telah diterima dan diverifikasi admin.', 1, 'verifikasi:lunas:1575acab2a43a3427398bd37d5e0ea47ff37831a.png', '2026-10-13 00:50:17'),
(365, 'GG-5805602CC18C', 'pesanan', 'diproses', NULL, 'Pembayaran diterima. Pesanan mulai disiapkan.', 1, 'diproses:GG-5805602CC18C', '2026-10-13 00:50:17'),
(366, 'GG-5805602CC18C', 'pesanan', 'siap_diambil', NULL, 'Pesanan siap diambil di GGmart.', 1, 'siap_diambil:GG-5805602CC18C', '2026-10-13 00:51:24'),
(367, 'GG-968560FAED70', 'pesanan', 'checkout', 'belum_bayar', 'Pesanan dibuat.', 24, 'checkout:GG-968560FAED70', '2026-10-13 00:59:47'),
(368, 'GG-968560FAED70', 'pembayaran', 'pembayaran_menunggu_verifikasi', 'menunggu_verifikasi', 'Bukti transfer diunggah. Menunggu pemeriksaan admin.', 24, 'bukti:860ca6f8810a065abc890678f136e807e2c727ce.jpg', '2026-10-13 01:00:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `kode_transaksi` varchar(15) NOT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `total_harga` decimal(12,2) NOT NULL,
  `total_pokok` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','diproses','dikirim','dibatalkan','selesai') DEFAULT 'pending',
  `metode_bayar` enum('tunai','transfer','qris') DEFAULT 'tunai',
  `status_bayar` varchar(30) NOT NULL DEFAULT 'belum_bayar',
  `metode_pengiriman` varchar(10) NOT NULL DEFAULT 'ambil',
  `kelurahan` varchar(100) DEFAULT NULL,
  `nama_penerima` varchar(150) DEFAULT NULL,
  `no_hp_penerima` varchar(25) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tagihan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `batas_bayar` datetime DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `catatan_bayar` varchar(500) DEFAULT NULL,
  `midtrans_order_id` varchar(80) DEFAULT NULL,
  `midtrans_id` varchar(100) DEFAULT NULL,
  `midtrans_env` varchar(15) DEFAULT NULL,
  `qris_url` varchar(500) DEFAULT NULL,
  `checkout_key` varchar(64) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `sumber_transaksi` varchar(10) NOT NULL DEFAULT 'online',
  `siap_diambil` tinyint(4) NOT NULL DEFAULT 0,
  `tanggal_kirim` datetime DEFAULT NULL,
  `tanggal_cek_qris` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`kode_transaksi`, `id_pengguna`, `tanggal_transaksi`, `total_harga`, `total_pokok`, `status`, `metode_bayar`, `status_bayar`, `metode_pengiriman`, `kelurahan`, `nama_penerima`, `no_hp_penerima`, `alamat_pengiriman`, `ongkir`, `total_tagihan`, `batas_bayar`, `tanggal_bayar`, `tanggal_selesai`, `bukti_bayar`, `catatan_bayar`, `midtrans_order_id`, `midtrans_id`, `midtrans_env`, `qris_url`, `checkout_key`, `kecamatan`, `sumber_transaksi`, `siap_diambil`, `tanggal_kirim`, `tanggal_cek_qris`) VALUES
('GG-0423BF783EA9', 22, '2026-10-12 09:15:00', 65000.00, 45500.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'pelanggan', '085829230787', NULL, 0.00, 65000.00, '2026-10-12 20:49:34', '2026-10-12 09:15:00', '2026-10-12 09:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'online', 0, NULL, NULL),
('GG-08F3C54E61D1', 22, '2026-10-07 13:15:00', 90000.00, 63000.00, 'selesai', 'transfer', 'lunas', 'ambil', NULL, 'pelanggan', '085829230787', NULL, 0.00, 90000.00, '2026-10-12 20:49:34', '2026-10-07 13:15:00', '2026-10-07 13:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'online', 0, NULL, NULL),
('GG-1F2166A235D4', 1, '2026-10-12 20:30:47', 65000.00, 45500.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 65000.00, '2026-10-12 22:30:47', '2026-10-12 20:30:47', '2026-10-12 20:30:47', NULL, NULL, NULL, NULL, NULL, NULL, 'e7707c0e-e0ee-496c-9874-f472ec3faf32', NULL, 'kasir', 0, NULL, NULL),
('GG-28318AA3CF53', 22, '2026-09-09 15:15:00', 65000.00, 45500.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'pelanggan', '085829230787', NULL, 0.00, 65000.00, '2026-10-12 20:49:34', '2026-09-09 15:15:00', '2026-09-09 15:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'online', 0, NULL, NULL),
('GG-3A1F362BBB26', 1, '2026-10-12 20:29:44', 45000.00, 31500.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 22:29:44', '2026-10-12 20:29:44', '2026-10-12 20:29:44', NULL, NULL, NULL, NULL, NULL, NULL, 'b880e9bb-f480-4601-aba7-bfa0dbfaf13b', NULL, 'kasir', 0, NULL, NULL),
('GG-3B6DBC7919F6', 1, '2026-10-12 19:45:00', 45000.00, 31500.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 21:45:00', '2026-10-12 19:45:20', '2026-10-12 19:45:22', NULL, NULL, 'GG-3B6DBC7919F6', '67599c3f-3a13-407a-8179-218ad2c6707b', 'sandbox', NULL, '7e38aeea-25e2-4847-93b4-1a9c684df6c9', NULL, 'kasir', 0, NULL, NULL),
('GG-4C794F2A8E80', 1, '2026-10-12 20:30:04', 45000.00, 31500.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 22:30:04', '2026-10-12 20:30:25', '2026-10-12 20:30:25', NULL, NULL, 'GG-4C794F2A8E80', 'a6c3f57f-e6bd-47af-902f-1475165aa31c', 'sandbox', NULL, 'b7f2e0e3-1629-4eb9-823e-7baeff71f908', NULL, 'kasir', 0, NULL, NULL),
('GG-5000AB552B3B', 1, '2026-10-12 19:44:43', 90000.00, 63000.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 90000.00, '2026-10-12 21:44:43', '2026-10-12 19:44:43', '2026-10-12 19:44:43', NULL, NULL, NULL, NULL, NULL, NULL, 'd65c6c59-f179-4c18-b4c2-e53b4e0024a9', NULL, 'kasir', 0, NULL, NULL),
('GG-54E11921AA52', 22, '2026-10-12 23:47:59', 110000.00, 77000.00, 'selesai', 'qris', 'lunas', 'kirim', 'Kayu Putih', 'pelanggan', '6285829230787', 'Jl Perintis Kemerdekaan 2', 7000.00, 117000.00, NULL, '2026-10-12 23:48:17', '2026-10-13 00:14:06', NULL, NULL, 'GG-54E11921AA52', 'a9b807c2-086e-4c5a-ad80-b0074f2348ff', 'sandbox', NULL, '78364c4c-ff6e-4bad-b81a-51be4328faf4', 'Oebobo', 'online', 0, '2026-10-13 00:13:55', NULL),
('GG-5805602CC18C', 22, '2026-10-13 00:49:13', 60000.00, 0.00, 'diproses', 'transfer', 'lunas', 'ambil', NULL, 'pelanggan', '6285829230787', NULL, 0.00, 60000.00, NULL, '2026-10-13 00:50:17', NULL, '1575acab2a43a3427398bd37d5e0ea47ff37831a.png', '', NULL, NULL, NULL, NULL, 'ffb5653f-f8ec-4c68-b413-80ef7ab719c8', NULL, 'online', 1, NULL, NULL),
('GG-64AC5C9D9F7B', 22, '2026-10-10 11:15:00', 135000.00, 94500.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'pelanggan', '085829230787', NULL, 0.00, 135000.00, '2026-10-12 20:49:34', '2026-10-10 11:15:00', '2026-10-10 11:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'online', 0, NULL, NULL),
('GG-73D4D10DCAA1', 22, '2026-10-10 11:15:00', 65000.00, 0.00, 'dibatalkan', 'tunai', 'belum_bayar', 'kirim', 'Fatululi', 'pelanggan', '085829230787', 'Jl Perintis Kemerdekaan 1', 5000.00, 70000.00, '2026-10-12 20:49:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oebobo', 'online', 0, NULL, NULL),
('GG-757FE875C94F', 22, '2026-10-12 10:15:00', 135000.00, 94500.00, 'selesai', 'transfer', 'lunas', 'ambil', NULL, 'pelanggan', '085829230787', NULL, 0.00, 135000.00, '2026-10-12 20:49:34', '2026-10-12 10:15:00', '2026-10-12 19:06:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'online', 0, NULL, NULL),
('GG-7CDDC6D37F3E', 22, '2026-10-12 09:15:00', 90000.00, 0.00, 'dibatalkan', 'transfer', 'belum_bayar', 'kirim', 'Fatululi', 'pelanggan', '085829230787', 'Jl Perintis Kemerdekaan 1', 5000.00, 95000.00, '2026-10-12 20:49:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oebobo', 'online', 0, NULL, NULL),
('GG-88BDD71C994D', 1, '2026-10-12 19:40:09', 45000.00, 31500.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 21:40:09', '2026-10-12 19:41:42', '2026-10-12 19:41:55', NULL, NULL, 'GG-88BDD71C994D', 'f196f664-b380-40d2-a39c-36da41d148c7', 'sandbox', NULL, 'a73d6bc1-c666-4a8a-a71f-b617dc5607e7', NULL, 'kasir', 0, NULL, NULL),
('GG-942561BB0503', 1, '2026-10-12 21:55:28', 45000.00, 31500.00, 'selesai', 'transfer', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, NULL, '2026-10-12 21:55:54', '2026-10-12 21:55:54', 'ee3a4c8e6d917faa7bcb67ce0bd5231e5ce34f6f.png', '', NULL, NULL, NULL, NULL, '15a3956b-b426-4980-8532-53e6932eb08c', NULL, 'kasir', 0, NULL, NULL),
('GG-9542E5026F3B', 22, '2026-10-09 12:15:00', 65000.00, 45500.00, 'selesai', 'tunai', 'lunas', 'kirim', 'Fatululi', 'pelanggan', '085829230787', 'Jl Perintis Kemerdekaan 1', 5000.00, 70000.00, '2026-10-12 20:49:34', '2026-10-09 12:15:00', '2026-10-09 12:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oebobo', 'online', 0, NULL, NULL),
('GG-968560FAED70', 24, '2026-10-13 00:59:47', 71000.00, 0.00, 'pending', 'transfer', 'menunggu_verifikasi', 'kirim', 'Kayu Putih', 'Stiven Melkianus Adu', '6281000000000', 'Jalan Nasution 2, RT19/RW05', 7000.00, 78000.00, '2026-10-13 02:59:47', NULL, NULL, '860ca6f8810a065abc890678f136e807e2c727ce.jpg', NULL, NULL, NULL, NULL, NULL, '0887372d1609477c8e5a98d4e48f35d6', 'Oebobo', 'online', 0, NULL, NULL),
('GG-9D68EBB6D777', 1, '2026-10-12 18:58:14', 110000.00, 0.00, 'dibatalkan', 'qris', 'kedaluwarsa', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 110000.00, '2026-10-12 20:58:14', NULL, NULL, NULL, NULL, 'GG-9D68EBB6D777', '4e617ecf-d22e-4634-88b6-2a63a98e81d3', 'sandbox', NULL, '81249061-7446-47fb-9591-76bae545034a', NULL, 'kasir', 0, NULL, NULL),
('GG-A985CD63D2A0', 22, '2026-09-30 14:15:00', 135000.00, 94500.00, 'selesai', 'qris', 'lunas', 'kirim', 'Fatululi', 'pelanggan', '085829230787', 'Jl Perintis Kemerdekaan 1', 5000.00, 140000.00, '2026-10-12 20:49:34', '2026-09-30 14:15:00', '2026-09-30 14:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oebobo', 'online', 0, NULL, NULL),
('GG-B1D502F9E68A', 1, '2026-10-12 21:56:09', 110000.00, 77000.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 110000.00, NULL, '2026-10-12 21:56:37', '2026-10-12 21:56:37', NULL, NULL, 'GG-B1D502F9E68A', '1e73b789-f784-43c0-9293-8ad4bd6071de', 'sandbox', NULL, '4dd7872f-074c-42f1-a242-647079f71db4', NULL, 'kasir', 0, NULL, NULL),
('GG-B2D74B37361B', 22, '2026-10-12 20:25:08', 135000.00, 94500.00, 'selesai', 'qris', 'lunas', 'kirim', 'Kayu Putih', 'pelanggan', '6285829230787', 'Jl Perintis Kemerdekaan 1', 10000.00, 145000.00, '2026-10-12 22:25:08', '2026-10-12 20:26:10', '2026-10-12 20:28:28', NULL, NULL, 'GG-B2D74B37361B', 'a1680263-c1ec-4242-8926-4223ee90e109', 'sandbox', NULL, '9921f4d4-fafb-471e-91ab-7341c9ff90d3', 'Oebobo', 'online', 0, '2026-10-12 20:27:11', NULL),
('GG-B4E733AED04D', 22, '2026-10-12 20:43:32', 45000.00, 31500.00, 'selesai', 'transfer', 'lunas', 'kirim', 'Kayu Putih', 'pelanggan', '6285829230787', 'Jl Perintis Kemerdekaan 1', 7000.00, 52000.00, '2026-10-12 22:43:32', '2026-10-12 20:51:59', '2026-10-12 21:54:29', 'da6766aecabc23cb75f538575185d3efe991623b.png', '', NULL, NULL, NULL, NULL, 'd4ed35b8-bd6d-49d3-8da2-2ff7ed0d788c', 'Oebobo', 'online', 0, '2026-10-12 21:54:18', NULL),
('GG-BC4714506D05', 22, '2026-10-12 19:38:30', 45000.00, 0.00, 'dibatalkan', 'qris', 'kedaluwarsa', 'kirim', 'Bonipoi', 'pelanggan', '6285829230787', 'Jl Perintis Kemerdekaan 1', 15000.00, 60000.00, '2026-10-12 21:38:30', NULL, NULL, NULL, NULL, 'GG-BC4714506D05', 'c56f060d-f6e3-4f93-ae46-e9b2a774365f', 'sandbox', NULL, 'cb28b84f-d8a1-45f5-9b7f-a4339aeb3bb4', 'Kota Lama', 'online', 0, NULL, NULL),
('GG-C673285D062A', 22, '2026-10-12 20:10:43', 45000.00, 31500.00, 'selesai', 'transfer', 'lunas', 'ambil', NULL, 'pelanggan', '6285829230787', NULL, 0.00, 45000.00, '2026-10-12 22:10:43', '2026-10-12 20:12:25', '2026-10-12 20:14:37', '6fb073179734cc8d79462e5c1fde4b19fe04adb9.png', '', NULL, NULL, NULL, NULL, 'ec5c60c7-15b9-490c-a8fd-5cfcbdb98c63', NULL, 'online', 1, NULL, NULL),
('GG-E7C53E868EAE', 1, '2026-10-12 20:31:19', 45000.00, 31500.00, 'selesai', 'tunai', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 22:31:19', '2026-10-12 20:31:19', '2026-10-12 20:31:19', NULL, NULL, NULL, NULL, NULL, NULL, '9e58632e-3296-4a7c-9174-2cdb7a5b67d8', NULL, 'kasir', 0, NULL, NULL),
('GG-EAD64FEE545C', 1, '2026-10-12 20:30:52', 45000.00, 31500.00, 'selesai', 'qris', 'lunas', 'ambil', NULL, 'admin', NULL, NULL, 0.00, 45000.00, '2026-10-12 22:30:52', '2026-10-12 20:31:11', '2026-10-12 20:31:11', NULL, NULL, 'GG-EAD64FEE545C', '074ad6d4-8233-4bb4-98b5-6c65f76b6c0a', 'sandbox', NULL, 'aebd5391-ffdc-4444-8698-557e84fc8bae', NULL, 'kasir', 0, NULL, NULL),
('GG-F66DE86C0F47', 22, '2026-10-11 10:15:00', 90000.00, 63000.00, 'selesai', 'transfer', 'lunas', 'kirim', 'Fatululi', 'pelanggan', '085829230787', 'Jl Perintis Kemerdekaan 1', 5000.00, 95000.00, '2026-10-12 20:49:34', '2026-10-11 10:15:00', '2026-10-11 10:15:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Oebobo', 'online', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `zona_pengiriman`
--

CREATE TABLE `zona_pengiriman` (
  `id` int(11) NOT NULL,
  `kelurahan` varchar(100) NOT NULL,
  `tarif` decimal(12,2) NOT NULL,
  `aktif` tinyint(4) NOT NULL DEFAULT 1,
  `kecamatan` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `zona_pengiriman`
--

INSERT INTO `zona_pengiriman` (`id`, `kelurahan`, `tarif`, `aktif`, `kecamatan`) VALUES
(1, 'Kelapa Lima', 5000.00, 1, 'Kelapa Lima'),
(2, 'Pasir Panjang', 5000.00, 1, 'Kota Lama'),
(3, 'Fatululi', 5000.00, 1, 'Oebobo'),
(4, 'Nefonaek', 5000.00, 1, 'Kota Lama'),
(5, 'Oebobo', 10000.00, 1, 'Oebobo'),
(6, 'Oetete', 10000.00, 1, 'Oebobo'),
(7, 'Kayu Putih', 7000.00, 1, 'Oebobo'),
(8, 'Fatubesi', 10000.00, 1, 'Kota Lama'),
(9, 'Oeba', 10000.00, 1, 'Kota Lama'),
(10, 'Oesapa Barat', 10000.00, 1, 'Kelapa Lima'),
(11, 'Oesapa Selatan', 10000.00, 1, 'Kelapa Lima'),
(12, 'Tuak Daun Merah', 15000.00, 1, 'Oebobo'),
(13, 'Oebufu', 15000.00, 1, 'Oebobo'),
(14, 'Oesapa', 15000.00, 1, 'Kelapa Lima'),
(15, 'Bonipoi', 15000.00, 1, 'Kota Lama'),
(16, 'Solor', 15000.00, 1, 'Kota Lama'),
(17, 'Tode Kisar', 15000.00, 1, 'Kota Lama'),
(18, 'Merdeka', 15000.00, 1, 'Kota Lama'),
(19, 'Lai Lai Bisi Kopan', 15000.00, 1, 'Kota Lama'),
(20, 'Fontein', 15000.00, 1, 'Kota Raja'),
(21, 'Nunleu', 15000.00, 1, 'Kota Raja'),
(22, 'Kuanino', 15000.00, 1, 'Kota Raja'),
(23, 'Airnona', 15000.00, 1, 'Kota Raja'),
(24, 'Naikoten Satu', 15000.00, 1, 'Kota Raja'),
(25, 'Naikoten Dua', 15000.00, 1, 'Kota Raja'),
(26, 'Oepura', 15000.00, 1, 'Maulafa'),
(27, 'Naikolan', 15000.00, 1, 'Maulafa'),
(28, 'Bakunase', 15000.00, 1, 'Kota Raja'),
(29, 'Nunhila', 15000.00, 1, 'Alak'),
(30, 'Fatufeto', 15000.00, 1, 'Alak'),
(31, 'Nunbaun Sabu', 15000.00, 1, 'Alak'),
(32, 'Airmata', 15000.00, 1, 'Kota Lama'),
(33, 'Liliba', 20000.00, 1, 'Oebobo'),
(34, 'Naimata', 20000.00, 1, 'Maulafa'),
(35, 'Maulafa', 20000.00, 1, 'Maulafa'),
(36, 'Sikumana', 20000.00, 1, 'Maulafa'),
(37, 'Bello', 20000.00, 1, 'Maulafa'),
(38, 'Kolhua', 20000.00, 1, 'Maulafa'),
(39, 'Bakunase Dua', 20000.00, 1, 'Kota Raja'),
(40, 'Nunbaun Delha', 20000.00, 1, 'Alak'),
(41, 'Manutapen', 20000.00, 1, 'Alak'),
(42, 'Mantasi', 20000.00, 1, 'Alak'),
(43, 'Namosain', 20000.00, 1, 'Alak'),
(44, 'Lasiana', 25000.00, 1, 'Kelapa Lima'),
(45, 'Penfui', 25000.00, 1, 'Maulafa'),
(46, 'Batu Plat', 25000.00, 1, 'Alak'),
(47, 'Penkase Oeleta', 25000.00, 1, 'Alak'),
(48, 'Alak', 30000.00, 1, 'Alak'),
(49, 'Manulai II', 30000.00, 1, 'Alak'),
(50, 'Fatukoa', 30000.00, 1, 'Maulafa'),
(51, 'Naioni', 35000.00, 1, 'Alak');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alokasi_stok`
--
ALTER TABLE `alokasi_stok`
  ADD PRIMARY KEY (`kode_transaksi`,`id_mutasi`),
  ADD KEY `id_mutasi` (`id_mutasi`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_kode_transaksi` (`kode_transaksi`),
  ADD KEY `idx_kode_produk` (`kode_produk`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD KEY `idx_id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `fk_keranjang_user` (`id_pengguna`),
  ADD KEY `fk_keranjang_produk` (`kode_produk`);

--
-- Indeks untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `kode_produk` (`kode_produk`),
  ADD KEY `idx_tanggal` (`tanggal`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notifikasi_penerima` (`id_riwayat`,`id_pengguna`),
  ADD KEY `idx_notifikasi_inbox` (`id_pengguna`,`peran`,`dibaca_pada`,`id`),
  ADD KEY `idx_notifikasi_terbaru` (`id_pengguna`,`peran`,`tanggal`);

--
-- Indeks untuk tabel `notifikasi_kirim`
--
ALTER TABLE `notifikasi_kirim`
  ADD PRIMARY KEY (`id_notifikasi`,`perangkat`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`nama`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_id_user` (`id_pengguna`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`kode_produk`),
  ADD KEY `idx_kode_produk` (`kode_produk`),
  ADD KEY `idx_nama_produk` (`nama_produk`),
  ADD KEY `idx_id_kategori` (`id_kategori`),
  ADD KEY `idx_tanggal_dibuat` (`tanggal_dibuat`),
  ADD KEY `idx_produk_penjualan` (`id_kategori`,`terjual`);

--
-- Indeks untuk tabel `reservasi_stok`
--
ALTER TABLE `reservasi_stok`
  ADD PRIMARY KEY (`kode_transaksi`,`kode_produk`),
  ADD KEY `kode_produk` (`kode_produk`);

--
-- Indeks untuk tabel `riwayat_pesanan`
--
ALTER TABLE `riwayat_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kunci_peristiwa` (`kunci_peristiwa`),
  ADD KEY `idx_pesanan_tanggal` (`kode_transaksi`,`tanggal`,`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`kode_transaksi`),
  ADD UNIQUE KEY `uq_checkout_key` (`checkout_key`),
  ADD UNIQUE KEY `uq_midtrans_order` (`midtrans_order_id`),
  ADD KEY `idx_kode_transaksi` (`kode_transaksi`),
  ADD KEY `idx_tanggal_transaksi` (`tanggal_transaksi`),
  ADD KEY `idx_metode_bayar` (`metode_bayar`),
  ADD KEY `idx_id_user` (`id_pengguna`);

--
-- Indeks untuk tabel `zona_pengiriman`
--
ALTER TABLE `zona_pengiriman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelurahan` (`kelurahan`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pesanan`
--
ALTER TABLE `riwayat_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=528;

--
-- AUTO_INCREMENT untuk tabel `zona_pengiriman`
--
ALTER TABLE `zona_pengiriman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alokasi_stok`
--
ALTER TABLE `alokasi_stok`
  ADD CONSTRAINT `alokasi_stok_ibfk_1` FOREIGN KEY (`kode_transaksi`) REFERENCES `transaksi` (`kode_transaksi`),
  ADD CONSTRAINT `alokasi_stok_ibfk_2` FOREIGN KEY (`id_mutasi`) REFERENCES `mutasi_stok` (`id_mutasi`);

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`kode_produk`) REFERENCES `produk` (`kode_produk`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`kode_transaksi`) REFERENCES `transaksi` (`kode_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`kode_produk`) REFERENCES `produk` (`kode_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mutasi_stok`
--
ALTER TABLE `mutasi_stok`
  ADD CONSTRAINT `mutasi_stok_ibfk_1` FOREIGN KEY (`kode_produk`) REFERENCES `produk` (`kode_produk`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_riwayat`) REFERENCES `riwayat_pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi_kirim`
--
ALTER TABLE `notifikasi_kirim`
  ADD CONSTRAINT `notifikasi_kirim_ibfk_1` FOREIGN KEY (`id_notifikasi`) REFERENCES `notifikasi` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reservasi_stok`
--
ALTER TABLE `reservasi_stok`
  ADD CONSTRAINT `reservasi_stok_ibfk_1` FOREIGN KEY (`kode_transaksi`) REFERENCES `transaksi` (`kode_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservasi_stok_ibfk_2` FOREIGN KEY (`kode_produk`) REFERENCES `produk` (`kode_produk`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `riwayat_pesanan`
--
ALTER TABLE `riwayat_pesanan`
  ADD CONSTRAINT `riwayat_pesanan_ibfk_1` FOREIGN KEY (`kode_transaksi`) REFERENCES `transaksi` (`kode_transaksi`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
