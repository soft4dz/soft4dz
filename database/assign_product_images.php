<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
/**
 * Associe les visuels de assets/images/products/ aux produits (correspondance par nom,
 * définie dans tools/product-images/catalog.json).
 * Par défaut, seuls les produits sans image sont modifiés ; --force remplace aussi les images existantes.
 * Usage : php database/assign_product_images.php [--force]
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';
$cfg = require 'config/database.php';

$force   = in_array('--force', $argv, true);
$catalog = json_decode((string) file_get_contents('tools/product-images/catalog.json'), true);
if (!is_array($catalog['products'] ?? null)) {
    echo "Catalogue introuvable ou invalide : tools/product-images/catalog.json\n";
    exit(1);
}

$normalize = static fn(string $name): string => mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
$byName = [];
foreach ($catalog['products'] as $entry) {
    $file = $entry['file'] . '.webp';
    if (!is_file('assets/images/products/' . $file)) {
        echo "WARN : visuel manquant assets/images/products/{$file} (lancez tools/product-images/generate.mjs)\n";
        continue;
    }
    foreach ($entry['names'] as $name) {
        $byName[$normalize($name)] = $file;
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password'],
        $cfg['options']
    );
    $update = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
    $assigned = $kept = 0;
    $unmatched = [];
    foreach ($pdo->query('SELECT id, name, image FROM products ORDER BY id') as $product) {
        $file = $byName[$normalize($product['name'])] ?? null;
        if ($file === null) {
            $unmatched[] = $product['name'];
            continue;
        }
        if (!empty($product['image']) && !$force) {
            $kept++;
            continue;
        }
        $update->execute([$file, $product['id']]);
        $assigned++;
    }
    echo "Visuels associés : {$assigned} — images existantes conservées : {$kept}\n";
    if ($unmatched) {
        echo 'Sans visuel dans le catalogue (' . count($unmatched) . ') : ' . implode(', ', $unmatched) . "\n";
    }
} catch (PDOException $e) {
    echo 'ERREUR : ' . $e->getMessage() . "\n";
    exit(1);
}
