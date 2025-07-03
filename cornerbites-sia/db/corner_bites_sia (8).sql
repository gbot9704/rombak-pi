-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 03, 2025 at 10:58 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `corner_bites_sia`
--

-- --------------------------------------------------------

--
-- Table structure for table `labor_costs`
--

CREATE TABLE `labor_costs` (
  `id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL,
  `hourly_rate` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labor_costs`
--

INSERT INTO `labor_costs` (`id`, `position_name`, `hourly_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Barista', '25000.00', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(2, 'Baker', '30000.00', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(3, 'Kitchen Staff', '20000.00', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(4, 'Manager', '50000.00', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(5, 'Intern', '20000.00', 1, '2025-07-01 06:31:02', '2025-07-01 06:31:02'),
(6, 'Pembantu', '5000.00', 1, '2025-07-01 07:19:35', '2025-07-01 07:19:35'),
(7, 'test', '1500000.00', 1, '2025-07-03 08:35:58', '2025-07-03 08:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overhead_costs`
--

CREATE TABLE `overhead_costs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `allocation_method` enum('percentage','per_unit','per_hour') DEFAULT 'percentage',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overhead_costs`
--

INSERT INTO `overhead_costs` (`id`, `name`, `description`, `amount`, `allocation_method`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sewa Toko', 'Biaya sewa bulanan', '5000000.00', 'percentage', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(2, 'Listrik & Air', 'Utility bulanan', '1500000.00', 'percentage', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(3, 'Gaji Admin', 'Gaji karyawan admin', '3000000.00', 'percentage', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(4, 'Penyusutan Peralatan', 'Depresiasi peralatan produksi', '500000.00', 'per_hour', 1, '2025-06-30 19:25:30', '2025-06-30 19:25:30'),
(5, 'test', '', '15000.00', 'per_unit', 1, '2025-06-30 19:28:00', '2025-06-30 19:28:00'),
(6, 'Contoh', 'Contoh', '20000.00', 'percentage', 0, '2025-07-01 06:30:49', '2025-07-03 08:54:18'),
(7, 'Listrik', '', '50000.00', 'percentage', 1, '2025-07-01 06:40:02', '2025-07-01 06:40:02'),
(8, 'Air', 'Test', '30000.00', 'percentage', 1, '2025-07-01 06:46:05', '2025-07-03 08:57:34'),
(9, 'Gaji Yoga', 'test', '50000.00', 'percentage', 0, '2025-07-01 08:00:52', '2025-07-01 08:45:55'),
(10, 'Gaji Nanda', 'test', '200000.00', 'percentage', 1, '2025-07-01 08:00:58', '2025-07-01 08:00:58'),
(11, 'Gaji Haikal', 'test', '50000.00', 'percentage', 1, '2025-07-01 08:01:10', '2025-07-01 08:01:10'),
(12, 'Apar', 'test', '50000.00', 'percentage', 0, '2025-07-01 08:32:52', '2025-07-03 08:54:22'),
(13, 'Popo', 'test', '50000.00', 'percentage', 0, '2025-07-03 08:10:08', '2025-07-03 08:10:15'),
(14, 'Gas', 'gas', '50000.00', 'percentage', 1, '2025-07-03 08:53:18', '2025-07-03 08:53:18');

-- --------------------------------------------------------

--
-- Table structure for table `production_batches`
--

CREATE TABLE `production_batches` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(100) NOT NULL,
  `quantity_produced` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `direct_material_cost` decimal(15,2) DEFAULT 0.00,
  `direct_labor_cost` decimal(15,2) DEFAULT 0.00,
  `overhead_cost` decimal(15,2) DEFAULT 0.00,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `cost_per_unit` decimal(15,2) DEFAULT 0.00,
  `labor_hours` decimal(8,2) DEFAULT 0.00,
  `machine_hours` decimal(8,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT 'Harga Pokok Produksi per unit (bisa dihitung dari resep)',
  `production_yield` int(11) DEFAULT 1,
  `sale_price` decimal(15,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `production_time_hours` decimal(5,2) DEFAULT 1.00,
  `overhead_percentage` decimal(5,2) DEFAULT 0.00,
  `direct_labor_cost` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `unit`, `cost_price`, `production_yield`, `sale_price`, `stock`, `created_at`, `updated_at`, `production_time_hours`, `overhead_percentage`, `direct_labor_cost`) VALUES
(1, 'restu babat', 'pcs', '100.00', 10, '20000.00', 5, '2025-06-21 04:20:12', '2025-07-01 05:42:19', '1.00', '0.00', '0.00'),
(2, 'yoga kue', 'pcs', '25000.00', 1, '100000.00', 15, '2025-06-21 04:56:38', '2025-07-01 05:42:49', '1.00', '0.00', '0.00'),
(3, 'jenn', 'pcs', '20000.00', 100, '70000.00', 0, '2025-06-21 15:49:21', '2025-07-01 05:42:57', '1.00', '0.00', '0.00'),
(4, 'haikal bakar', 'custom', '77000.00', 123, '10000.00', 12, '2025-06-29 11:09:01', '2025-07-01 06:28:37', '1.00', '0.00', '0.00'),
(6, 'Kopi', 'pcs', NULL, 1, '60000.00', 500, '2025-07-01 05:49:22', '2025-07-01 05:49:22', '1.00', '0.00', '0.00'),
(7, 'Pisang Goreng', 'pcs', NULL, 1, '5000.00', 20, '2025-07-01 06:32:42', '2025-07-01 06:32:42', '1.00', '0.00', '0.00'),
(8, 'Donat Deso', 'pcs', NULL, 30, '5000.00', 50, '2025-07-01 06:33:01', '2025-07-03 07:52:04', '1.00', '0.00', '0.00'),
(9, 'Kentang Goreng', 'porsi', NULL, 1, '25000.00', 30, '2025-07-01 06:33:15', '2025-07-01 06:33:15', '1.00', '0.00', '0.00'),
(10, 'Croisant', 'pcs', NULL, 1, '20000.00', 30, '2025-07-01 06:33:33', '2025-07-01 06:33:33', '1.00', '0.00', '0.00'),
(11, 'Ketan Susu', 'porsi', NULL, 1, '15000.00', 20, '2025-07-01 06:33:45', '2025-07-01 06:33:45', '1.00', '0.00', '0.00'),
(12, 'Lemon Grass Tea', 'pcs', NULL, 1, '19000.00', 30, '2025-07-01 06:34:35', '2025-07-01 06:34:35', '1.00', '0.00', '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `product_recipes`
--

CREATE TABLE `product_recipes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'ID Produk Jadi (dari tabel products)',
  `raw_material_id` int(11) NOT NULL COMMENT 'ID Bahan Baku (dari tabel raw_materials)',
  `quantity_used` decimal(10,4) NOT NULL COMMENT 'Jumlah bahan baku yang digunakan untuk 1 unit produk jadi',
  `unit_measurement` varchar(50) DEFAULT NULL COMMENT 'Satuan pengukuran dalam resep (e.g., gram, ml, pcs)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Resep produk jadi berdasarkan bahan baku';

--
-- Dumping data for table `product_recipes`
--

INSERT INTO `product_recipes` (`id`, `product_id`, `raw_material_id`, `quantity_used`, `unit_measurement`, `created_at`, `updated_at`) VALUES
(2, 3, 3, '10.0000', 'gram', '2025-06-24 07:48:35', NULL),
(3, 3, 7, '250.0000', 'gram', '2025-06-24 12:36:21', NULL),
(4, 1, 8, '250.0000', 'gram', '2025-06-24 12:39:00', NULL),
(5, 1, 6, '500.0000', 'gram', '2025-06-28 05:23:50', NULL),
(6, 1, 3, '10.0000', 'gram', '2025-06-28 05:24:18', NULL),
(8, 4, 7, '250.0000', 'gram', '2025-07-01 06:28:57', NULL),
(9, 6, 7, '250.0000', 'gram', '2025-07-01 06:29:29', NULL),
(10, 8, 7, '500.0000', 'gram', '2025-07-03 07:31:33', '2025-07-03 07:31:48');

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nama bahan baku atau kemasan',
  `brand` varchar(255) DEFAULT NULL,
  `unit` varchar(50) NOT NULL COMMENT 'Satuan dasar pembelian atau penggunaan (e.g., kg, gram, liter, pcs, lembar)',
  `type` enum('bahan','kemasan') NOT NULL DEFAULT 'bahan',
  `purchase_price_per_unit` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga beli per satuan dasar',
  `default_package_quantity` decimal(10,3) DEFAULT NULL COMMENT 'Kuantitas default per paket/satuan beli (e.g., 1.5 untuk 1.5kg, 500 untuk 500ml)',
  `current_stock` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah stok saat ini',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Daftar bahan baku dan kemasan';

--
-- Dumping data for table `raw_materials`
--

INSERT INTO `raw_materials` (`id`, `name`, `brand`, `unit`, `type`, `purchase_price_per_unit`, `default_package_quantity`, `current_stock`, `created_at`, `updated_at`) VALUES
(2, 'Tepung', NULL, 'kg', 'bahan', '10000.00', NULL, '10.00', '2025-06-22 10:56:53', '2025-06-22 11:21:22'),
(3, 'Maizena', NULL, 'kg', 'bahan', '10000.00', NULL, '2.00', '2025-06-22 11:27:04', '2025-06-22 11:30:54'),
(4, 'Gula Pasir', NULL, 'kg', 'bahan', '15500.00', NULL, '1.00', '2025-06-22 11:32:01', '2025-06-24 07:55:11'),
(6, 'Gula Merah', '', 'gram', 'bahan', '25000.00', '1000.000', '1.00', '2025-06-24 08:03:20', '2025-06-29 17:20:45'),
(7, 'Gula Halus', '', 'gram', 'bahan', '20000.00', '25.500', '6.00', '2025-06-24 08:03:47', '2025-06-29 13:30:54'),
(8, 'Jamur', 'Champinon', 'gram', 'bahan', '34000.00', '1000.000', '1.00', '2025-06-24 08:06:25', '2025-06-24 12:38:35'),
(9, 'Plastik Bawang', '', 'pcs', 'kemasan', '30000.00', '500.000', '500.00', '2025-06-24 08:45:35', '2025-06-29 13:31:01'),
(10, 'Telur', 'Warung', 'gram', 'bahan', '32000.00', '1500.000', '20.00', '2025-06-25 11:59:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(12, 'admin', '$2y$10$8xA3oov7m3mmREj4ymIESerL9p9u7VOX7f30cakIKW26h5pqXt12y', 'admin', '2025-06-19 08:55:53'),
(13, 'adminku', '$2y$10$u0pY71Xs5x0.G30SC9C9f.hsK/alnBIvkxNW8J6L1j06M.zCwiLL2', 'admin', '2025-06-19 09:00:28'),
(14, 'cornerbites', '$2y$10$x84NxRds4m6LNvh1FkqD6.rFKiXHeQPEzaNBakDvS6rSZKCoXaQwW', 'user', '2025-06-19 12:33:26'),
(15, 'yogagi', '$2y$10$hAYCkLzWy7GmNnAFVAD4reqwd0zI6WpUQi5FuFIU9H2.P6ZrzjZi2', 'user', '2025-06-20 12:32:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `labor_costs`
--
ALTER TABLE `labor_costs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `overhead_costs`
--
ALTER TABLE `overhead_costs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `production_batches`
--
ALTER TABLE `production_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_recipes`
--
ALTER TABLE `product_recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_raw_material_unique` (`product_id`,`raw_material_id`),
  ADD KEY `raw_material_id` (`raw_material_id`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `labor_costs`
--
ALTER TABLE `labor_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overhead_costs`
--
ALTER TABLE `overhead_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `production_batches`
--
ALTER TABLE `production_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_recipes`
--
ALTER TABLE `product_recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `production_batches`
--
ALTER TABLE `production_batches`
  ADD CONSTRAINT `production_batches_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `production_batches_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_recipes`
--
ALTER TABLE `product_recipes`
  ADD CONSTRAINT `product_recipes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_recipes_ibfk_2` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
