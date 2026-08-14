<?php

declare(strict_types=1);

function default_site_settings(): array
{
    return [
        'brand_name' => 'Frank Shines',
        'hero_eyebrow' => 'Lagu, iman, dan kesaksian',
        'hero_tagline' => 'Songs that lead the heart home.',
        'intro_text' => 'Ruang resmi untuk mendengar karya, membaca lirik, dan mengenal cerita di balik setiap lagu.',
        'youtube_channel_url' => 'https://www.youtube.com/watch?v=-0xhYb9Xhh8',
        'instagram_url' => '',
        'contact_email' => '',
    ];
}

function get_site_settings(): array
{
    $settings = default_site_settings();
    $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
    foreach ($rows as $row) {
        $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
    }
    return $settings;
}

function save_site_settings(array $settings): void
{
    $statement = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    foreach ($settings as $key => $value) {
        $statement->execute(['key' => $key, 'value' => $value]);
    }
}

function get_featured_song(): ?array
{
    $statement = db()->query(
        "SELECT * FROM songs WHERE status = 'published' ORDER BY is_featured DESC, created_at DESC LIMIT 1"
    );
    $song = $statement->fetch();
    return $song ?: null;
}

function get_published_songs(string $search = '', ?int $excludeId = null, ?int $limit = null): array
{
    $where = ["status = 'published'"];
    $params = [];

    if ($search !== '') {
        $where[] = '(title LIKE :search OR artist LIKE :search OR album LIKE :search OR songwriter LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    if ($excludeId !== null) {
        $where[] = 'id != :exclude_id';
        $params['exclude_id'] = $excludeId;
    }

    $sql = 'SELECT * FROM songs WHERE ' . implode(' AND ', $where) . ' ORDER BY is_featured DESC, release_year DESC, title ASC';
    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function get_song_by_slug(string $slug, bool $includeDraft = false): ?array
{
    $sql = 'SELECT * FROM songs WHERE slug = :slug';
    if (!$includeDraft) {
        $sql .= " AND status = 'published'";
    }
    $sql .= ' LIMIT 1';

    $statement = db()->prepare($sql);
    $statement->execute(['slug' => $slug]);
    $song = $statement->fetch();
    return $song ?: null;
}

function get_song_by_id(int $id): ?array
{
    $statement = db()->prepare('SELECT * FROM songs WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $song = $statement->fetch();
    return $song ?: null;
}

function get_all_songs(): array
{
    return db()->query('SELECT * FROM songs ORDER BY updated_at DESC, title ASC')->fetchAll();
}

function song_counts(): array
{
    $row = db()->query(
        "SELECT COUNT(*) AS total,
                SUM(status = 'published') AS published,
                SUM(status = 'draft') AS draft
         FROM songs"
    )->fetch();

    return [
        'total' => (int) ($row['total'] ?? 0),
        'published' => (int) ($row['published'] ?? 0),
        'draft' => (int) ($row['draft'] ?? 0),
    ];
}
