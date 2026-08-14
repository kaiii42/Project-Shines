<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$search = trim((string) ($_GET['q'] ?? ''));
$databaseError = null;

try {
    $settings = get_site_settings();
    $featuredSong = get_featured_song();
    $songs = get_published_songs($search);
} catch (Throwable $exception) {
    error_log((string) $exception);
    $settings = default_site_settings();
    $featuredSong = null;
    $songs = [];
    $databaseError = $exception;
}

$pageTitle = ($settings['brand_name'] ?? 'Frank Shines') . ' · Official Music & Lyrics';
$pageDescription = $settings['intro_text'] ?? 'Dengarkan lagu dan baca lirik resmi Frank Shines.';
$bodyClass = 'home-page';
$heroTitle = $featuredSong['title'] ?? 'Songs that lead the heart home.';
$heroArtist = $featuredSong['artist'] ?? ($settings['brand_name'] ?? 'Frank Shines');
$heroImage = $featuredSong
    ? (is_http_url($featuredSong['cover_url'] ?? '') ? $featuredSong['cover_url'] : youtube_thumbnail($featuredSong['youtube_id'] ?? null))
    : youtube_thumbnail(null);

require APP_ROOT . '/app/partials/site-header.php';
?>
<main id="main-content">
    <section class="hero" style="--hero-image: url('<?= e(css_url($heroImage)) ?>')">
        <div class="hero-fallback" aria-hidden="true"></div>
        <?php if (!empty($featuredSong['youtube_id'])): ?>
            <div class="hero-video" aria-hidden="true">
                <iframe
                    id="hero-youtube"
                    src="<?= e(youtube_embed_url($featuredSong['youtube_id'], true)) ?>"
                    title="Video latar <?= e($featuredSong['title']) ?>"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    tabindex="-1"
                ></iframe>
            </div>
        <?php endif; ?>
        <div class="hero-wash" aria-hidden="true"></div>
        <div class="shell hero-content">
            <p class="eyebrow"><?= e($settings['hero_eyebrow'] ?? 'Lagu, iman, dan kesaksian') ?></p>
            <?php if ($featuredSong): ?>
                <p class="hero-kicker">Lagu unggulan · <?= e($featuredSong['release_year'] ?: 'Terbaru') ?></p>
            <?php endif; ?>
            <h1><?= e($heroTitle) ?></h1>
            <p class="hero-byline"><?= e($heroArtist) ?></p>
            <p class="hero-intro"><?= e($featuredSong['short_description'] ?? ($settings['intro_text'] ?? 'Ruang untuk mendengar karya dan membaca lirik.')) ?></p>
            <div class="hero-actions">
                <?php if ($featuredSong): ?>
                    <a class="button button-primary" href="<?= e(url('song.php?slug=' . rawurlencode($featuredSong['slug']))) ?>"><span aria-hidden="true">▶</span> Dengarkan sekarang</a>
                <?php else: ?>
                    <a class="button button-primary" href="#lagu">Lihat katalog</a>
                <?php endif; ?>
                <a class="button button-ghost" href="#lagu">Baca lirik <span aria-hidden="true">↓</span></a>
            </div>
        </div>
        <div class="hero-edge">
            <span>Scroll untuk menjelajah</span>
            <?php if (!empty($featuredSong['youtube_id'])): ?>
                <button class="sound-control" type="button" data-video-toggle aria-pressed="false">
                    <span data-video-icon aria-hidden="true">Ⅱ</span>
                    <span data-video-label>Jeda latar</span>
                </button>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($databaseError): ?>
        <section class="setup-notice">
            <div class="shell setup-notice-inner">
                <div>
                    <p class="eyebrow dark">Satu langkah lagi</p>
                    <h2>Database belum terhubung.</h2>
                    <p>Jalankan MySQL di XAMPP, impor <code>database/schema.sql</code> melalui phpMyAdmin, kemudian muat ulang halaman ini.</p>
                    <?php if (config('debug')): ?><small><?= e($databaseError->getMessage()) ?></small><?php endif; ?>
                </div>
                <a class="button button-dark" href="<?= e(url('admin/setup.php')) ?>">Buka setup admin</a>
            </div>
        </section>
    <?php endif; ?>

    <section class="catalog section" id="lagu">
        <div class="shell">
            <div class="section-heading">
                <div>
                    <p class="eyebrow dark">Music library</p>
                    <h2>Setiap lagu punya cerita.</h2>
                </div>
                <p>Temukan video, lirik, dan kredit karya dalam satu ruang yang tenang.</p>
            </div>

            <form class="song-search" action="<?= e(url()) ?>" method="get" role="search">
                <label for="song-search-input">Cari lagu atau penyanyi</label>
                <div class="search-control">
                    <span aria-hidden="true">⌕</span>
                    <input id="song-search-input" name="q" type="search" value="<?= e($search) ?>" placeholder="Ketik judul, penyanyi, atau album…">
                    <button type="submit">Cari</button>
                </div>
            </form>

            <?php if ($search !== ''): ?>
                <div class="search-summary">
                    <p><?= count($songs) ?> hasil untuk “<?= e($search) ?>”</p>
                    <a href="<?= e(url('#lagu')) ?>">Hapus pencarian</a>
                </div>
            <?php endif; ?>

            <?php if ($songs): ?>
                <div class="song-grid">
                    <?php foreach ($songs as $index => $song): ?>
                        <?php $songNumber = $index + 1; require APP_ROOT . '/app/partials/song-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!$databaseError): ?>
                <div class="empty-state">
                    <span aria-hidden="true">♫</span>
                    <h3><?= $search !== '' ? 'Lagu belum ditemukan' : 'Katalog masih kosong' ?></h3>
                    <p><?= $search !== '' ? 'Coba kata kunci lain atau tampilkan semua lagu.' : 'Masuk ke panel admin untuk menambahkan karya pertama.' ?></p>
                    <a class="button button-dark" href="<?= e($search !== '' ? url('#lagu') : url('admin/login.php')) ?>"><?= $search !== '' ? 'Tampilkan semua' : 'Buka panel admin' ?></a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section" id="tentang">
        <div class="shell about-grid">
            <div class="about-index" aria-hidden="true">01</div>
            <div class="about-title">
                <p class="eyebrow">Tentang ruang ini</p>
                <h2>Lebih dekat dengan pesan di balik musik.</h2>
            </div>
            <div class="about-copy">
                <p><?= e($settings['intro_text'] ?? 'Ruang resmi untuk mendengar karya, membaca lirik, dan mengenal cerita di balik setiap lagu.') ?></p>
                <p>Video tetap diputar dari kanal YouTube resmi, sementara lirik dan kredit dapat Anda kelola sendiri dari panel admin lokal.</p>
                <?php if (is_http_url($settings['youtube_channel_url'] ?? '')): ?>
                    <a class="text-link light" href="<?= e($settings['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer">Kunjungi kanal YouTube <span aria-hidden="true">↗</span></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="closing-quote">
        <div class="shell">
            <span class="quote-mark" aria-hidden="true">“</span>
            <p><?= e($settings['hero_tagline'] ?? 'Songs that lead the heart home.') ?></p>
            <small><?= e($settings['brand_name'] ?? 'Frank Shines') ?></small>
        </div>
    </section>
</main>
<?php require APP_ROOT . '/app/partials/site-footer.php'; ?>
