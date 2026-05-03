<?php

declare(strict_types=1);

$envPath = __DIR__ . '/../.env';
$cfg = parse_ini_file($envPath, false, INI_SCANNER_RAW);

if (!is_array($cfg)) {
    fwrite(STDERR, "Failed to read .env at {$envPath}\n");
    exit(1);
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s',
    $cfg['DB_HOST'] ?? '127.0.0.1',
    $cfg['DB_PORT'] ?? '3306',
    $cfg['DB_DATABASE'] ?? ''
);

$pdo = new PDO($dsn, $cfg['DB_USERNAME'] ?? 'root', $cfg['DB_PASSWORD'] ?? '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$rows = $pdo->query('SELECT id, name, username, email, is_active FROM users ORDER BY id ASC')
    ->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo implode("\t", [
        $row['id'],
        $row['username'] ?: '-',
        $row['email'] ?: '-',
        $row['name'] ?: '-',
        (string) $row['is_active'],
    ]) . PHP_EOL;
}

