<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validasi kolom kosong
    if ($nama === '' || $nim === '' || $prodi === '' || $jabatan === '' || $password === '' || $confirmPassword === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Cek apakah NIM sudah terdaftar
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE nim = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $nim);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'NIM sudah terdaftar.';
        } else {
            mysqli_stmt_close($stmt);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Kolom disesuaikan: prodi dan jabatan dimasukkan ke database
            $insert = mysqli_prepare($conn, 'INSERT INTO users (nama, nim, prodi, jabatan, password, role) VALUES (?, ?, ?, ?, ?, ?)');
            $role = 'user';
            mysqli_stmt_bind_param($insert, 'ssssss', $nama, $nim, $prodi, $jabatan, $hashedPassword, $role);
            
            if (mysqli_stmt_execute($insert)) {
                $success = 'Pendaftaran berhasil. Silakan masuk.';
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
            }
            mysqli_stmt_close($insert);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelompok KKN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f6ebdc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-[#f6ebdc]">
    
    <div class="w-full max-w-2xl bg-[#fff7ee] border border-[#8c4335]/30 p-6 sm:p-10 rounded-[28px] shadow-[0_20px_50px_-30px_rgba(140,67,53,0.4)]">
        
        <div class="flex flex-col items-center gap-3 text-center mb-8">
            <img src="logo_kkn.png" alt="KKN Logo" class="w-20 h-20 rounded-full border border-[#8c4335]/20 object-cover">
            <div>
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.25em] text-[#8c4335]">KKN Lingkungan Kampus</p>
                <h1 class="mt-1.5 text-2xl sm:text-3xl font-bold text-[#5f312d]">Daftar Kelompok</h1>
                <p class="mt-1 text-sm text-[#6b3e36]">Buat akun untuk mengelola laporan keuangan KKN Anda.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="mb-5 rounded-2xl border border-[#b84133] bg-[#f5d7ca] p-3.5 text-xs sm:text-sm text-[#7e3329] text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-5 rounded-2xl border border-[#4a7c59] bg-[#e8f5e9] p-3.5 text-xs sm:text-sm text-[#2e4f37] text-center"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" class="space-y-4 sm:space-y-5">
            
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="nama">NAMA LENGKAP</label>
                    <input id="nama" name="nama" type="text" autocomplete="name" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" placeholder="Nama lengkap Anda">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="nim">NIM</label>
                    <input id="nim" name="nim" type="text" autocomplete="username" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>" placeholder="Nomor Induk Mahasiswa">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="prodi">PROGRAM STUDI</label>
                    <input id="prodi" name="prodi" type="text" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" value="<?php echo isset($_POST['prodi']) ? htmlspecialchars($_POST['prodi']) : ''; ?>" placeholder="Contoh: Teknik Informatika">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="jabatan">JABATAN</label>
                    <input id="jabatan" name="jabatan" type="text" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" value="<?php echo isset($_POST['jabatan']) ? htmlspecialchars($_POST['jabatan']) : ''; ?>" placeholder="Contoh: Bendahara / Anggota">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="password">KATA SANDI</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" placeholder="Minimal 6 karakter">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider" for="confirm_password">KONFIRMASI KATA SANDI</label>
                    <input id="confirm_password" name="confirm_password" type="password" required class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" placeholder="Ulangi kata sandi">
                </div>
            </div>

            <button type="submit" class="w-full rounded-[18px] sm:rounded-[22px] bg-[#8c4335] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#8c4335]/10 transition duration-150 active:scale-[0.99] hover:bg-[#7c3a2f] pt-3">Daftar Kelompok</button>
        </form>

        <div class="mt-6 sm:mt-8 border-t border-[#d9b8aa]/40 pt-5 text-center text-sm text-[#6b3e36]">
            <p>Sudah punya akun? <a href="login.php" class="font-semibold text-[#8c4335] hover:text-[#6b3e36] transition-colors underline underline-offset-2">Masuk Sekarang</a></p>
        </div>
    </div>
    
</body>
</html>