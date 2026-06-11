<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$userId = $_SESSION['user_id'];

function format_idr($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

$message = '';
$showForm = false;
$users = [];

if ($isAdmin) {
    $stmt = mysqli_prepare($conn, 'SELECT id, nama, nim FROM users ORDER BY nama, nim');
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    if (($_POST['action'] ?? '') === 'add_income') {
        $selectedUserId = intval($_POST['user_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $date = trim($_POST['date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $notes = $notes === '' ? null : $notes;

        if ($selectedUserId > 0 && $description !== '' && $category !== '' && $amount > 0 && strtotime($date) !== false) {
            $stmt = mysqli_prepare($conn, 'INSERT INTO transactions (user_id, date, description, category, type, amount, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $type = 'income';
                mysqli_stmt_bind_param($stmt, 'issssds', $selectedUserId, $date, $description, $category, $type, $amount, $notes);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = 'Pemasukan berhasil disimpan.';
                } else {
                    $message = 'Terjadi kesalahan saat menyimpan pemasukan.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Gagal mempersiapkan penyimpanan data.';
            }
        } else {
            $message = 'Mohon isi semua kolom dengan benar.';
        }
        $showForm = true;
    } elseif (($_POST['action'] ?? '') === 'delete_income') {
        $deleteId = intval($_POST['transaction_id'] ?? 0);
        if ($deleteId > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM transactions WHERE id = ? AND type = 'income'");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $deleteId);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = 'Pemasukan berhasil dihapus.';
                } else {
                    $message = 'Data pemasukan tidak ditemukan atau gagal dihapus.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = 'Gagal mempersiapkan penghapusan data.';
            }
        }
    }
}

$totalIncome = 0;
if ($isAdmin) {
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE type = 'income'");
} else {
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions WHERE user_id = ? AND type = 'income'");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $totalIncome = (float)$row['total'];
}
mysqli_stmt_close($stmt);

$incomes = [];
if ($isAdmin) {
    $stmt = mysqli_prepare($conn, "SELECT t.id, t.date, t.description, t.category, t.amount FROM transactions t WHERE t.type = 'income' ORDER BY t.date DESC, t.id DESC LIMIT 10");
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, date, description, category, amount FROM transactions WHERE user_id = ? AND type = 'income' ORDER BY date DESC, id DESC LIMIT 10");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $incomes[] = $row;
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemasukan Kas KKN</title>
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
                <a href="pemasukan.php" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-2xl bg-[#8c4335]/10 text-[#8c4335] border border-[#8c4335]/5 shadow-[0_4px_12px_rgba(140,67,53,0.04)] transition-all">
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
        <a href="user.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl text-slate-500 font-medium text-[10px] tracking-tight transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 00-1 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>
            <span>Dashboard</span>
        </a>
        <a href="pemasukan.php" class="flex flex-col items-center gap-1 py-1.5 px-4 rounded-xl bg-[#8c4335]/10 text-[#8c4335] font-semibold text-[10px] tracking-tight transition-all">
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
                <h2 class="text-[10px] font-bold text-[#8c4335]/70 uppercase tracking-widest">Modul Keuangan</h2>
                <h1 class="text-lg md:text-2xl font-bold text-slate-800 tracking-tight mt-0.5">Pemasukan Kas</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="logout.php" class="md:hidden p-2 rounded-xl bg-rose-500/10 text-rose-700 border border-rose-500/10" title="Keluar Sistem">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 space-y-6 overflow-y-auto max-w-7xl w-full mx-auto">
            <div class="bg-gradient-to-r from-emerald-600/90 to-teal-700/90 text-white border border-white/20 p-6 rounded-[24px] shadow-[0_15px_30px_rgba(16,185,129,0.1)] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-xs font-semibold text-emerald-100/80 uppercase tracking-wider">Total Pemasukan Kelompok</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight"><?php echo format_idr($totalIncome); ?></p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/10 border border-white/10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 18zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" /></svg>
                    Dana Terhimpun
                </span>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[28px] p-5 md:p-6 shadow-sm flex flex-col justify-between">
                    <div class="pb-3 border-b border-black/5 mb-4">
                        <h3 class="text-base font-bold text-slate-800 tracking-tight">Detail Pemasukan</h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Rekap penerimaan dana yang tercatat.</p>
                    </div>

                    <div class="space-y-3">
                        <?php if (count($incomes) === 0): ?>
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center border border-amber-500/15 mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-700">Belum ada pemasukan</h4>
                                <p class="text-xs text-slate-400 max-w-[180px] mt-1 leading-relaxed">Catatan kas masuk belum tersedia.</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Aktivitas Terakhir</span>
                                <span class="inline-block mt-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-500/10 text-emerald-700 border border-emerald-500/10">Transaksi Pemasukan</span>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Deskripsi Transaksi</span>
                                    <p class="mt-1 text-xs text-slate-700 font-semibold bg-white/40 p-3 rounded-xl border border-black/[0.02]">"<?php echo htmlspecialchars($incomes[0]['description']); ?>"</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kategori</span>
                                        <span class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($incomes[0]['category']); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nominal</span>
                                        <span class="text-xs font-bold text-emerald-600"><?php echo format_idr($incomes[0]['amount']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isAdmin): ?>
                        <button type="button" onclick="toggleForm('incomeForm')" class="mt-6 w-full inline-flex justify-center items-center gap-2 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-700 px-4 py-3 text-sm font-semibold hover:bg-emerald-500/15 transition-all">+ Tambah Pemasukan</button>
                    <?php endif; ?>
                </div>

                <div class="bg-white/50 backdrop-blur-xl border border-white/60 rounded-[28px] p-5 md:p-6 lg:col-span-2 shadow-sm">
                    <div class="pb-3 border-b border-black/5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800 tracking-tight">Riwayat Log Pemasukan</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Daftar penerimaan dana kelompok terhimpun</p>
                        </div>
                        <?php if ($isAdmin): ?>
                            <button type="button" onclick="toggleForm('incomeForm')" class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition-all">+ Tambah Baru</button>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <div id="incomeForm" class="mt-4 <?php echo $showForm ? '' : 'hidden'; ?>">
                        <form method="post" class="space-y-4 bg-slate-50 border border-slate-200 rounded-[28px] p-5">
                            <input type="hidden" name="action" value="add_income">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-600">Nama / NIM</label>
                                    <select name="user_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" required>
                                        <option value="">Pilih pengguna</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['nama'] . ' / ' . $user['nim']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-600">Kategori</label>
                                    <select name="category" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" required>
                                        <option value="">Pilih kategori</option>
                                        <option value="Uang Kas">Uang Kas</option>
                                        <option value="Sponsor">Sponsor</option>
                                        <option value="Donasi">Donasi</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-600">Jumlah</label>
                                    <input type="number" name="amount" step="0.01" min="0" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-600">Tanggal</label>
                                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" required>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-600">Catatan</label>
                                    <input type="text" name="notes" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" placeholder="Catatan tambahan...">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold text-slate-600">Keterangan Transaksi</label>
                                <input type="text" name="description" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" placeholder="Nama transaksi atau sumber dana" required>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#5f312d] px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#8c4335] transition-all">Simpan Pemasukan</button>
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-sm text-slate-600 border-collapse">
                            <thead>
                                <tr class="text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-black/5">
                                    <th class="pb-3 font-semibold">Tanggal</th>
                                    <th class="pb-3 font-semibold">Deskripsi</th>
                                    <th class="pb-3 font-semibold">Kategori</th>
                                    <th class="pb-3 font-semibold text-right">Jumlah</th>
                                    <?php if ($isAdmin): ?>
                                        <th class="pb-3 font-semibold text-right">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.03]">
                                <?php if (count($incomes) === 0): ?>
                                    <tr>
                                        <td colspan="<?php echo $isAdmin ? 5 : 4; ?>" class="py-8 text-center text-slate-400 text-xs font-medium">Belum ada aktivitas catatan dana masuk yang terdaftar.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($incomes as $inc): ?>
                                        <tr class="hover:bg-white/40 transition-colors">
                                            <td class="py-3.5 pr-2 font-medium text-slate-700 text-xs whitespace-nowrap"><?php echo date('d M Y', strtotime($inc['date'])); ?></td>
                                            <td class="py-3.5 px-2 max-w-[150px] truncate font-medium text-xs text-slate-800"><?php echo htmlspecialchars($inc['description']); ?></td>
                                            <td class="py-3.5 px-2">
                                                <span class="inline-block px-2.5 py-0.5 text-[10px] rounded-full bg-white/80 border border-black/5 text-slate-600 font-semibold shadow-sm"><?php echo htmlspecialchars($inc['category']); ?></span>
                                            </td>
                                            <td class="py-3.5 pl-2 text-right font-bold text-xs text-emerald-600 whitespace-nowrap">+ <?php echo format_idr($inc['amount']); ?></td>
                                            <?php if ($isAdmin): ?>
                                                <td class="py-3.5 pl-2 text-right">
                                                    <form method="post" class="inline-block" onsubmit="return confirm('Hapus pemasukan ini?');">
                                                        <input type="hidden" name="action" value="delete_income">
                                                        <input type="hidden" name="transaction_id" value="<?php echo $inc['id']; ?>">
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-semibold text-red-700 hover:bg-red-100 transition-all">Hapus</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        function toggleForm(id) {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('hidden');
        }
    </script>
</body>
</html>
