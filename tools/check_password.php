<?php

declare(strict_types=1);

use Illuminate\Hashing\BcryptHasher;

require __DIR__ . '/../vendor/autoload.php';

$login = $argv[1] ?? null;
$plain = $argv[2] ?? null;

if (!is_string($login) || $login === '' || !is_string($plain)) {
    fwrite(STDERR, "Usage: php tools/check_password.php <username-or-email> <plain>\n");
    exit(2);
}

$cfg = parse_ini_file(__DIR__ . '/../.env', false, INI_SCANNER_RAW);
if (!is_array($cfg)) {
    fwrite(STDERR, "Failed to read .env\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $cfg['DB_HOST'], $cfg['DB_PORT'], $cfg['DB_DATABASE']);
$pdo = new PDO($dsn, $cfg['DB_USERNAME'], $cfg['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$login, $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    fwrite(STDERR, "User not found\n");
    exit(1);
}

$hasher = new BcryptHasher();
$ok = $hasher->check($plain, $user['password']);

echo ($ok ? "OK\n" : "NO\n");

