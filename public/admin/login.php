<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

if (is_authenticated()) {
    redirect('admin/index.php');
}

$error = flash('error');
$databaseError = null;
$username = '';

try {
    if (!admin_exists()) {
        redirect('admin/setup.php');
    }
} catch (Throwable $exception) {
    error_log((string) $exception);
    $databaseError = $exception;
}

if (is_post() && !$databaseError) {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attempt_login($username, $password)) {
        flash('success', 'Selamat datang kembali.');
        redirect('admin/index.php');
    }

    $blockedUntil = (int) ($_SESSION['login_blocked_until'] ?? 0);
    $error = $blockedUntil > time()
        ? 'Terlalu banyak percobaan. Tunggu sekitar satu menit, lalu coba lagi.'
        : 'Username atau password tidak cocok.';
}

$authTitle = 'Masuk admin';
require APP_ROOT . '/app/partials/auth-header.php';
?>
<section class="auth-card">
    <div class="auth-step">Panel pengelola</div>
    <h1>Selamat datang kembali.</h1>
    <p>Masuk untuk menambah lagu, memperbarui lirik, dan memilih video unggulan.</p>

    <?php if ($databaseError): ?>
        <div class="alert alert-error">
            <strong>Database belum terhubung.</strong>
            <p>Pastikan MySQL XAMPP aktif dan konfigurasi database sudah benar.</p>
            <?php if (config('debug')): ?><small><?= e($databaseError->getMessage()) ?></small><?php endif; ?>
        </div>
    <?php else: ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="stack-form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>
            <button class="button button-dark button-block" type="submit">Masuk ke dashboard</button>
        </form>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/partials/auth-footer.php'; ?>
