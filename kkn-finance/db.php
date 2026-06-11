<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'kkn_finance';

$conn = mysqli_connect($dbHost, $dbUser, $dbPass);
if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

if (!mysqli_select_db($conn, $dbName)) {
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!mysqli_query($conn, $createDbSql)) {
        die('Gagal membuat database: ' . mysqli_error($conn));
    }
    if (!mysqli_select_db($conn, $dbName)) {
        die('Gagal memilih database: ' . mysqli_error($conn));
    }
}

mysqli_set_charset($conn, 'utf8mb4');

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nim VARCHAR(50) NOT NULL UNIQUE,
  prodi VARCHAR(100) DEFAULT NULL,
  jabatan VARCHAR(100) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (!mysqli_query($conn, $createUsersTable)) {
    die('Gagal membuat tabel users: ' . mysqli_error($conn));
}

$createTransactionsTable = "CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  description VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  type ENUM('income','expense') NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (!mysqli_query($conn, $createTransactionsTable)) {
    die('Gagal membuat tabel transactions: ' . mysqli_error($conn));
}

add_column_if_missing($conn, 'transactions', 'notes', 'notes VARCHAR(255) DEFAULT NULL');

function add_column_if_missing($conn, $table, $column, $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($check && mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "ALTER TABLE `$table` ADD COLUMN $definition");
    }
}

add_column_if_missing($conn, 'users', 'prodi', 'prodi VARCHAR(100) DEFAULT NULL');
add_column_if_missing($conn, 'users', 'jabatan', 'jabatan VARCHAR(100) DEFAULT NULL');
add_column_if_missing($conn, 'users', 'email', 'email VARCHAR(100) DEFAULT NULL');

$defaultAdminName = 'Admin KKN';
$defaultAdminNim = '000';
$defaultAdminProdi = 'Teknologi Informasi';
$defaultAdminJabatan = 'Koordinator';
$defaultAdminEmail = 'admin@kkn-keuangan.local';
$defaultAdminPassword = '$2y$12$qS6BPn983Aa89mxKS7dQ.epha7kyqEDZPx7U536/f2BBkWp2LRiKO'; // hash untuk '000'
$defaultAdminRole = 'admin';

$adminCheck = mysqli_query($conn, "SELECT id, nim, password FROM users WHERE role = 'admin' LIMIT 1");
if ($adminCheck) {
    if (mysqli_num_rows($adminCheck) === 0) {
        $insertAdmin = mysqli_prepare($conn, 'INSERT INTO users (nama, nim, prodi, jabatan, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($insertAdmin) {
            mysqli_stmt_bind_param($insertAdmin, 'sssssss', $defaultAdminName, $defaultAdminNim, $defaultAdminProdi, $defaultAdminJabatan, $defaultAdminEmail, $defaultAdminPassword, $defaultAdminRole);
            mysqli_stmt_execute($insertAdmin);
            mysqli_stmt_close($insertAdmin);
        }
    } else {
        $adminRow = mysqli_fetch_assoc($adminCheck);
        if ($adminRow['nim'] !== $defaultAdminNim || !password_verify('000', $adminRow['password'])) {
            $updateAdmin = mysqli_prepare($conn, 'UPDATE users SET nim = ?, password = ? WHERE id = ?');
            if ($updateAdmin) {
                mysqli_stmt_bind_param($updateAdmin, 'ssi', $defaultAdminNim, $defaultAdminPassword, $adminRow['id']);
                mysqli_stmt_execute($updateAdmin);
                mysqli_stmt_close($updateAdmin);
            }
        }
    }
}
