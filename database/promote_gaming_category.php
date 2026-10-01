<?php
/**
 * Promeut la catégorie Gaming en rubrique principale (top-level).
 * Usage : php database/promote_gaming_category.php
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';

$cfg = require 'config/database.php';
$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['database']),
    $cfg['username'],
    $cfg['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$gaming = $pdo->query("SELECT id, parent_id FROM categories WHERE slug = 'gaming' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$gaming) {
    fwrite(STDERR, "Catégorie gaming introuvable.\n");
    exit(1);
}

if ($gaming['parent_id'] === null) {
    echo "Gaming est déjà une rubrique principale.\n";
    exit(0);
}

$pdo->exec('UPDATE categories SET sort_order = sort_order + 1 WHERE parent_id IS NULL AND sort_order >= 3');
$st = $pdo->prepare("UPDATE categories SET parent_id = NULL, sort_order = 3, icon = 'bi-controller', description = ? WHERE id = ?");
$st->execute(['Recharges gaming, UC, Steam Wallet, Game Pass et plus', (int) $gaming['id']]);

echo "OK — Gaming promu en catégorie principale (id={$gaming['id']}).\n";
