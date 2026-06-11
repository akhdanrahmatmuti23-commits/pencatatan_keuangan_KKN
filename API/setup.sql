CREATE DATABASE IF NOT EXISTS kkn_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE kkn_finance;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nim VARCHAR(50) NOT NULL UNIQUE,
  prodi VARCHAR(100) DEFAULT NULL,
  jabatan VARCHAR(100) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  description VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (nama, nim, prodi, jabatan, email, password, role) VALUES
('Admin KKN', 'admin', 'Teknologi Informasi', 'Koordinator', 'admin@kkn-keuangan.local', '$2y$12$sTCm2ePcUfEj3.7y9ULVzOBsWtCvqa.aCFwFh1PYtUIdiZxO9isKC', 'admin'),
('User KKN', '2310001', 'Manajemen', 'Anggota', 'user@kkn-keuangan.local', '$2y$12$UOYb4vYyq3MGVuth8Rqu1u2oPJP5A4rJYmsaUEeL46y4QfJBiB8b2', 'user');

INSERT INTO transactions (user_id, date, description, category, type, amount) VALUES
(2, '2026-06-02', 'Pembelian ATK kegiatan', 'Operasional', 'expense', 450000.00),
(2, '2026-05-28', 'Dana bantuan kelompok', 'Pemasukan', 'income', 2000000.00),
(2, '2026-05-15', 'Transportasi lapangan', 'Logistik', 'expense', 950000.00),
(2, '2026-05-10', 'Hasil penjualan donasi', 'Pemasukan', 'income', 500000.00);
