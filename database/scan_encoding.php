<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
chdir(dirname(__DIR__));
require 'config/config.php';
$cfg = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8mb4",
    $cfg['username'],
    $cfg['password'],
    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"]
);

$tables = [
    'categories' => ['name', 'description'],
    'products' => ['name', 'short_desc', 'description'],
    'settings' => ['value'],
    'blog_posts' => ['title', 'excerpt', 'content'],
    'services' => ['title', 'description'],
];

foreach ($tables as $table => $cols) {
    try {
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        continue;
    }
    foreach ($rows as $row) {
        foreach ($cols as $col) {
            $v = (string) ($row[$col] ?? '');
            if ($v === '') continue;
            if (preg_match('/\?\?|Ã|â€™|â€|�/', $v)) {
                echo "$table#{$row['id']}.$col => $v\n";
            }
        }
    }
}
