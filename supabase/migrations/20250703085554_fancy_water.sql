-- Database tables untuk Overhead Management
-- Jalankan script ini di phpMyAdmin atau MySQL client Anda

-- Tabel untuk biaya overhead
CREATE TABLE IF NOT EXISTS overhead_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
);

-- Tabel untuk biaya tenaga kerja
CREATE TABLE IF NOT EXISTS labor_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(255) NOT NULL,
    hourly_rate DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_position_name (position_name),
    INDEX idx_is_active (is_active)
);

-- Tambahkan kolom untuk HPP calculation di tabel products (jika belum ada)
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS production_yield INT DEFAULT 1 COMMENT 'Hasil produksi per batch',
ADD COLUMN IF NOT EXISTS production_time_minutes INT DEFAULT 60 COMMENT 'Waktu produksi dalam menit',
ADD COLUMN IF NOT EXISTS cost_price DECIMAL(15, 2) DEFAULT 0.00 COMMENT 'Harga pokok produksi (HPP)',
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update kolom production_time_minutes menjadi production_time_hours untuk konsistensi
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS production_time_hours DECIMAL(5, 2) DEFAULT 1.0 COMMENT 'Waktu produksi dalam jam';

-- Insert sample data untuk testing (opsional)
INSERT IGNORE INTO overhead_costs (name, description, amount) VALUES
('Listrik', 'Biaya listrik bulanan untuk operasional', 500000),
('Sewa Tempat', 'Biaya sewa tempat usaha per bulan', 2000000),
('Internet & Telepon', 'Biaya komunikasi dan internet', 300000),
('Kebersihan', 'Biaya kebersihan dan sanitasi', 200000);

INSERT IGNORE INTO labor_costs (position_name, hourly_rate) VALUES
('Koki Utama', 35000),
('Asisten Koki', 25000),
('Kasir', 20000),
('Pelayan', 18000),
('Cleaning Service', 15000);