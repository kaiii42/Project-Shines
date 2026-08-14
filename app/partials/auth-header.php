<?php

declare(strict_types=1);

$authTitle = $authTitle ?? 'Admin Frank Shines';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1716">
    <title><?= e($authTitle) ?> · Frank Shines</title>
    <link rel="icon" href="<?= e(asset('assets/images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<main class="auth-shell">
    <a class="brand auth-brand" href="<?= e(url()) ?>">
        <span class="brand-mark" aria-hidden="true">FS</span>
        <span class="brand-copy"><strong>Frank Shines</strong><small>Official music & lyrics</small></span>
    </a>
