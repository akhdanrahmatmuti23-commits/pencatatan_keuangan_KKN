<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f6ebdc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[#f6ebdc]">
    <div class="w-full max-w-lg rounded-[32px] bg-[#fff7ee] border border-[#8c4335] p-10 shadow-[0_30px_60px_-30px_rgba(140,67,53,0.75)]">
        <div class="text-center mb-8">
            <img src="logo_kkn.png" alt="Logo KKN" class="mx-auto w-20 h-20 rounded-full border border-[#8c4335]/20 object-cover">
            <h1 class="mt-5 text-3xl font-semibold text-[#5f312d]">Lupa Kata Sandi</h1>
            <p class="mt-3 text-[#6b3e36]">Silakan hubungi admin KKN untuk mereset kata sandi.</p>
        </div>
        <div class="rounded-[28px] bg-[#f3e5d6] border border-[#9c4f43] p-6 text-[#6b3e36]">
            <p class="mb-4">Reset otomatis belum tersedia. Untuk keamanan, minta admin sistem KKN membuat ulang password Anda.</p>
            <p class="text-sm text-[#7d463d]">Jika Anda admin, Anda bisa mengubah password langsung pada tabel <code>users</code> di database MySQL.</p>
        </div>
        <div class="mt-8 text-center text-sm text-[#6b3e36]">
            <a href="login.php" class="font-semibold text-[#8c4335] hover:text-[#6b3e36]">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
