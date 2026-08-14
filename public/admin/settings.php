<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();

$values = get_site_settings();
$errors = [];
$success = flash('success');

if (is_post()) {
    verify_csrf();
    $allowedKeys = ['brand_name', 'hero_eyebrow', 'hero_tagline', 'intro_text', 'youtube_channel_url', 'instagram_url', 'contact_email'];
    foreach ($allowedKeys as $key) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if ($values['brand_name'] === '') {
        $errors['brand_name'] = 'Nama website wajib diisi.';
    }
    foreach (['youtube_channel_url', 'instagram_url'] as $key) {
        if ($values[$key] !== '' && !is_http_url($values[$key])) {
            $errors[$key] = 'Masukkan URL http:// atau https:// yang valid.';
        }
    }
    if ($values['contact_email'] !== '' && !filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['contact_email'] = 'Alamat email belum valid.';
    }

    if (!$errors) {
        save_site_settings(array_intersect_key($values, array_flip($allowedKeys)));
        flash('success', 'Pengaturan website berhasil disimpan.');
        redirect('admin/settings.php');
    }
}

$adminTitle = 'Pengaturan situs';
require APP_ROOT . '/app/partials/admin-header.php';
?>
<div class="admin-page-heading form-heading">
    <div><p class="eyebrow dark">Identitas website</p><h1>Atur suara dan nama Anda.</h1><p>Informasi ini muncul di beranda, navigasi, dan footer.</p></div>
    <a class="button button-outline" href="<?= e(url()) ?>" target="_blank">Lihat website ↗</a>
</div>

<?php if ($success): ?><div class="alert alert-success" data-alert><?= e($success) ?></div><?php endif; ?>

<form method="post" class="settings-form" novalidate>
    <?= csrf_field() ?>
    <section class="admin-panel form-section">
        <div class="panel-heading"><div><h2>Nama & pesan utama</h2><p>Gunakan kalimat singkat yang mencerminkan pelayanan atau karya Anda.</p></div></div>
        <div class="form-grid two">
            <div class="field span-two">
                <label for="brand-name">Nama website <b>*</b></label>
                <input id="brand-name" name="brand_name" value="<?= e($values['brand_name']) ?>" maxlength="120" required <?= !empty($errors['brand_name']) ? 'aria-invalid="true" aria-describedby="brand-name-error"' : '' ?>>
                <?php if (!empty($errors['brand_name'])): ?><small class="field-error" id="brand-name-error"><?= e($errors['brand_name']) ?></small><?php endif; ?>
            </div>
            <div class="field">
                <label for="hero-eyebrow">Teks kecil di hero</label>
                <input id="hero-eyebrow" name="hero_eyebrow" value="<?= e($values['hero_eyebrow']) ?>">
            </div>
            <div class="field">
                <label for="hero-tagline">Tagline</label>
                <input id="hero-tagline" name="hero_tagline" value="<?= e($values['hero_tagline']) ?>">
            </div>
            <div class="field span-two">
                <label for="intro-text">Perkenalan singkat</label>
                <textarea id="intro-text" name="intro_text" rows="5"><?= e($values['intro_text']) ?></textarea>
            </div>
        </div>
    </section>

    <section class="admin-panel form-section">
        <div class="panel-heading"><div><h2>Tautan & kontak</h2><p>Kosongkan tautan yang belum ingin ditampilkan.</p></div></div>
        <div class="form-grid two">
            <div class="field">
                <label for="youtube-channel">URL kanal YouTube</label>
                <input id="youtube-channel" name="youtube_channel_url" type="url" value="<?= e($values['youtube_channel_url']) ?>" maxlength="500" placeholder="https://youtube.com/@…" <?= !empty($errors['youtube_channel_url']) ? 'aria-invalid="true" aria-describedby="youtube-channel-error"' : '' ?>>
                <?php if (!empty($errors['youtube_channel_url'])): ?><small class="field-error" id="youtube-channel-error"><?= e($errors['youtube_channel_url']) ?></small><?php endif; ?>
            </div>
            <div class="field">
                <label for="instagram-url">URL Instagram</label>
                <input id="instagram-url" name="instagram_url" type="url" value="<?= e($values['instagram_url']) ?>" maxlength="500" placeholder="https://instagram.com/…" <?= !empty($errors['instagram_url']) ? 'aria-invalid="true" aria-describedby="instagram-url-error"' : '' ?>>
                <?php if (!empty($errors['instagram_url'])): ?><small class="field-error" id="instagram-url-error"><?= e($errors['instagram_url']) ?></small><?php endif; ?>
            </div>
            <div class="field span-two">
                <label for="contact-email">Email kontak</label>
                <input id="contact-email" name="contact_email" type="email" value="<?= e($values['contact_email']) ?>" maxlength="254" placeholder="nama@email.com" <?= !empty($errors['contact_email']) ? 'aria-invalid="true" aria-describedby="contact-email-error"' : '' ?>>
                <?php if (!empty($errors['contact_email'])): ?><small class="field-error" id="contact-email-error"><?= e($errors['contact_email']) ?></small><?php endif; ?>
            </div>
        </div>
    </section>
    <div class="settings-actions"><button class="button button-dark" type="submit">Simpan pengaturan</button></div>
</form>
<?php require APP_ROOT . '/app/partials/admin-footer.php'; ?>
