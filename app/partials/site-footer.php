<?php

declare(strict_types=1);
?>
<footer class="site-footer">
    <div class="shell footer-grid">
        <div>
            <a class="brand brand-footer" href="<?= e(url()) ?>">
                <span class="brand-mark" aria-hidden="true">FS</span>
                <span class="brand-copy"><strong><?= e($settings['brand_name'] ?? 'Frank Shines') ?></strong></span>
            </a>
            <p>Musik yang lahir dari iman, dibagikan untuk menjadi berkat.</p>
        </div>
        <div class="footer-links">
            <strong>Jelajahi</strong>
            <a href="<?= e(url('#lagu')) ?>">Katalog lagu</a>
            <a href="<?= e(url('#tentang')) ?>">Cerita kami</a>
            <a href="<?= e(url('admin/login.php')) ?>">Panel admin</a>
        </div>
        <div class="footer-links">
            <strong>Terhubung</strong>
            <?php if (is_http_url($settings['youtube_channel_url'] ?? '')): ?>
                <a href="<?= e($settings['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer">YouTube</a>
            <?php endif; ?>
            <?php if (is_http_url($settings['instagram_url'] ?? '')): ?>
                <a href="<?= e($settings['instagram_url']) ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
            <?php endif; ?>
            <?php if (!empty($settings['contact_email'])): ?>
                <a href="mailto:<?= e($settings['contact_email']) ?>"><?= e($settings['contact_email']) ?></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="shell footer-bottom">
        <span>© <?= date('Y') ?> <?= e($settings['brand_name'] ?? 'Frank Shines') ?></span>
        <span>Dibuat untuk karya yang Anda miliki atau izinkan.</span>
    </div>
</footer>
</body>
</html>
