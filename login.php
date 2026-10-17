<?php
require_once __DIR__ . '/auth.php';
start_secure_session();

if (current_user() !== null) {
    header('Location: dashboard.php');
    exit;
}

const MAX_ATTEMPTS = 5;      // failed tries before a short lock
const LOCK_SECONDS = 300;    // 5 minutes

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $locked = ($_SESSION['lock_until'] ?? 0) > time();

    if (!csrf_valid()) {
        $error = 'Session expired. Please try again.';
    } elseif ($locked) {
        $wait = ceil((($_SESSION['lock_until']) - time()) / 60);
        $error = "Too many failed attempts. Try again in about {$wait} minute(s).";
    } elseif ($username === '' || $password === '') {
        $error = 'Enter your username and password.';
    } else {
        try {
            $stmt = db()->prepare('SELECT id, username, password_hash, full_name, role FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $row = $stmt->fetch();

            // Always run a hash check so timing doesn't reveal whether the user exists.
            $hash = $row['password_hash'] ?? password_hash('dummy-password', PASSWORD_DEFAULT);
            $ok = password_verify($password, $hash) && $row;

            if ($ok) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'        => (int)$row['id'],
                    'username'  => $row['username'],
                    'full_name' => $row['full_name'],
                    'role'      => $row['role'],
                ];
                unset($_SESSION['attempts'], $_SESSION['lock_until']);

                if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                        ->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
                }
                header('Location: dashboard.php');
                exit;
            }

            $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
            if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
                $_SESSION['lock_until'] = time() + LOCK_SECONDS;
                $_SESSION['attempts'] = 0;
            }
            $error = 'Wrong username or password.';
        } catch (PDOException $ex) {
            $error = 'Cannot reach the database. Check config.php.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - WebPharmacy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 flex items-center justify-center p-4">

    <main class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl border border-emerald-100 p-8">

            <div class="flex flex-col items-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-600/30">
                    <svg viewBox="0 0 24 24" class="w-9 h-9 text-white" fill="currentColor" aria-hidden="true">
                        <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z"/>
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-800">WebPharmacy</h1>
                <p class="text-sm text-slate-500">Sign in to the POS system</p>
            </div>

            <?php if ($error !== ''): ?>
                <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" class="space-y-5" autocomplete="on">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input id="username" name="username" type="text" required autofocus
                           value="<?= e($username) ?>" autocomplete="username"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 pr-16 text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30 outline-none">
                        <button type="button" id="togglePw"
                                class="absolute inset-y-0 right-0 px-4 text-sm font-medium text-emerald-700 hover:text-emerald-900">
                            Show
                        </button>
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 py-2.5 font-semibold text-white shadow hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition">
                    Sign in
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">&copy; <?= date('Y') ?> WebPharmacy</p>
    </main>

    <script>
        const pw = document.getElementById('password');
        const btn = document.getElementById('togglePw');
        btn.addEventListener('click', () => {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Hide' : 'Show';
        });
    </script>
</body>
</html>
