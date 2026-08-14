<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();

$songs = get_all_songs();
$counts = song_counts();
$success = flash('success');
$error = flash('error');
$adminTitle = 'Ringkasan';

require APP_ROOT . '/app/partials/admin-header.php';
?>
<div class="admin-page-heading">
    <div>
        <p class="eyebrow dark">Dashboard</p>
        <h1>Karya Anda, dalam satu tempat.</h1>
        <p>Kelola katalog, lirik, dan video yang tampil di website.</p>
    </div>
    <a class="button button-dark" href="<?= e(url('admin/song-form.php')) ?>">＋ Tambah lagu</a>
</div>

<?php if ($success): ?><div class="alert alert-success" data-alert><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" data-alert><?= e($error) ?></div><?php endif; ?>

<div class="stat-grid">
    <article><span>Total karya</span><strong><?= $counts['total'] ?></strong><small>Semua lagu tersimpan</small></article>
    <article><span>Sudah tayang</span><strong><?= $counts['published'] ?></strong><small>Dapat dilihat pengunjung</small></article>
    <article><span>Masih draft</span><strong><?= $counts['draft'] ?></strong><small>Hanya terlihat di admin</small></article>
</div>

<section class="admin-panel">
    <div class="panel-heading">
        <div><h2>Daftar lagu</h2><p>Terakhir diperbarui ditampilkan paling atas.</p></div>
        <a class="text-link" href="<?= e(url()) ?>" target="_blank">Lihat website ↗</a>
    </div>

    <?php if ($songs): ?>
        <div class="table-wrap">
            <table class="song-table">
                <thead><tr><th>Lagu</th><th>Status</th><th>Video</th><th>Diperbarui</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody>
                <?php foreach ($songs as $song): ?>
                    <tr>
                        <td>
                            <div class="table-song">
                                <span class="table-cover" style="--card-image: url('<?= e(css_url(is_http_url($song['cover_url'] ?? '') ? $song['cover_url'] : youtube_thumbnail($song['youtube_id'] ?? null, 'default'))) ?>')"></span>
                                <div><strong><?= e($song['title']) ?><?= $song['is_featured'] ? ' ★' : '' ?></strong><small><?= e($song['artist']) ?><?= $song['release_year'] ? ' · ' . e($song['release_year']) : '' ?></small></div>
                            </div>
                        </td>
                        <td><span class="status-pill <?= e($song['status']) ?>"><?= $song['status'] === 'published' ? 'Tayang' : 'Draft' ?></span></td>
                        <td><?= $song['youtube_id'] ? 'Terhubung' : 'Belum ada' ?></td>
                        <td><?= e(date('d M Y', strtotime($song['updated_at']))) ?></td>
                        <td class="table-actions">
                            <?php if ($song['status'] === 'published'): ?><a href="<?= e(url('song.php?slug=' . rawurlencode($song['slug']))) ?>" target="_blank" title="Lihat">↗</a><?php endif; ?>
                            <a href="<?= e(url('admin/song-form.php?id=' . $song['id'])) ?>">Edit</a>
                            <form action="<?= e(url('admin/song-delete.php')) ?>" method="post" data-confirm="Hapus lagu ‘<?= e($song['title']) ?>’? Tindakan ini tidak dapat dibatalkan.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $song['id'] ?>">
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty"><span>♫</span><h3>Belum ada lagu</h3><p>Tambahkan karya pertama untuk mulai mengisi website.</p><a class="button button-dark" href="<?= e(url('admin/song-form.php')) ?>">Tambah lagu pertama</a></div>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/partials/admin-footer.php'; ?>
