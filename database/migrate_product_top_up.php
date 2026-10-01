<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
/**
 * Ajoute le type de sortie top_up aux produits.
 * Usage : php database/migrate_product_top_up.php
 */
require __DIR__ . '/../config/config.php';

$cfg = require __DIR__ . '/../config/database.php';
$pdo = new PDO(
    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
    $cfg['username'],
    $cfg['password'],
    $cfg['options']
);

$pdo->exec("ALTER TABLE products
    MODIFY COLUMN `type` ENUM(
        'subscription','saas','gift_card','software','account','service','top_up'
    ) NOT NULL DEFAULT 'software'");

$updated = $pdo->exec("UPDATE products SET type = 'top_up'
    WHERE type = 'gift_card'
      AND (
        slug LIKE '%pubg%'
        OR slug LIKE '%free-fire%'
        OR slug LIKE '%v-bucks%'
        OR name LIKE '%Recharge%'
      )");

echo "OK — type top_up ajouté. Produits reclassés en top_up : " . (int)$updated . "\n";
