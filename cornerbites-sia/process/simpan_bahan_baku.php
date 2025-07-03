<?php
// process/simpan_bahan_baku.php
// File ini menangani logika penyimpanan/pembaruan/penghapusan bahan baku.

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../includes/auth_check.php'; // Pastikan user sudah login
require_once __DIR__ . '/../config/db.php'; // Sertakan file koneksi database

try {
    $conn = $db; // Menggunakan koneksi $db dari db.php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- Proses Tambah/Edit Bahan Baku ---
        $bahan_baku_id = $_POST['bahan_baku_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $type = trim($_POST['type'] ?? 'bahan');
        $unit = trim($_POST['unit'] ?? '');
        $purchase_size = (float) ($_POST['purchase_size'] ?? 0);
        // Pastikan nama kolom di PHP sesuai dengan nama kolom di DB
        $purchase_price_per_unit = (float) ($_POST['purchase_price_per_unit'] ?? 0);
        $current_stock = (float) ($_POST['current_stock'] ?? 0); // Ubah ke float jika stok bisa desimal

        // Validasi dasar
        if (empty($name) || empty($unit) || $purchase_size <= 0 || $purchase_price_per_unit <= 0 || $current_stock < 0) {
            $_SESSION['bahan_baku_message'] = ['text' => 'Nama, satuan, ukuran beli, harga beli per unit (harus > 0), dan stok tidak boleh kosong atau negatif.', 'type' => 'error'];
            header("Location: /cornerbites-sia/pages/bahan_baku.php");
            exit();
        }

        if ($bahan_baku_id) {
            // Update Bahan Baku
            $stmt = $conn->prepare("UPDATE raw_materials SET name = ?, brand = ?, type = ?, unit = ?, default_package_quantity = ?, purchase_price_per_unit = ?, current_stock = ?, updated_at = CURRENT_TIMESTAMP() WHERE id = ?");
            if ($stmt->execute([$name, $brand, $type, $unit, $purchase_size, $purchase_price_per_unit, $current_stock, $bahan_baku_id])) {
                $_SESSION['bahan_baku_message'] = ['text' => 'Bahan baku berhasil diperbarui!', 'type' => 'success'];
            } else {
                $_SESSION['bahan_baku_message'] = ['text' => 'Gagal memperbarui bahan baku.', 'type' => 'error'];
            }
        } else {
            // Tambah Bahan Baku Baru
            // Cek duplikasi nama dan brand (boleh sama nama jika brand berbeda)
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM raw_materials WHERE name = ? AND brand = ?");
            $stmtCheck->execute([$name, $brand]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['bahan_baku_message'] = ['text' => 'Kombinasi nama dan brand sudah ada. Gunakan kombinasi lain.', 'type' => 'error'];
                header("Location: /cornerbites-sia/pages/bahan_baku.php");
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO raw_materials (name, brand, type, unit, default_package_quantity, purchase_price_per_unit, current_stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $brand, $type, $unit, $purchase_size, $purchase_price_per_unit, $current_stock])) {
                $_SESSION['bahan_baku_message'] = ['text' => 'Bahan baku baru berhasil ditambahkan!', 'type' => 'success'];
            } else {
                $_SESSION['bahan_baku_message'] = ['text' => 'Gagal menambahkan bahan baku baru.', 'type' => 'error'];
            }
        }
        header("Location: /cornerbites-sia/pages/bahan_baku.php");
        exit();

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        // --- Proses Hapus Bahan Baku ---
        $bahan_baku_id = (int) $_GET['id'];

        // Cek apakah bahan baku ini digunakan di resep produk mana pun
        $stmtCheckRecipe = $conn->prepare("SELECT COUNT(*) FROM product_recipes WHERE raw_material_id = ?");
        $stmtCheckRecipe->execute([$bahan_baku_id]);
        if ($stmtCheckRecipe->fetchColumn() > 0) {
            $_SESSION['bahan_baku_message'] = ['text' => 'Tidak bisa menghapus bahan baku karena sudah digunakan dalam resep produk. Hapus resep yang menggunakan bahan baku ini terlebih dahulu.', 'type' => 'error'];
            header("Location: /cornerbites-sia/pages/bahan_baku.php");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM raw_materials WHERE id = ?");
        if ($stmt->execute([$bahan_baku_id])) {
            $_SESSION['bahan_baku_message'] = ['text' => 'Bahan baku berhasil dihapus!', 'type' => 'success'];
        } else {
            $_SESSION['bahan_baku_message'] = ['text' => 'Gagal menghapus bahan baku.', 'type' => 'error'];
        }
        header("Location: /cornerbites-sia/pages/bahan_baku.php");
        exit();

    } else {
        // Jika diakses langsung tanpa POST/GET yang valid, redirect
        header("Location: /cornerbites-sia/pages/bahan_baku.php");
        exit();
    }

} catch (PDOException $e) {
    error_log("Error simpan/hapus bahan baku: " . $e->getMessage());
    $_SESSION['bahan_baku_message'] = ['text' => 'Terjadi kesalahan sistem: ' . $e->getMessage(), 'type' => 'error'];
    header("Location: /cornerbites-sia/pages/bahan_baku.php");
    exit();
}