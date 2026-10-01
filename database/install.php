<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
// Run from CLI: php database/install.php
chdir(dirname(__DIR__));
require_once 'config/config.php';
$cfg = require 'config/database.php';

try {
    $opts = $cfg['options'] ?? [];
    $opts[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password'],
        $opts
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$cfg['dbname']}`");
    $pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $sql = preg_replace('/^CREATE DATABASE.*?;/im', '', $sql);
    $sql = preg_replace('/^USE.*?;/im', '', $sql);

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = 0; $skip = 0;
    foreach ($statements as $stmt) {
        if (!$stmt) continue;
        try {
            $pdo->exec($stmt);
            $ok++;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate')) {
                $skip++;
            } else {
                echo "WARN: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Done: $ok statements executed, $skip skipped.\n";

    $admin = $pdo->query("SELECT id, name, email, password_hash FROM users WHERE role='admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "Admin: {$admin['name']} ({$admin['email']})\n";
        if (empty($admin['password_hash'])) {
            $plain = rtrim(strtr(base64_encode(random_bytes(15)), '+/', '-_'), '=');
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($plain, PASSWORD_ARGON2ID), $admin['id']]);
            echo "Mot de passe admin généré (affiché une seule fois) : {$plain}\n";
        }
    }

    $products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "Products: $products\n";

    $categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    echo "Categories: $categories\n";

} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
