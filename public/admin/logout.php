<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/bootstrap.php';

if (!is_post()) {
    redirect('admin/index.php');
}

verify_csrf();
logout_user();
redirect('admin/login.php');

