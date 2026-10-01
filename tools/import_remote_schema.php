<?php
/** Import schema vers MySQL cPanel (usage unique). */
chdir(dirname(__DIR__));
require_once 'config/config.php';

$cfg = require 'config/database.php';
$cfg['host']     = 'soft4dz.com';
$cfg['dbname']   = 'softdzco_soft4dz';
$cfg['username'] = 'softdzco_soft4dz';
$cfg['password'] = getenv('REMOTE_DB_PASS') ?: '';

if ($cfg['password'] === '') {
    fwrite(STDERR, "REMOTE_DB_PASS manquant\n");
    exit(1);
}

$opts = $cfg['options'];
$opts[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

$pdo = new PDO(
    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4",
    $cfg['username'],
    $cfg['password'],
    $opts
);

$sql = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
$sql = preg_replace('/^CREATE DATABASE.*?;/im', '', $sql);
$sql = preg_replace('/^USE.*?;/im', '', $sql);

$ok = 0;
$skip = 0;
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if (!$stmt || str_starts_with($stmt, '--')) {
        continue;
    }
    try {
        $pdo->exec($stmt);
        $ok++;
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate')) {
            $skip++;
        } else {
            throw $e;
        }
    }
}

echo "Import OK: $ok executed, $skip skipped\n";
echo 'Products: ' . $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() . "\n";
echo 'Admin: ' . $pdo->query("SELECT email FROM users WHERE role='admin' LIMIT 1")->fetchColumn() . "\n";
