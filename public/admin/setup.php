<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

if (is_authenticated()) {
    redirect('admin/index.php');
}

$errors = [];
$databaseError = null;
$setupBlocked = false;
$values = ['username' => 'admin'];

try {
    if (admin_exists()) {
        redirect('admin/login.php');
    }
    $setupBlocked = !is_local_request();
    if ($setupBlocked) {
        http_response_code(403);
    }
} catch (Throwable $exception) {
    error_log((string) $exception);
    $databaseError = $exception;
}

if (is_post() && !$databaseError && !$setupBlocked) {
    verify_csrf();
    $values['username'] = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (preg_match('/^[A-Za-z0-9._-]{3,50}$/', $values['username']) !== 1) {
        $errors['username'] = 'Gunakan 3–50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
    }
    if (strlen($password) < 10) {
        $errors['password'] = 'Password minimal 10 karakter.';
    }
    if ($password !== $passwordConfirmation) {
        $errors['password_confirmation'] = 'Konfirmasi password belum sama.';
    }

    if (!$errors) {
        $pdo = db();
        $lockAcquired = false;
        $newUserId = null;

        try {
            $lockAcquired = (int) $pdo
                ->query("SELECT GET_LOCK('frank_shines_initial_admin', 5)")
                ->fetchColumn() === 1;

            if (!$lockAcquired) {
                throw new RuntimeException('Setup admin sedang diproses oleh permintaan lain.');
            }

            if (admin_exists()) {
                $errors['form'] = 'Akun admin sudah dibuat. Silakan buka halaman login.';
            } else {
                $statement = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)');
                $statement->execute([
                    'username' => $values['username'],
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $newUserId = (int) $pdo->lastInsertId();
            }
        } catch (Throwable $exception) {
            error_log((string) $exception);
            $errors['form'] = 'Akun belum dapat dibuat. Periksa koneksi database lalu coba lagi.';
        } finally {
            if ($lockAcquired) {
                $pdo->query("SELECT RELEASE_LOCK('frank_shines_initial_admin')");
            }
        }

        if ($newUserId !== null) {
            login_user(['id' => $newUserId, 'username' => $values['username']]);
            flash('success', 'Akun admin berhasil dibuat. Selamat datang!');
            redirect('admin/index.php');
        }
    }
}

$authTitle = 'Buat akun admin';
require APP_ROOT . '/app/partials/auth-header.php';
?>
<section class="auth-card">
    <div class="auth-step">Setup pertama · 1 menit</div>
    <h1>Buat akun admin.</h1>
    <p>Akun ini hanya disimpan di database lokal Anda dan dipakai untuk mengelola lagu serta lirik.</p>

    <?php if ($databaseError): ?>
        <div class="alert alert-error">
            <strong>Database belum siap.</strong>
            <p>Jalankan MySQL di XAMPP lalu impor <code>database/schema.sql</code> melalui phpMyAdmin.</p>
            <?php if (config('debug')): ?><small><?= e($databaseError->getMessage()) ?></small><?php endif; ?>
        </div>
    <?php elseif ($setupBlocked): ?>
        <div class="alert alert-error">
            <strong>Setup admin dikunci.</strong>
            <p>Demi keamanan, akun admin pertama hanya dapat dibuat dari <code>localhost</code>. Buat akun di komputer ini sebelum memindahkan database ke hosting.</p>
        </div>
    <?php else: ?>
        <?php if (!empty($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="stack-form" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" value="<?= e($values['username']) ?>" autocomplete="username" required autofocus <?= !empty($errors['username']) ? 'aria-invalid="true" aria-describedby="username-error"' : '' ?>>
                <?php if (!empty($errors['username'])): ?><small class="field-error" id="username-error"><?= e($errors['username']) ?></small><?php endif; ?>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="10" required aria-describedby="password-help<?= !empty($errors['password']) ? ' password-error' : '' ?>" <?= !empty($errors['password']) ? 'aria-invalid="true"' : '' ?>>
                <small id="password-help">Minimal 10 karakter. Simpan password ini dengan aman.</small>
                <?php if (!empty($errors['password'])): ?><small class="field-error" id="password-error"><?= e($errors['password']) ?></small><?php endif; ?>
            </div>
            <div class="field">
                <label for="password-confirmation">Ulangi password</label>
                <input id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required <?= !empty($errors['password_confirmation']) ? 'aria-invalid="true" aria-describedby="password-confirmation-error"' : '' ?>>
                <?php if (!empty($errors['password_confirmation'])): ?><small class="field-error" id="password-confirmation-error"><?= e($errors['password_confirmation']) ?></small><?php endif; ?>
            </div>
            <button class="button button-dark button-block" type="submit">Buat akun & masuk</button>
        </form>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/partials/auth-footer.php'; ?>
