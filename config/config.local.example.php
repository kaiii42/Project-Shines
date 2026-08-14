<?php

declare(strict_types=1);

// Salin file ini menjadi config.local.php hanya jika konfigurasi XAMPP Anda berbeda.
return [
    // Aktifkan hanya saat memeriksa error di komputer lokal.
    'debug' => true,
    'base_url' => '',
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'jmi_music',
        'username' => 'root',
        'password' => '',
    ],
];
