<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$adminName = $_SESSION['nama'] ?? 'Admin';

function format_idr($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

$summary = [
    'income' => 0,
    'expense' => 0,
    'balance' => 0,
];

$stmt = mysqli_prepare($conn, "SELECT
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense
    FROM transactions");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $summary['income'] = (float)$row['total_income'];
    $summary['expense'] = (float)$row['total_expense'];
    $summary['balance'] = $summary['income'] - $summary['expense'];
}
mysqli_stmt_close($stmt);

$transactions = [];
$stmt = mysqli_prepare($conn, 'SELECT t.date, t.description, t.category, t.type, t.amount, u.nama, u.nim FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.date DESC, t.id DESC LIMIT 6');
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $transactions[] = $row;
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at 0% 0%, #fdf8f5 0%, #f4e7e4 50%, #ebdad6 100%);
            background-attachment: fixed;
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
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl bg-[#8c4335]/10 text-[#8c4335] border border-[#8c4335]/5 shadow-[0_4px_12px_rgba(140,67,53,0.04)] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
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
                <a href="profil.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl text-slate-600 hover:bg-white/50 hover:text-slate-900 transition-all">
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
        <a href="admin.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl bg-[#8c4335]/10 text-[#8c4335] font-semibold text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
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
        <a href="profil.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl text-slate-500 font-medium text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
            <span>Profil</span>
        </a>
    </nav>

    <div class="flex-1 flex flex-col min-w-0 md:pl-4">
        <header class="bg-white/20 backdrop-blur-xl border-b border-white/30 px-6 py-4 md:px-8 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h2 class="text-[10px] font-bold text-[#8c4335]/70 uppercase tracking-widest">Dashboard Admin</h2>
                <h1 class="text-lg md:text-2xl font-bold text-slate-800 tracking-tight mt-0.5">Halo, <?php echo htmlspecialchars($adminName); ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-800 border border-emerald-500/10">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Admin Aktif
                </span>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto max-w-7xl w-full mx-auto">
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-3">
                <div class="bg-white/50 backdrop-blur-xl border border-white/60 p-6 rounded-[24px] shadow-[0_10px_30px_rgba(140,67,53,0.03)] flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Saldo Saat Ini</p>
                        <p class="mt-2 text-2xl md:text-3xl font-bold text-slate-800 tracking-tight"><?php echo format_idr($summary['balance']); ?></p>
                    </div>
                    <p class="mt-4 text-[11px] text-slate-500 font-medium">Seluruh saldo masuk dan keluar sistem.</p>
                </div>
                <div class="bg-white/50 backdrop-blur-xl border border-white/60 p-6 rounded-[24px] shadow-[0_10px_30px_rgba(140,67,53,0.03)] flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
                        <p class="mt-2 text-2xl md:text-3xl font-bold text-emerald-600 tracking-tight"><?php echo format_idr($summary['income']); ?></p>
                    </div>
                    <p class="mt-4 text-[11px] text-slate-500 font-medium">Jumlah total dana masuk seluruh pengguna.</p>
                </div>
                <div class="bg-white/50 backdrop-blur-xl border border-white/60 p-6 rounded-[24px] shadow-[0_10px_30px_rgba(140,67,53,0.03)] flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                        <p class="mt-2 text-2xl md:text-3xl font-bold text-rose-600 tracking-tight"><?php echo format_idr($summary['expense']); ?></p>
                    </div>
                    <p class="mt-4 text-[11px] text-slate-500 font-medium">Total biaya tercatat seluruh pengguna.</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[28px] shadow-[0_15px_35px_rgba(140,67,53,0.03)] p-5 md:p-6 lg:col-span-2">
                    <div class="pb-4 border-b border-black/5">
                        <h3 class="text-base md:text-lg font-bold text-slate-800 tracking-tight">Catatan Transaksi Terbaru</h3>
                        <p class="text-xs text-slate-400 mt-0.5">6 aktivitas finansial terbaru seluruh pengguna.</p>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-sm text-slate-600 border-collapse">
                            <thead>
                                <tr class="text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-black/5">
                                    <th class="pb-3 font-semibold">Tanggal</th>
                                    <th class="pb-3 font-semibold">Deskripsi</th>
                                    <th class="pb-3 font-semibold">Kategori</th>
                                    <th class="pb-3 font-semibold text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.03]">
                                <?php if (count($transactions) === 0): ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400 text-xs font-medium">Belum ada aktivitas transaksi yang tercatat.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $trx): ?>
                                        <tr class="hover:bg-white/40 transition-colors">
                                            <td class="py-3.5 pr-2 font-medium text-slate-700 text-xs whitespace-nowrap"><?php echo date('d M Y', strtotime($trx['date'])); ?></td>
                                            <td class="py-3.5 px-2 max-w-[180px] truncate font-medium text-xs text-slate-800">
                                                <?php echo htmlspecialchars($trx['description']); ?>
                                                <span class="block text-[10px] text-slate-400 mt-1"><?php echo htmlspecialchars($trx['nama'] . ' / ' . $trx['nim']); ?></span>
                                            </td>
                                            <td class="py-3.5 px-2">
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] rounded-full bg-white/80 border border-black/5 text-slate-600 font-semibold shadow-sm"><?php echo htmlspecialchars($trx['category']); ?></span>
                                            </td>
                                            <td class="py-3.5 pl-2 text-right font-bold text-xs whitespace-nowrap <?php echo $trx['type'] === 'income' ? 'text-emerald-600' : 'text-rose-600'; ?>">
                                                <?php echo $trx['type'] === 'income' ? '+ ' . format_idr($trx['amount']) : '- ' . format_idr($trx['amount']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[24px] p-5 shadow-[0_10px_30px_rgba(140,67,53,0.02)]">
                        <h4 class="text-[11px] font-bold text-[#8c4335] uppercase tracking-wider mb-2">Pemberitahuan Sistem</h4>
                        <p class="text-xs text-slate-500 leading-relaxed font-medium">
                            Pastikan semua bukti nota fisik dicatat dengan benar di halaman <strong>Pemasukan</strong> dan <strong>Pengeluaran</strong> untuk audit kas yang transparan.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-[#5f312d] to-[#8c4335] text-white rounded-[24px] p-5 shadow-[0_15px_35px_rgba(140,67,53,0.15)] relative overflow-hidden">
                        <h4 class="text-[11px] font-bold uppercase tracking-wider mb-2 text-white/90">Kontrol Admin</h4>
                        <p class="text-xs text-white/80 leading-relaxed mb-4 font-light">Gunakan modul ini untuk mencatat transaksi yang mewakili seluruh anggota kelompok.</p>
                        <a href="pemasukan.php" class="inline-block text-xs font-semibold text-[#5f312d] bg-white hover:bg-white/90 px-4 py-2 rounded-xl shadow-sm transition-all active:scale-95">Buka Pemasukan</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
