<?php
session_start();
require_once 'db.php';

$error = '';
$remember = $_COOKIE['remember_identity'] ?? '';

if (!empty($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin.php');
        exit;
    }
    header('Location: user.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember']);

    if ($identity === '' || $password === '') {
        $error = 'Silakan isi EMAIL/NIM dan password Anda.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, nama, password, role FROM users WHERE nim = ? OR email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 'ss', $identity, $identity);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = $result ? mysqli_fetch_assoc($result) : null;

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = strtolower($user['role']);

            if ($rememberMe) {
                setcookie('remember_identity', $identity, time() + 60 * 60 * 24 * 30, '/');
            } else {
                setcookie('remember_identity', '', time() - 3600, '/');
            }

            if ($_SESSION['role'] === 'admin') {
                header('Location: admin.php');
                exit;
            }
            header('Location: user.php');
            exit;
        }

        $error = 'EMAIL/NIM atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login KKN Lingkungan Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f6ebdc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-[#f6ebdc]">
    
    <div class="w-full max-w-md bg-[#fff7ee] border border-[#8c4335]/30 p-6 sm:p-10 rounded-[28px] shadow-[0_20px_50px_-30px_rgba(140,67,53,0.4)]">
        
        <div class="flex flex-col items-center text-center mb-8">
            <img src="logo_kkn.png" alt="Logo KKN" class="w-20 h-20 rounded-full border border-[#8c4335]/20 object-cover shrink-0 mb-4">
            <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-[0.25em] text-[#8c4335]">KKN Lingkungan Kampus</p>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-[#5f312d] leading-tight">Sistem Keuangan</h1>
            <p class="mt-1 text-sm text-[#6b3e36]">Masuk ke dashboard kelompok Anda</p>
        </div>
        
        <?php if ($error !== ''): ?>
            <div class="mb-5 rounded-2xl border border-[#b84133] bg-[#f5d7ca] p-3.5 text-xs sm:text-sm text-[#7e3329] text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="login.php" class="space-y-4 sm:space-y-5">
            <div>
                <label for="identity" class="block text-xs font-semibold text-[#5f312d] mb-1.5 tracking-wider">NIM</label>
                <input id="identity" name="identity" type="text" autocomplete="username" required value="<?php echo htmlspecialchars($remember); ?>" placeholder="Contoh: 2310000 " class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" />
            </div>
            
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="text-xs font-semibold text-[#5f312d] tracking-wider">KATA SANDI</label>
                    <a href="lupa-password.php" class="text-xs font-semibold text-[#8c4335] hover:text-[#6b3e36] transition-colors">Lupa sandi?</a>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="w-full rounded-[18px] sm:rounded-[22px] border border-[#cda79c] bg-[#fff8f1] px-4 py-2.5 sm:py-3 text-sm text-[#5f312d] placeholder:text-[#af8376]/70 focus:border-[#8c4335] focus:outline-none focus:ring-2 focus:ring-[#8c4335]/20" />
            </div>
            
            <div class="pt-1">
                <label class="inline-flex items-center gap-3 text-sm text-[#5f312d] cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#8c4335] bg-[#fff8f1] text-[#8c4335] focus:ring-[#8c4335] focus:ring-offset-0" />
                    <span>Ingat saya</span>
                </label>
            </div>
            
            <button type="submit" class="w-full rounded-[18px] sm:rounded-[22px] bg-[#8c4335] px-5 py-3 text-sm font-semibold text-white shadow-md shadow-[#8c4335]/10 transition duration-150 active:scale-[0.99] hover:bg-[#7c3a2f]">Masuk Sekarang</button>
        </form>
        
        <div class="mt-6 sm:mt-8 text-center text-sm text-[#6b3e36]">
            Belum memiliki akun? <a href="register.php" class="font-semibold text-[#8c4335] hover:text-[#6b3e36] transition-colors underline underline-offset-2">Daftar Kelompok</a>
        </div>
        
    </div>
</body>
</html>