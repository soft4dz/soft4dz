<?php
/**
 * Corrige les textes UTF-8 endommagés (ex. « sécurité » → « s??curit?? »)
 * après import SQL sans connexion utf8mb4.
 *
 * Usage : php database/fix_encoding.php
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';

$cfg = require 'config/database.php';

/** Corrections explicites par clé produit (slug) */
$productFixes = [
    'netflix-premium-1-mois' => [
        'short_desc' => 'Compte Netflix 4K Ultra HD partagé valable 1 mois',
        'description' => '<p>Profitez de Netflix Premium avec streaming 4K Ultra HD.</p>',
    ],
    'spotify-premium-3-mois' => [
        'short_desc' => 'Accès Spotify Premium sans publicité pendant 3 mois',
        'description' => '<p>Musique illimitée sans publicité, hors ligne.</p>',
    ],
    'microsoft-office-2024' => [
        'short_desc' => 'Clé de licence Microsoft Office 2024 Pro Plus',
        'description' => '<p>Suite Office complète avec Word, Excel, PowerPoint.</p>',
    ],
    'canva-pro-1-an' => [
        'short_desc' => 'Accès Canva Pro avec toutes les fonctionnalités premium',
        'description' => '<p>Design professionnel avec Canva Pro.</p>',
    ],
    'nordvpn-1-an' => [
        'description' => '<p>Naviguez en toute sécurité avec NordVPN.</p>',
    ],
];

$categoryFixes = [
    'comptes-premium' => ['Comptes Premium', 'Comptes prémium partagés ou dédiés'],
    'securite'          => ['Sécurité', 'VPN, antivirus et outils de sécurité'],
];

$settingFixes = [
    'site_tagline' => 'Votre marketplace digital en Algérie',
];

/** Remplacements génériques pour les restes « ?? » */
$genericReplacements = [
    'partag??'        => 'partagé',
    'Acc??s'          => 'Accès',
    'acc??s'          => 'accès',
    'publicit??'      => 'publicité',
    'illimit??e'      => 'illimitée',
    'Cl??'            => 'Clé',
    'cl??'            => 'clé',
    'compl??te'       => 'complète',
    'fonctionnalit??s'=> 'fonctionnalités',
    's??curit??'      => 'sécurité',
    'S??curit??'      => 'Sécurité',
    'pr??mium'        => 'prémium',
    'Pr??mium'        => 'Prémium',
    'Alg??rie'        => 'Algérie',
    'd??di??s'        => 'dédiés',
    'D??di??'         => 'Dédié',
    'cr??ation'       => 'création',
    'Cr??ation'       => 'Création',
    'r??ponse'        => 'réponse',
    'R??ponse'        => 'Réponse',
    'r??u'            => 'reçu',
    'Re??u'           => 'Reçu',
    'Num??ro'         => 'Numéro',
    'num??ro'         => 'numéro',
    'T??l??phone'     => 'Téléphone',
    't??l??phone'     => 'téléphone',
    'D??connexion'    => 'Déconnexion',
    'd??connexion'    => 'déconnexion',
];

function applyGenericFixes(string $text, array $map): string {
    return str_replace(array_keys($map), array_values($map), $text);
}

function looksBroken(string $text): bool {
    return (bool) preg_match('/\?\?|Ã|â€™|â€|�/', $text);
}

try {
    $opts = $cfg['options'] ?? [];
    $opts[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password'],
        $opts
    );

    $pdo->exec("ALTER DATABASE `{$cfg['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    echo "Base et tables converties en utf8mb4.\n";

    $updated = 0;

    $prodStmt = $pdo->prepare('UPDATE products SET short_desc = ?, description = ? WHERE slug = ?');
    foreach ($productFixes as $slug => $fields) {
        $row = $pdo->prepare('SELECT short_desc, description FROM products WHERE slug = ?');
        $row->execute([$slug]);
        $current = $row->fetch(PDO::FETCH_ASSOC);
        if (!$current) {
            continue;
        }
        $short = $fields['short_desc'] ?? $current['short_desc'];
        $desc  = $fields['description'] ?? $current['description'];
        $prodStmt->execute([$short, $desc, $slug]);
        if ($prodStmt->rowCount() > 0) {
            echo "Produit corrigé : $slug\n";
            $updated++;
        }
    }

    $catStmt = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE slug = ?');
    foreach ($categoryFixes as $slug => [$name, $desc]) {
        $catStmt->execute([$name, $desc, $slug]);
        if ($catStmt->rowCount() > 0) {
            echo "Catégorie corrigée : $slug\n";
            $updated++;
        }
    }

    $setStmt = $pdo->prepare('UPDATE settings SET value = ? WHERE `key` = ?');
    foreach ($settingFixes as $key => $value) {
        $setStmt->execute([$value, $key]);
        if ($setStmt->rowCount() > 0) {
            echo "Paramètre corrigé : $key\n";
            $updated++;
        }
    }

    $textColumns = [
        'products'     => ['name', 'short_desc', 'description', 'meta_title', 'meta_desc'],
        'categories'   => ['name', 'description'],
        'settings'     => ['value'],
        'blog_posts'   => ['title', 'excerpt', 'content', 'meta_title', 'meta_desc'],
        'services'     => ['title', 'description', 'short_desc'],
        'users'        => ['name'],
        'orders'       => ['notes'],
        'tickets'      => ['subject', 'body'],
        'ticket_messages' => ['body'],
        'notifications'=> ['title', 'body'],
    ];

    foreach ($textColumns as $table => $columns) {
        try {
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            continue;
        }

        foreach ($rows as $row) {
            $changes = [];
            foreach ($columns as $col) {
                if (!array_key_exists($col, $row)) {
                    continue;
                }
                $val = (string) ($row[$col] ?? '');
                if ($val === '' || !looksBroken($val)) {
                    continue;
                }
                $fixed = applyGenericFixes($val, $genericReplacements);
                if ($fixed !== $val) {
                    $changes[$col] = $fixed;
                }
            }

            if (!$changes) {
                continue;
            }

            $sets = implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($changes)));
            $params = array_merge(array_values($changes), [$row['id']]);
            $pdo->prepare("UPDATE `$table` SET $sets WHERE id = ?")->execute($params);
            echo "Auto-corrigé $table#{$row['id']}\n";
            $updated++;
        }
    }

    echo "\nTerminé — $updated enregistrement(s) mis à jour.\n";
} catch (PDOException $e) {
    echo 'ERREUR : ' . $e->getMessage() . "\n";
    exit(1);
}
