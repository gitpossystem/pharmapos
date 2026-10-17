<?php
// One-time page: creates the FIRST admin account.
// It refuses to work once any user exists. DELETE this file from the server afterwards.
require_once __DIR__ . '/auth.php';
start_secure_session();

$error = '';
$done = false;
$full_name = '';
$username = '';

try {
    $count = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Cannot reach the database or the tables are missing. Check config.php and import schema.sql.');
}

if ($count > 0) {
    http_response_code(403);
    exit('Setup already done. Delete setup_admin.php from the server.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (!csrf_valid()) {
        $error = 'Session expired. Please try again.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $username)) {
        $error = 'Username must be 3-60 characters: letters, numbers, dot, dash or underscore.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        db()->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, \'admin\')')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $full_name !== '' ? $full_name : null]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First admin - WebPharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-emerald-50 flex items-center justify-center p-4">
    <main class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-emerald-100 p-8">
        <h1 class="text-xl font-bold text-slate-800">Create the first admin</h1>
        <p class="text-sm text-slate-500 mb-6">This page works only once, while there are no users.</p>

        <?php if ($done): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Admin created. <strong>Delete setup_admin.php from the server now</strong>, then
                <a class="underline font-semibold" href="login.php">sign in</a>.
            </div>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="full_name">Full name</label>
                    <input id="full_name" name="full_name" type="text" value="<?= e($full_name) ?>"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="username">Username</label>
                    <input id="username" name="username" type="text" required value="<?= e($username) ?>" autocomplete="off"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Password (min 8 characters)</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="confirm">Confirm password</label>
                    <input id="confirm" name="confirm" type="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                </div>

                <button type="submit" class="w-full rounded-lg bg-emerald-600 py-2.5 font-semibold text-white hover:bg-emerald-700 transition">
                    Create admin
                </button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
