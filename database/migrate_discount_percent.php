<?php
/**
 * One-shot: ajoute discount_percent aux produits si manquant.
 * Usage: php database/migrate_discount_percent.php
 */
require __DIR__ . '/../config/config.php';

$cfg = require __DIR__ . '/../config/database.php';
$pdo = new PDO(
    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
    $cfg['username'],
    $cfg['password'],
    $cfg['options']
);

$exists = $pdo->query("SHOW COLUMNS FROM products LIKE 'discount_percent'")->fetch();
if ($exists) {
    echo "OK — colonne discount_percent déjà présente.\n";
    exit(0);
}

$pdo->exec("ALTER TABLE products ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT NULL AFTER sale_price");
echo "OK — colonne discount_percent ajoutée.\n";
