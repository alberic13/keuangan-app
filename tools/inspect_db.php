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

$db = $cfg['DB_DATABASE'];

function dumpTableColumns(PDO $pdo, string $db, string $table): void
{
    echo "== {$table} columns ==\n";
    $stmt = $pdo->prepare('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION');
    $stmt->execute([$db, $table]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo $row['COLUMN_NAME'] . "\t" . $row['COLUMN_TYPE'] . "\t" . $row['IS_NULLABLE'] . "\t" . ($row['COLUMN_DEFAULT'] ?? 'NULL') . "\n";
    }
    echo "\n";
}

foreach (['users', 'kategori_transaksi', 'transaksi', 'expenses', 'expense_categories', 'cash_accounts', 'cash_ledger_entries'] as $table) {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->execute([$db, $table]);
    $exists = (int) $stmt->fetchColumn() > 0;
    echo $table . ": " . ($exists ? "exists" : "missing") . "\n";
}

echo "\n";

foreach (['users', 'kategori_transaksi', 'transaksi'] as $table) {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->execute([$db, $table]);
    if ((int) $stmt->fetchColumn() > 0) {
        dumpTableColumns($pdo, $db, $table);
    }
}

