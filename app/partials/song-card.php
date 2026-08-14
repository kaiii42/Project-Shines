<?php

declare(strict_types=1);

$cardImage = is_http_url($song['cover_url'] ?? '') ? $song['cover_url'] : youtube_thumbnail($song['youtube_id'] ?? null, 'hqdefault');
?>
<article class="song-card">
    <a class="song-card-image" href="<?= e(url('song.php?slug=' . rawurlencode((string) $song['slug']))) ?>" style="--card-image: url('<?= e(css_url($cardImage)) ?>')">
        <span class="song-number" aria-hidden="true"><?= str_pad((string) ($songNumber ?? 1), 2, '0', STR_PAD_LEFT) ?></span>
        <span class="play-chip" aria-hidden="true">▶</span>
        <span class="sr-only">Buka lagu <?= e($song['title']) ?></span>
    </a>
    <div class="song-card-body">
        <p class="song-card-meta"><?= e($song['release_year'] ?: 'Lagu') ?> · <?= e($song['artist']) ?></p>
        <h3><a href="<?= e(url('song.php?slug=' . rawurlencode((string) $song['slug']))) ?>"><?= e($song['title']) ?></a></h3>
        <p><?= e(truncate_text($song['short_description'] ?: 'Dengarkan lagunya dan baca lirik lengkapnya.', 118)) ?></p>
        <a class="text-link" href="<?= e(url('song.php?slug=' . rawurlencode((string) $song['slug']))) ?>">Dengar & baca lirik <span aria-hidden="true">→</span></a>
    </div>
</article>
