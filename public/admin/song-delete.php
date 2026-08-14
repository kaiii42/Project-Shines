<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();

if (!is_post()) {
    redirect('admin/index.php');
}

verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    flash('error', 'Permintaan hapus tidak valid.');
    redirect('admin/index.php');
}

$song = get_song_by_id((int) $id);
if (!$song) {
    flash('error', 'Lagu sudah tidak ditemukan.');
    redirect('admin/index.php');
}

$statement = db()->prepare('DELETE FROM songs WHERE id = :id');
$statement->execute(['id' => $id]);

if ((int) $song['is_featured'] === 1) {
    $replacement = get_featured_song();
    if ($replacement) {
        $feature = db()->prepare('UPDATE songs SET is_featured = 1 WHERE id = :id');
        $feature->execute(['id' => $replacement['id']]);
    }
}

flash('success', 'Lagu “' . $song['title'] . '” berhasil dihapus.');
redirect('admin/index.php');

