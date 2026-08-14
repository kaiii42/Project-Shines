<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    global $config;

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) config('base_url', ''), '/');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base !== '' ? $base . '/' : '/';
    }

    return ($base !== '' ? $base : '') . '/' . $path;
}

function asset(string $path): string
{
    return url($path);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return is_string($value) ? $value : null;
}

function old(array $values, string $key, mixed $default = ''): mixed
{
    return array_key_exists($key, $values) ? $values[$key] : $default;
}

function slugify(string $value): string
{
    $value = trim($value);
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'lagu';
}

function unique_song_slug(string $requestedSlug, ?int $ignoreId = null): string
{
    $base = rtrim(substr(slugify($requestedSlug), 0, 180), '-');
    if ($base === '') {
        $base = 'lagu';
    }
    $slug = $base;
    $counter = 2;

    while (true) {
        $sql = 'SELECT id FROM songs WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }

        $statement = db()->prepare($sql . ' LIMIT 1');
        $statement->execute($params);
        if (!$statement->fetch()) {
            return $slug;
        }

        $suffix = '-' . $counter;
        $slug = rtrim(substr($base, 0, 180 - strlen($suffix)), '-') . $suffix;
        $counter++;
    }
}

function is_local_request(): bool
{
    $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $hostHeader = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    $hostName = strtolower((string) parse_url('http://' . $hostHeader, PHP_URL_HOST));

    return in_array($remoteAddress, ['127.0.0.1', '::1'], true)
        && in_array($hostName, ['localhost', '127.0.0.1', '::1'], true);
}

function youtube_id_from_input(?string $input): ?string
{
    $input = trim((string) $input);
    if ($input === '') {
        return null;
    }

    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input) === 1) {
        return $input;
    }

    $patterns = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~i',
        '~youtube(?:-nocookie)?\.com/(?:watch\?.*?v=|embed/|shorts/|live/)([A-Za-z0-9_-]{11})~i',
        '~[?&]v=([A-Za-z0-9_-]{11})~i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input, $matches) === 1) {
            return $matches[1];
        }
    }

    return null;
}

function youtube_thumbnail(?string $youtubeId, string $quality = 'hqdefault'): string
{
    if (!$youtubeId) {
        return 'https://images.unsplash.com/photo-1565804951749-2426372cdc74?auto=format&fit=crop&w=1800&q=82';
    }

    return 'https://i.ytimg.com/vi/' . rawurlencode($youtubeId) . '/' . $quality . '.jpg';
}

function is_http_url(?string $value): bool
{
    $value = trim((string) $value);
    if ($value === '' || filter_var($value, FILTER_VALIDATE_URL) === false) {
        return false;
    }

    return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function css_url(string $value): string
{
    return str_replace(
        ["\\", "'", "\r", "\n", "\f"],
        ["\\\\", "\\'", '', '', ''],
        $value
    );
}

function youtube_embed_url(string $youtubeId, bool $background = false): string
{
    $parameters = [
        'rel' => '0',
        'playsinline' => '1',
        'enablejsapi' => '1',
    ];

    if ($background) {
        $parameters += [
            'autoplay' => '1',
            'mute' => '1',
            'loop' => '1',
            'playlist' => $youtubeId,
            'controls' => '0',
            'modestbranding' => '1',
        ];
    }

    return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtubeId) . '?' . http_build_query($parameters);
}

function format_lyrics(?string $lyrics): string
{
    $lyrics = trim((string) $lyrics);
    if ($lyrics === '') {
        return '<p class="lyrics-empty">Lirik belum ditambahkan. Masuk ke panel admin untuk mengisinya.</p>';
    }

    $output = [];
    $lines = preg_split('/\R/u', $lyrics) ?: [];

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            $output[] = '<span class="lyric-space" aria-hidden="true"></span>';
            continue;
        }

        if (preg_match('/^\[[^\]]+\]$/u', $trimmed) === 1) {
            $output[] = '<span class="lyric-section">' . e(trim($trimmed, '[]')) . '</span>';
            continue;
        }

        $output[] = '<span class="lyric-line">' . e($line) . '</span>';
    }

    return implode("\n", $output);
}

function truncate_text(?string $text, int $length = 140): string
{
    $text = trim((string) $text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $length - 1)) . '…';
}

function current_path_is(string $needle): bool
{
    $path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($path, $needle);
}
