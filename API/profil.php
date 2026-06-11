<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') === 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$userId = $_SESSION['user_id'];

// 1. AMBIL DATA LENGKAP USER (TERMASUK PRODI & JABATAN)
$stmt = mysqli_prepare($conn, 'SELECT nama, nim, prodi, jabatan FROM users WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

// Jika data tidak ditemukan, amankan sesi
$userName = $user['nama'] ?? $_SESSION['nama'];
$userNim = $user['nim'] ?? '-';
$userProdi = $user['prodi'] ?? 'Belum Diatur';
$userJabatan = $user['jabatan'] ?? 'Anggota';

function format_idr($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

// 2. AMBIL DATA TOTAL UANG KKN KELUAR (EXPENSE)
$totalExpense = 0;
$stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE user_id = ? AND type = 'expense'");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $totalExpense = (float)$row['total'];
}
mysqli_stmt_close($stmt);

// Ambil inisial nama untuk avatar kemewahan iOS
$initials = strtoupper(substr($userName, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Kelompok KKN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: radial-gradient(circle at 0% 0%, #fdf8f5 0%, #f4e7e4 50%, #ebdad6 100%);
            background-attachment: fixed;
            font-family: 'Inter', sans-serif; 
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 flex flex-col md:flex-row pb-24 md:pb-0 antialiased">

    <aside class="hidden md:flex fixed top-4 left-4 bottom-4 w-64 bg-white/40 backdrop-blur-2xl border border-white/40 p-6 flex-col justify-between rounded-[32px] shadow-[0_20px_50px_rgba(140,67,53,0.08)] z-50">
        <div>
            <div class="flex items-center gap-3 mb-8 pb-4 border-b border-black/5">
                <div class="p-1 rounded-full bg-white/60 border border-white/80 shadow-sm">
                    <img src="logo_kkn.png" alt="Logo KKN" class="h-9 w-9 rounded-full object-cover">
                </div>
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-[#5f312d]">Finance KKN</h1>
                    <span class="text-[10px] text-[#8c4335]/70 font-semibold uppercase tracking-wider">Spatial Report</span>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="user.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl text-slate-600 hover:bg-white/50 hover:text-slate-900 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 00-1 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
                    Dashboard
                </a>
                <a href="pemasukan.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl text-slate-600 hover:bg-white/50 hover:text-slate-900 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 18zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" /></svg>
                    Pemasukan
                </a>
                <a href="pengeluaran.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl text-slate-600 hover:bg-white/50 hover:text-slate-900 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 18zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)" /></svg>
                    Pengeluaran
                </a>
                <a href="profil.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl bg-[#8c4335]/10 text-[#8c4335] border border-[#8c4335]/5 shadow-[0_4px_12px_rgba(140,67,53,0.04)] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                    Profil
                </a>
            </nav>
        </div>

        <div class="border-t border-black/5 pt-4">
            <a href="logout.php" class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold rounded-2xl bg-rose-500/10 text-rose-700 border border-rose-500/10 hover:bg-rose-500/20 transition-all">
                Keluar Sistem
            </a>
        </div>
    </aside>

    <nav class="md:hidden fixed bottom-4 left-4 right-4 z-50 bg-white/60 backdrop-blur-2xl border border-white/40 shadow-[0_15px_40px_rgba(140,67,53,0.12)] px-2 py-2 flex justify-around items-center rounded-[24px]">
        <a href="user.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl text-slate-500 font-medium text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 00-1 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
            <span>Dashboard</span>
        </a>
        <a href="pemasukan.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl text-slate-500 font-medium text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 18zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" /></svg>
            <span>Pemasukan</span>
        </a>
        <a href="pengeluaran.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl text-slate-500 font-medium text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 18zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)" /></svg>
            <span>Pengeluaran</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl bg-[#8c4335]/10 text-[#8c4335] font-semibold text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
            <span>Profil</span>
        </a>
    </nav>

    <div class="flex-1 flex flex-col min-w-0 md:pl-4">
        
        <header class="bg-white/20 backdrop-blur-xl border-b border-white/30 px-6 py-4 md:px-8 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h2 class="text-[10px] font-bold text-[#8c4335]/70 uppercase tracking-widest">Pengaturan Akun</h2>
                <h1 class="text-lg md:text-2xl font-bold text-slate-800 tracking-tight mt-0.5">Profil Kelompok</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="logout.php" class="md:hidden p-2 rounded-xl bg-rose-500/10 text-rose-700 border border-rose-500/10" title="Keluar Sistem">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto max-w-3xl w-full mx-auto">
            
            <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[32px] p-6 md:p-8 shadow-sm flex flex-col items-center text-center relative overflow-hidden">
                
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-[#5f312d] to-[#8c4335] border-4 border-white shadow-md flex items-center justify-center text-white text-3xl font-bold tracking-wider mb-4 select-none">
                    <?php echo $initials; ?>
                </div>

                <h2 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight"><?php echo htmlspecialchars($userName); ?></h2>
                <p class="text-xs font-semibold px-3 py-1 rounded-full bg-[#8c4335]/10 text-[#8c4335] border border-[#8c4335]/5 mt-1.5 uppercase tracking-wide">
                    <?php echo htmlspecialchars($userJabatan); ?>
                </p>

                <div class="w-full border-t border-black/5 my-6"></div>

                <div class="w-full grid gap-4 grid-cols-1 sm:grid-cols-2 text-left">
                    <div class="bg-white/40 p-3.5 rounded-2xl border border-black/[0.01]">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Induk Mahasiswa (NIM)</span>
                        <span class="text-sm font-semibold text-slate-800 mt-0.5 block"><?php echo htmlspecialchars($userNim); ?></span>
                    </div>

                    <div class="bg-white/40 p-3.5 rounded-2xl border border-black/[0.01]">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Program Studi</span>
                        <span class="text-sm font-semibold text-slate-800 mt-0.5 block"><?php echo htmlspecialchars($userProdi); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[28px] p-6 shadow-sm flex items-center gap-4 relative overflow-hidden">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center border border-rose-500/10 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l-4 4m0 0l-4-4m4 4V3" /></svg>
                </div>
                
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Uang KKN Keluar</span>
                    <span class="text-2xl font-black text-rose-600 tracking-tight block mt-0.5">
                        <?php echo format_idr($totalExpense); ?>
                    </span>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Akumulasi seluruh pembiayaan program kerja kelompok.</p>
                </div>

                <div class="absolute -right-6 -bottom-6 text-rose-500/5 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-28 w-28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <div class="text-center px-4">
                <p class="text-[10px] text-slate-400/80 font-medium leading-relaxed">
                    ID Akun: #<?php echo hash('crc32b', $userId); ?> &bull; Data profil dienkripsi di dalam pangkalan data lokal server LPPM Kampus.
                </p>
            </div>

        </main>
    </div>

</body>
</html>