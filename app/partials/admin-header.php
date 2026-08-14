<?php

declare(strict_types=1);

$adminTitle = $adminTitle ?? 'Dashboard';
$currentUser = current_user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1716">
    <title><?= e($adminTitle) ?> · Admin Frank Shines</title>
    <link rel="icon" href="<?= e(asset('assets/images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
    <script defer src="<?= e(asset('assets/js/app.js')) ?>"></script>
</head>
<body class="admin-body">
<a class="skip-link" href="#admin-content">Lewati ke konten</a>
<header class="admin-topbar">
    <a class="brand" href="<?= e(url('admin/index.php')) ?>">
        <span class="brand-mark" aria-hidden="true">FS</span>
        <span class="brand-copy"><strong>Studio Admin</strong><small>Kelola karya Anda</small></span>
    </a>
    <button class="admin-menu-toggle" type="button" data-admin-menu aria-expanded="false" aria-controls="admin-sidebar">Menu</button>
    <div class="admin-user">
        <span>Halo, <?= e($currentUser['username'] ?? 'Admin') ?></span>
        <a href="<?= e(url()) ?>" target="_blank">Lihat situs ↗</a>
    </div>
</header>
<div class="admin-layout">
    <aside class="admin-sidebar" id="admin-sidebar" data-admin-sidebar>
        <nav aria-label="Navigasi admin">
            <a class="<?= current_path_is('/admin/index.php') ? 'active' : '' ?>" href="<?= e(url('admin/index.php')) ?>"><span aria-hidden="true">◫</span> Ringkasan</a>
            <a class="<?= current_path_is('/admin/song-form.php') ? 'active' : '' ?>" href="<?= e(url('admin/song-form.php')) ?>"><span aria-hidden="true">＋</span> Tambah lagu</a>
            <a class="<?= current_path_is('/admin/settings.php') ? 'active' : '' ?>" href="<?= e(url('admin/settings.php')) ?>"><span aria-hidden="true">◎</span> Pengaturan situs</a>
        </nav>
        <form action="<?= e(url('admin/logout.php')) ?>" method="post">
            <?= csrf_field() ?>
            <button class="sidebar-logout" type="submit">Keluar</button>
        </form>
    </aside>
    <main class="admin-content" id="admin-content">
