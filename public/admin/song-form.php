<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$song = $id ? get_song_by_id((int) $id) : null;

if ($id && !$song) {
    flash('error', 'Lagu yang ingin diedit tidak ditemukan.');
    redirect('admin/index.php');
}

$isEdit = $song !== null;
$values = $song ?: [
    'title' => '',
    'slug' => '',
    'artist' => '',
    'songwriter' => '',
    'release_year' => date('Y'),
    'album' => '',
    'production' => '',
    'youtube_id' => '',
    'cover_url' => '',
    'short_description' => '',
    'lyrics' => '',
    'is_featured' => 0,
    'status' => 'draft',
];
$errors = [];

if (is_post()) {
    verify_csrf();

    $values = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'artist' => trim((string) ($_POST['artist'] ?? '')),
        'songwriter' => trim((string) ($_POST['songwriter'] ?? '')),
        'release_year' => trim((string) ($_POST['release_year'] ?? '')),
        'album' => trim((string) ($_POST['album'] ?? '')),
        'production' => trim((string) ($_POST['production'] ?? '')),
        'youtube_id' => trim((string) ($_POST['youtube_url'] ?? '')),
        'cover_url' => trim((string) ($_POST['cover_url'] ?? '')),
        'short_description' => trim((string) ($_POST['short_description'] ?? '')),
        'lyrics' => trim((string) ($_POST['lyrics'] ?? '')),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'status' => in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft',
    ];

    if ($values['title'] === '') {
        $errors['title'] = 'Judul lagu wajib diisi.';
    }
    if ($values['artist'] === '') {
        $errors['artist'] = 'Nama penyanyi atau artis wajib diisi.';
    }

    $lengthLimits = [
        'title' => [150, 'Judul lagu maksimal 150 karakter.'],
        'slug' => [180, 'Alamat halaman maksimal 180 karakter.'],
        'artist' => [120, 'Nama penyanyi maksimal 120 karakter.'],
        'songwriter' => [120, 'Nama songwriter maksimal 120 karakter.'],
        'album' => [150, 'Nama album atau pelayanan maksimal 150 karakter.'],
        'production' => [180, 'Kredit produksi maksimal 180 karakter.'],
        'cover_url' => [500, 'URL cover maksimal 500 karakter.'],
    ];
    foreach ($lengthLimits as $field => [$limit, $message]) {
        if (mb_strlen((string) $values[$field]) > $limit) {
            $errors[$field] = $message;
        }
    }

    $youtubeId = youtube_id_from_input($values['youtube_id']);
    if ($values['youtube_id'] !== '' && !$youtubeId) {
        $errors['youtube_url'] = 'Masukkan URL YouTube yang valid atau ID video 11 karakter.';
    }

    $year = $values['release_year'] !== '' ? filter_var($values['release_year'], FILTER_VALIDATE_INT) : null;
    if ($year !== null && ($year === false || $year < 1900 || $year > (int) date('Y') + 1)) {
        $errors['release_year'] = 'Tahun rilis harus antara 1900 dan ' . ((int) date('Y') + 1) . '.';
    }

    if ($values['cover_url'] !== '') {
        if (!is_http_url($values['cover_url'])) {
            $errors['cover_url'] = 'URL cover harus berupa alamat http:// atau https:// yang valid.';
        }
    }

    if (mb_strlen($values['short_description']) > 600) {
        $errors['short_description'] = 'Cerita singkat maksimal 600 karakter.';
    }
    if ($values['is_featured'] && $values['status'] !== 'published') {
        $errors['is_featured'] = 'Lagu unggulan harus berstatus Tayang.';
    }

    if (!$errors) {
        $slug = unique_song_slug($values['slug'] !== '' ? $values['slug'] : $values['title'], $isEdit ? (int) $song['id'] : null);
        $pdo = db();
        $pdo->beginTransaction();

        try {
            if ($values['is_featured']) {
                $pdo->exec('UPDATE songs SET is_featured = 0');
            }

            $parameters = [
                'title' => $values['title'],
                'slug' => $slug,
                'artist' => $values['artist'],
                'songwriter' => $values['songwriter'] ?: null,
                'release_year' => $year ?: null,
                'album' => $values['album'] ?: null,
                'production' => $values['production'] ?: null,
                'youtube_id' => $youtubeId,
                'cover_url' => $values['cover_url'] ?: null,
                'short_description' => $values['short_description'] ?: null,
                'lyrics' => $values['lyrics'],
                'is_featured' => $values['is_featured'],
                'status' => $values['status'],
            ];

            if ($isEdit) {
                $parameters['id'] = (int) $song['id'];
                $statement = $pdo->prepare(
                    'UPDATE songs SET title = :title, slug = :slug, artist = :artist, songwriter = :songwriter,
                     release_year = :release_year, album = :album, production = :production, youtube_id = :youtube_id,
                     cover_url = :cover_url, short_description = :short_description, lyrics = :lyrics,
                     is_featured = :is_featured, status = :status WHERE id = :id'
                );
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO songs (title, slug, artist, songwriter, release_year, album, production, youtube_id,
                     cover_url, short_description, lyrics, is_featured, status)
                     VALUES (:title, :slug, :artist, :songwriter, :release_year, :album, :production, :youtube_id,
                     :cover_url, :short_description, :lyrics, :is_featured, :status)'
                );
            }

            $statement->execute($parameters);
            $pdo->commit();
            flash('success', $isEdit ? 'Perubahan lagu berhasil disimpan.' : 'Lagu baru berhasil ditambahkan.');
            redirect('admin/index.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log((string) $exception);
            $errors['form'] = 'Data belum dapat disimpan. Periksa isian dan koneksi database, lalu coba lagi.';
        }
    }
}

