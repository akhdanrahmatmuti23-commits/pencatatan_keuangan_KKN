# KKN Finance Local Server

## Setup Database

1. Buka XAMPP / MySQL.
2. Jalankan `setup.sql` di phpMyAdmin atau MySQL CLI.
3. Database `kkn_finance` dan tabel `users` serta `transactions` akan dibuat.
4. Admin default:
   - NIM: `admin`
   - Email: `admin@kkn-keuangan.local`
   - Password: `admin123`
5. Contoh akun user:
   - NIM: `2310001`
   - Email: `user@kkn-keuangan.local`
   - Password: `user1234`

## File penting

- `db.php` - koneksi database umum.
- `login.php` - halaman login utama.
- `register.php` - halaman pendaftaran akun baru.
- `logout.php` - logout session.
- `lupa-password.php` - halaman bantuan lupa kata sandi.

## Akses dari perangkat lain

Agar perangkat lain bisa login ke server lokal:

1. Pastikan Apache dan MySQL berjalan.
2. Gunakan alamat IP komputer host, misalnya `http://192.168.x.x/kkn-finance/login.php`.
3. Pastikan firewall Windows mengizinkan port 80.

> Catatan: jika perangkat lain tidak berada di jaringan yang sama, gunakan layanan tunneling seperti ngrok atau port forwarding.
