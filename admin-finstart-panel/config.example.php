<?php
declare(strict_types=1);

const ADMIN_LOGIN = 'Admin';
const ADMIN_PASSWORD_SHA256 = 'PUT_SHA256_PASSWORD_HASH_HERE';
const DATA_FILE = __DIR__ . '/../data/site.json';
const BACKUP_DIR = __DIR__ . '/../data/backups';

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