$adminTitle = $isEdit ? 'Edit ' . $song['title'] : 'Tambah lagu';
require APP_ROOT . '/app/partials/admin-header.php';
?>
<div class="admin-page-heading form-heading">
    <div>
        <a class="admin-back" href="<?= e(url('admin/index.php')) ?>">← Kembali ke daftar</a>
        <h1><?= $isEdit ? 'Edit karya.' : 'Tambahkan karya baru.' ?></h1>
        <p>Isi informasi utama, video YouTube, serta lirik yang Anda miliki atau sudah diizinkan.</p>
    </div>
</div>

<?php if (!empty($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>

<form method="post" class="editor-form" novalidate>
    <?= csrf_field() ?>
    <div class="editor-main">
        <section class="admin-panel form-section">
            <div class="panel-heading"><div><span class="form-step">01</span><h2>Informasi lagu</h2><p>Data yang akan dibaca oleh pengunjung.</p></div></div>
            <div class="form-grid two">
                <div class="field span-two">
                    <label for="title">Judul lagu <b>*</b></label>
                    <input id="title" name="title" value="<?= e($values['title']) ?>" maxlength="150" required autofocus <?= !empty($errors['title']) ? 'aria-invalid="true" aria-describedby="title-error"' : '' ?>>
                    <?php if (!empty($errors['title'])): ?><small class="field-error" id="title-error"><?= e($errors['title']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="artist">Penyanyi / artis <b>*</b></label>
                    <input id="artist" name="artist" value="<?= e($values['artist']) ?>" maxlength="120" required <?= !empty($errors['artist']) ? 'aria-invalid="true" aria-describedby="artist-error"' : '' ?>>
                    <?php if (!empty($errors['artist'])): ?><small class="field-error" id="artist-error"><?= e($errors['artist']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="songwriter">Songwriter</label>
                    <input id="songwriter" name="songwriter" value="<?= e($values['songwriter']) ?>" maxlength="120" <?= !empty($errors['songwriter']) ? 'aria-invalid="true" aria-describedby="songwriter-error"' : '' ?>>
                    <?php if (!empty($errors['songwriter'])): ?><small class="field-error" id="songwriter-error"><?= e($errors['songwriter']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="release-year">Tahun rilis</label>
                    <input id="release-year" name="release_year" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>" value="<?= e($values['release_year']) ?>" <?= !empty($errors['release_year']) ? 'aria-invalid="true" aria-describedby="release-year-error"' : '' ?>>
                    <?php if (!empty($errors['release_year'])): ?><small class="field-error" id="release-year-error"><?= e($errors['release_year']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="album">Album / pelayanan</label>
                    <input id="album" name="album" value="<?= e($values['album']) ?>" maxlength="150" placeholder="Contoh: JMI Praise & Worship" <?= !empty($errors['album']) ? 'aria-invalid="true" aria-describedby="album-error"' : '' ?>>
                    <?php if (!empty($errors['album'])): ?><small class="field-error" id="album-error"><?= e($errors['album']) ?></small><?php endif; ?>
                </div>
                <div class="field span-two">
                    <label for="production">Produksi / kredit tambahan</label>
                    <input id="production" name="production" value="<?= e($values['production']) ?>" maxlength="180" placeholder="Contoh: Chaka Music Production" <?= !empty($errors['production']) ? 'aria-invalid="true" aria-describedby="production-error"' : '' ?>>
                    <?php if (!empty($errors['production'])): ?><small class="field-error" id="production-error"><?= e($errors['production']) ?></small><?php endif; ?>
                </div>
                <div class="field span-two">
                    <label for="slug">Alamat halaman <span>opsional</span></label>
                    <div class="input-prefix"><span>/song.php?slug=</span><input id="slug" name="slug" value="<?= e($values['slug']) ?>" maxlength="180" placeholder="dibuat otomatis-dari-judul" <?= !empty($errors['slug']) ? 'aria-invalid="true" aria-describedby="slug-error"' : '' ?>></div>
                    <?php if (!empty($errors['slug'])): ?><small class="field-error" id="slug-error"><?= e($errors['slug']) ?></small><?php endif; ?>
                </div>
                <div class="field span-two">
                    <label for="description">Cerita singkat</label>
                    <textarea id="description" name="short_description" rows="4" maxlength="600" placeholder="Ceritakan pesan atau latar belakang lagu dalam beberapa kalimat…" <?= !empty($errors['short_description']) ? 'aria-invalid="true" aria-describedby="description-error"' : '' ?>><?= e($values['short_description']) ?></textarea>
                    <small>Maksimal 600 karakter.</small>
                    <?php if (!empty($errors['short_description'])): ?><small class="field-error" id="description-error"><?= e($errors['short_description']) ?></small><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="admin-panel form-section">
            <div class="panel-heading"><div><span class="form-step">02</span><h2>Video & tampilan</h2><p>Video ini menjadi player dan latar jika lagu dipilih sebagai unggulan.</p></div></div>
            <div class="form-grid">
                <div class="field">
                    <label for="youtube-url">URL atau ID video YouTube</label>
                    <input id="youtube-url" name="youtube_url" value="<?= e($values['youtube_id']) ?>" maxlength="500" placeholder="https://www.youtube.com/watch?v=…" <?= !empty($errors['youtube_url']) ? 'aria-invalid="true" aria-describedby="youtube-url-help youtube-url-error"' : 'aria-describedby="youtube-url-help"' ?>>
                    <small id="youtube-url-help">Tempel URL lengkap; ID video akan diambil otomatis.</small>
                    <?php if (!empty($errors['youtube_url'])): ?><small class="field-error" id="youtube-url-error"><?= e($errors['youtube_url']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="cover-url">URL gambar cover <span>opsional</span></label>
                    <input id="cover-url" name="cover_url" type="url" value="<?= e($values['cover_url']) ?>" maxlength="500" placeholder="https://…/cover.jpg" <?= !empty($errors['cover_url']) ? 'aria-invalid="true" aria-describedby="cover-url-help cover-url-error"' : 'aria-describedby="cover-url-help"' ?>>
                    <small id="cover-url-help">Kosongkan untuk memakai thumbnail YouTube otomatis.</small>
                    <?php if (!empty($errors['cover_url'])): ?><small class="field-error" id="cover-url-error"><?= e($errors['cover_url']) ?></small><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="admin-panel form-section">
            <div class="panel-heading"><div><span class="form-step">03</span><h2>Lirik lagu</h2><p>Gunakan baris baru seperti lirik biasa. Bagian seperti [Verse 1] akan ditata sebagai judul.</p></div></div>
            <div class="field">
                <label for="lyrics">Lirik yang diizinkan</label>
                <textarea class="lyrics-editor" id="lyrics" name="lyrics" rows="22" placeholder="[Verse 1]&#10;Tulis lirik Anda di sini…&#10;&#10;[Chorus]&#10;…"><?= e($values['lyrics']) ?></textarea>
                <small>Pastikan Anda memiliki hak atau izin untuk menerbitkan lirik ini.</small>
            </div>
        </section>
    </div>

    <aside class="editor-sidebar">
        <section class="admin-panel publish-card">
            <h2>Publikasi</h2>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?= $values['status'] === 'draft' ? 'selected' : '' ?>>Draft — hanya admin</option>
                    <option value="published" <?= $values['status'] === 'published' ? 'selected' : '' ?>>Tayang — dapat dilihat</option>
                </select>
            </div>
            <label class="check-card">
                <input type="checkbox" name="is_featured" value="1" <?= $values['is_featured'] ? 'checked' : '' ?> <?= !empty($errors['is_featured']) ? 'aria-invalid="true" aria-describedby="featured-error"' : '' ?>>
                <span><strong>Jadikan lagu unggulan</strong><small>Video tampil sebagai latar beranda. Pilihan sebelumnya akan diganti.</small></span>
            </label>
            <?php if (!empty($errors['is_featured'])): ?><small class="field-error" id="featured-error"><?= e($errors['is_featured']) ?></small><?php endif; ?>
            <button class="button button-dark button-block" type="submit"><?= $isEdit ? 'Simpan perubahan' : 'Tambahkan lagu' ?></button>
            <a class="button button-plain button-block" href="<?= e(url('admin/index.php')) ?>">Batal</a>
        </section>
        <div class="editor-tip"><strong>Tip</strong><p>Autoplay latar YouTube selalu tanpa suara karena aturan browser. Pengunjung tetap dapat menonton dengan suara melalui player di halaman lagu.</p></div>
    </aside>
</form>
<?php require APP_ROOT . '/app/partials/admin-footer.php'; ?>
