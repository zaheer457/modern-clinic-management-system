<?php

require_once __DIR__ . '/functions.php';

function require_admin(): void
{
    $user = current_user();

    if (!$user || ($user['role'] ?? '') !== 'admin') {
        redirect('/clinic-management-system/login.php');
    }
}
