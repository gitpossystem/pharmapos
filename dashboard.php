<?php
require_once __DIR__ . '/auth.php';
$user = require_login();   // sends visitors to login.php if not signed in
$name = $user['full_name'] ?: $user['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - WebPharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50">

    <header class="bg-emerald-700 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg viewBox="0 0 24 24" class="w-7 h-7" fill="currentColor" aria-hidden="true"><path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z"/></svg>
                <span class="text-lg font-bold">WebPharmacy</span>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span><?= e($name) ?> <span class="ml-1 rounded bg-emerald-900/40 px-2 py-0.5 text-xs uppercase"><?= e($user['role']) ?></span></span>
                <a href="logout.php" class="rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20 transition">Sign out</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-slate-800">Welcome, <?= e($name) ?></h1>
        <p class="text-slate-500 mb-8">Login works. The modules below are next.</p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach (['New sale (POS)', 'Medicines & stock', 'Customers & suppliers', 'Reports'] as $card): ?>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="font-semibold text-slate-800"><?= e($card) ?></div>
                    <div class="mt-1 text-xs text-slate-400">Coming next</div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
