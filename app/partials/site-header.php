<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? (string) ($settings['brand_name'] ?? config('app_name'));
$pageDescription = $pageDescription ?? (string) ($settings['intro_text'] ?? 'Katalog lagu dan lirik resmi.');
$bodyClass = $bodyClass ?? '';
$settings = $settings ?? default_site_settings();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#0b1716">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= e(asset('assets/images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <script defer src="<?= e(asset('assets/js/app.js')) ?>"></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Lewati ke konten</a>
<header class="site-header" data-site-header>
    <div class="shell header-inner">
        <a class="brand" href="<?= e(url()) ?>" aria-label="Kembali ke beranda">
            <span class="brand-mark" aria-hidden="true">FS</span>
            <span class="brand-copy">
                <strong><?= e($settings['brand_name'] ?? 'Frank Shines') ?></strong>
                <small>Official music & lyrics</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation" data-nav-toggle>
            <span></span><span></span>
            <span class="sr-only">Buka menu</span>
        </button>

        <nav class="site-nav" id="site-navigation" aria-label="Navigasi utama" data-site-nav>
            <a href="<?= e(url()) ?>">Beranda</a>
            <a href="<?= e(url('#lagu')) ?>">Lagu & lirik</a>
            <a href="<?= e(url('#tentang')) ?>">Tentang</a>
            <?php if (is_http_url($settings['youtube_channel_url'] ?? '')): ?>
                <a class="nav-cta" href="<?= e($settings['youtube_channel_url']) ?>" target="_blank" rel="noopener noreferrer">YouTube <span aria-hidden="true">↗</span></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
