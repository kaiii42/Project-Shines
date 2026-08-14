<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));

try {
    $settings = get_site_settings();
    $song = $slug !== '' ? get_song_by_slug($slug) : null;
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    $settings = default_site_settings();
    $pageTitle = 'Database belum terhubung · Frank Shines';
    $pageDescription = 'Website sedang menunggu koneksi database lokal.';
    $bodyClass = 'inner-page';
    require APP_ROOT . '/app/partials/site-header.php';
    ?>
    <main id="main-content" class="error-page">
        <div class="shell error-content">
            <span>503</span>
            <h1>Database belum terhubung.</h1>
            <p>Jalankan MySQL di XAMPP dan pastikan <code>database/schema.sql</code> sudah diimpor.</p>
            <a class="button button-dark" href="<?= e(url()) ?>">Kembali ke beranda</a>
        </div>
    </main>
    <?php
    require APP_ROOT . '/app/partials/site-footer.php';
    exit;
}

if (!$song) {
    http_response_code(404);
    $pageTitle = 'Lagu tidak ditemukan · ' . ($settings['brand_name'] ?? 'Frank Shines');
    $bodyClass = 'inner-page';
    require APP_ROOT . '/app/partials/site-header.php';
    ?>
    <main id="main-content" class="error-page">
        <div class="shell error-content">
            <span>404</span>
            <h1>Lagu tidak ditemukan.</h1>
            <p>Mungkin tautannya berubah atau lagu masih berupa draft.</p>
            <a class="button button-dark" href="<?= e(url()) ?>">Kembali ke katalog</a>
        </div>
    </main>
    <?php
    require APP_ROOT . '/app/partials/site-footer.php';
    exit;
}

$relatedSongs = get_published_songs('', (int) $song['id'], 3);
$pageTitle = $song['title'] . ' · ' . $song['artist'];
$pageDescription = $song['short_description'] ?: 'Dengarkan ' . $song['title'] . ' dan baca lirik resminya.';
$bodyClass = 'inner-page song-page';
$coverImage = is_http_url($song['cover_url'] ?? '') ? $song['cover_url'] : youtube_thumbnail($song['youtube_id'] ?? null);

require APP_ROOT . '/app/partials/site-header.php';
?>
<main id="main-content">
    <section class="song-masthead" style="--song-cover: url('<?= e(css_url($coverImage)) ?>')">
        <div class="song-masthead-image" aria-hidden="true"></div>
        <div class="song-masthead-wash" aria-hidden="true"></div>
        <div class="shell song-masthead-content">
            <a class="back-link" href="<?= e(url('#lagu')) ?>">← Semua lagu</a>
            <p class="eyebrow">Single · <?= e($song['release_year'] ?: 'Frank Shines Music') ?></p>
            <h1><?= e($song['title']) ?></h1>
            <p class="song-artist"><?= e($song['artist']) ?></p>
            <p class="song-lead"><?= e($song['short_description'] ?: 'Dengarkan lagunya, resapi pesannya, dan baca liriknya di bawah.') ?></p>
        </div>
    </section>

    <section class="song-detail section">
        <div class="shell song-detail-grid">
            <aside class="song-player-column">
                <div class="song-player-card">
                    <?php if (!empty($song['youtube_id'])): ?>
                        <div class="video-frame">
                            <iframe
                                src="<?= e(youtube_embed_url($song['youtube_id'])) ?>"
                                title="Putar <?= e($song['title']) ?>"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </div>
                    <?php else: ?>
                        <div class="video-placeholder" style="--card-image: url('<?= e(css_url($coverImage)) ?>')"><span>Video belum ditambahkan</span></div>
                    <?php endif; ?>
                    <div class="player-caption">
                        <div><small>Sekarang diputar</small><strong><?= e($song['title']) ?></strong></div>
                        <?php if (!empty($song['youtube_id'])): ?>
                            <a href="https://www.youtube.com/watch?v=<?= e($song['youtube_id']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Buka di YouTube">↗</a>
                        <?php endif; ?>
                    </div>
                </div>

                <dl class="song-credits">
                    <?php if (!empty($song['songwriter'])): ?><div><dt>Songwriter</dt><dd><?= e($song['songwriter']) ?></dd></div><?php endif; ?>
                    <div><dt>Vokal / artis</dt><dd><?= e($song['artist']) ?></dd></div>
                    <?php if (!empty($song['album'])): ?><div><dt>Album / pelayanan</dt><dd><?= e($song['album']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($song['production'])): ?><div><dt>Produksi</dt><dd><?= e($song['production']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($song['release_year'])): ?><div><dt>Tahun rilis</dt><dd><?= e($song['release_year']) ?></dd></div><?php endif; ?>
                </dl>
            </aside>

            <article class="lyrics-reader">
                <div class="lyrics-toolbar">
                    <div>
                        <p class="eyebrow dark">Official lyrics</p>
                        <h2>Lirik <?= e($song['title']) ?></h2>
                    </div>
                    <div class="reader-tools" aria-label="Alat pembaca">
                        <button type="button" data-font-decrease aria-label="Perkecil tulisan">A−</button>
                        <button type="button" data-font-increase aria-label="Perbesar tulisan">A＋</button>
                        <button type="button" data-copy-lyrics data-copy-label="Salin lirik">Salin lirik</button>
                    </div>
                </div>
                <div class="lyrics-content" id="lyrics-text" data-lyrics-content>
                    <?= format_lyrics($song['lyrics']) ?>
                </div>
                <?php if (!empty($song['short_description'])): ?>
                    <div class="song-note">
                        <strong>Tentang lagu</strong>
                        <p><?= nl2br(e($song['short_description'])) ?></p>
                    </div>
                <?php endif; ?>
            </article>
        </div>
    </section>

    <?php if ($relatedSongs): ?>
        <section class="related section">
            <div class="shell">
                <div class="section-heading compact">
                    <div><p class="eyebrow dark">Putar berikutnya</p><h2>Lagu lainnya</h2></div>
                    <a class="text-link" href="<?= e(url('#lagu')) ?>">Lihat semua <span aria-hidden="true">→</span></a>
                </div>
                <div class="song-grid">
                    <?php foreach ($relatedSongs as $index => $relatedSong): ?>
                        <?php $song = $relatedSong; $songNumber = $index + 1; require APP_ROOT . '/app/partials/song-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require APP_ROOT . '/app/partials/site-footer.php'; ?>
