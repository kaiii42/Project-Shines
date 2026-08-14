-- Frank Shines Music & Lyrics
-- Impor file ini melalui phpMyAdmin. Aman dijalankan ulang.

CREATE DATABASE IF NOT EXISTS jmi_music
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE jmi_music;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS songs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    artist VARCHAR(120) NOT NULL,
    songwriter VARCHAR(120) NULL,
    release_year SMALLINT UNSIGNED NULL,
    album VARCHAR(150) NULL,
    production VARCHAR(180) NULL,
    youtube_id CHAR(11) NULL,
    cover_url VARCHAR(500) NULL,
    short_description TEXT NULL,
    lyrics LONGTEXT NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_songs_status (status),
    INDEX idx_songs_featured (is_featured),
    INDEX idx_songs_year (release_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
    ('brand_name', 'Frank Shines'),
    ('hero_eyebrow', 'Lagu, iman, dan kesaksian'),
    ('hero_tagline', 'Songs that lead the heart home.'),
    ('intro_text', 'Ruang resmi untuk mendengar karya, membaca lirik, dan mengenal cerita di balik setiap lagu.'),
    ('youtube_channel_url', 'https://www.youtube.com/watch?v=-0xhYb9Xhh8'),
    ('instagram_url', ''),
    ('contact_email', '');

-- Data contoh. Lirik sengaja dikosongkan; masukkan hanya lirik yang Anda miliki/izinkan lewat admin.
INSERT IGNORE INTO songs (
    title, slug, artist, songwriter, release_year, album, production,
    youtube_id, cover_url, short_description, lyrics, is_featured, status
) VALUES (
    'Thy Cross',
    'thy-cross',
    'Ika Rosarie',
    'Jake Merril Ibo',
    2022,
    'JMI Praise & Worship',
    'Chaka Music Production',
    '-0xhYb9Xhh8',
    NULL,
    'Sebuah lagu penyembahan tentang iman, pengorbanan, dan penebusan; ditulis oleh Jake Merril Ibo dan dibawakan oleh Ika Rosarie.',
    '',
    1,
    'published'
);
