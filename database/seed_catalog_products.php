<?php
/**
 * Ajoute les produits catalogue manquants (titres conseillés).
 * Usage : php database/seed_catalog_products.php
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';

if (!function_exists('slug')) {
    require_once 'app/Helpers/functions.php';
}

$cfg = require 'config/database.php';

$catalog = [
    'streaming-video' => [
        'name' => 'Streaming vidéo',
        'description' => 'Abonnements streaming Netflix, Disney+, Prime Video et plus',
        'icon' => 'bi-play-btn',
        'parent_slug' => 'abonnements',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Compte personnel Netflix', 'type' => 'account', 'price' => 1500],
            ['name' => 'Compte personnel Disney+', 'type' => 'account', 'price' => 1200],
            ['name' => 'Compte personnel Prime Video', 'type' => 'account', 'price' => 1100],
            ['name' => 'Compte personnel Shahid VIP', 'type' => 'account', 'price' => 900],
            ['name' => 'Compte personnel OSN+', 'type' => 'account', 'price' => 1300],
            ['name' => 'Compte personnel Apple TV+', 'type' => 'account', 'price' => 1000],
            ['name' => 'Compte personnel Crunchyroll', 'type' => 'account', 'price' => 800],
            ['name' => 'Compte personnel YouTube Premium', 'type' => 'account', 'price' => 1400],
        ],
    ],
    'intelligence-artificielle' => [
        'name' => 'Intelligence artificielle',
        'description' => 'Comptes IA : ChatGPT, Claude, Gemini, Midjourney…',
        'icon' => 'bi-robot',
        'parent_slug' => 'comptes-premium',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Compte personnel ChatGPT Plus', 'type' => 'account', 'price' => 2500],
            ['name' => 'Compte personnel Claude Pro', 'type' => 'account', 'price' => 2800],
            ['name' => 'Compte personnel Gemini Advanced', 'type' => 'account', 'price' => 2400],
            ['name' => 'Compte personnel Perplexity Pro', 'type' => 'account', 'price' => 2200],
            ['name' => 'Compte personnel Canva Pro', 'type' => 'subscription', 'price' => 1800],
            ['name' => 'Compte personnel Poe Premium', 'type' => 'account', 'price' => 2000],
            ['name' => 'Compte personnel Midjourney', 'type' => 'account', 'price' => 3500],
            ['name' => 'Compte personnel Leonardo AI', 'type' => 'account', 'price' => 2600],
        ],
    ],
    'design-productivite' => [
        'name' => 'Design & productivité',
        'description' => 'Outils créatifs et productivité professionnelle',
        'icon' => 'bi-palette',
        'parent_slug' => 'saas-tools',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Compte personnel CapCut Pro', 'type' => 'subscription', 'price' => 1500],
            ['name' => 'Compte personnel Adobe', 'type' => 'subscription', 'price' => 4500],
            ['name' => 'Accès Photoshop Premium', 'type' => 'software', 'price' => 3200],
            ['name' => 'Accès Illustrator Premium', 'type' => 'software', 'price' => 3200],
            ['name' => 'Compte personnel Envato', 'type' => 'subscription', 'price' => 2800],
            ['name' => 'Compte personnel Freepik', 'type' => 'subscription', 'price' => 1600],
            ['name' => 'Compte personnel Flaticon', 'type' => 'subscription', 'price' => 1200],
        ],
    ],
    'bureautique-logiciels' => [
        'name' => 'Bureautique & logiciels',
        'description' => 'Microsoft 365, Windows, Office et stockage cloud',
        'icon' => 'bi-window',
        'parent_slug' => 'logiciels',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Compte personnel Microsoft 365', 'type' => 'subscription', 'price' => 3500],
            ['name' => 'Licence Office', 'type' => 'software', 'price' => 7500],
            ['name' => 'Clé Windows 10 Pro', 'type' => 'software', 'price' => 4000],
            ['name' => 'Clé Windows 11 Pro', 'type' => 'software', 'price' => 4500],
            ['name' => 'Compte personnel Google One', 'type' => 'subscription', 'price' => 1800],
            ['name' => 'Stockage OneDrive', 'type' => 'subscription', 'price' => 1500],
            ['name' => 'Compte personnel Dropbox', 'type' => 'subscription', 'price' => 2000],
            ['name' => 'Compte personnel Notion', 'type' => 'subscription', 'price' => 2200],
        ],
    ],
    'musique-audio' => [
        'name' => 'Musique & audio',
        'description' => 'Spotify, Deezer, Apple Music et services audio',
        'icon' => 'bi-music-note-beamed',
        'parent_slug' => 'abonnements',
        'sort_order' => 2,
        'products' => [
            ['name' => 'Compte personnel Spotify', 'type' => 'account', 'price' => 900],
            ['name' => 'Compte personnel Deezer', 'type' => 'account', 'price' => 850],
            ['name' => 'Compte personnel Apple Music', 'type' => 'account', 'price' => 950],
            ['name' => 'Compte personnel Anghami', 'type' => 'account', 'price' => 700],
            ['name' => 'Compte personnel SoundCloud', 'type' => 'account', 'price' => 800],
            ['name' => 'Compte personnel Tidal', 'type' => 'account', 'price' => 1100],
        ],
    ],
    'gaming' => [
        'name' => 'Gaming',
        'description' => 'Abonnements, cartes cadeaux et recharges jeux',
        'icon' => 'bi-controller',
        'parent_slug' => 'cartes-cadeaux',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Abonnement PlayStation Plus', 'type' => 'subscription', 'price' => 3500],
            ['name' => 'Compte Xbox Game Pass', 'type' => 'subscription', 'price' => 3200],
            ['name' => 'Carte Steam Wallet', 'type' => 'gift_card', 'price' => 2000],
            ['name' => 'Crédit Epic Games', 'type' => 'gift_card', 'price' => 2000],
            ['name' => 'Compte Nintendo Online', 'type' => 'subscription', 'price' => 1800],
            ['name' => 'Compte EA Play', 'type' => 'subscription', 'price' => 2200],
            ['name' => 'Carte Roblox', 'type' => 'gift_card', 'price' => 1500],
            ['name' => 'Recharge PUBG UC', 'type' => 'top_up', 'price' => 1200],
            ['name' => 'Recharge Free Fire', 'type' => 'top_up', 'price' => 1000],
            ['name' => 'Recharge V-Bucks', 'type' => 'top_up', 'price' => 1500],
        ],
    ],
    'apprentissage-formations' => [
        'name' => 'Apprentissage & formations',
        'description' => 'Plateformes e-learning et formations en ligne',
        'icon' => 'bi-mortarboard',
        'parent_slug' => 'saas-tools',
        'sort_order' => 2,
        'products' => [
            ['name' => 'Compte Coursera Plus', 'type' => 'subscription', 'price' => 4000],
            ['name' => 'Formation Udemy', 'type' => 'service', 'price' => 2500],
            ['name' => 'Compte Skillshare', 'type' => 'subscription', 'price' => 2200],
            ['name' => 'Compte LinkedIn Learning', 'type' => 'subscription', 'price' => 3500],
            ['name' => 'Compte Duolingo Super', 'type' => 'subscription', 'price' => 1200],
            ['name' => 'Compte Busuu Premium', 'type' => 'subscription', 'price' => 1400],
        ],
    ],
    'securite-vpn' => [
        'name' => 'Sécurité & VPN',
        'description' => 'VPN, antivirus et protection numérique',
        'icon' => 'bi-shield-lock',
        'parent_slug' => 'securite',
        'sort_order' => 1,
        'products' => [
            ['name' => 'Compte NordVPN', 'type' => 'subscription', 'price' => 6000],
            ['name' => 'Compte ExpressVPN', 'type' => 'subscription', 'price' => 6500],
            ['name' => 'Compte Surfshark', 'type' => 'subscription', 'price' => 4500],
            ['name' => 'Compte CyberGhost', 'type' => 'subscription', 'price' => 4000],
            ['name' => 'Licence Bitdefender', 'type' => 'software', 'price' => 3500],
            ['name' => 'Licence Kaspersky', 'type' => 'software', 'price' => 3200],
            ['name' => 'Licence Avast Premium', 'type' => 'software', 'price' => 2800],
        ],
    ],
];

/** Alias pour détecter les produits déjà présents sous un autre intitulé */
$existingAliases = [
    'Compte personnel Netflix' => ['netflix'],
    'Compte personnel Spotify' => ['spotify'],
    'Compte personnel Canva Pro' => ['canva'],
    'Compte NordVPN' => ['nordvpn'],
    'Licence Office' => ['microsoft office', 'office 202'],
    'Compte personnel Microsoft 365' => ['microsoft 365', 'office 365'],
];

function normalizeName(string $name): string {
    $name = mb_strtolower($name);
    $name = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name);
    return trim(preg_replace('/\s+/', ' ', $name));
}

function productExists(array $existingProducts, string $targetName, array $aliases): bool {
    $target = normalizeName($targetName);
    $needles = array_merge([$target], $aliases[$targetName] ?? []);

    foreach ($existingProducts as $row) {
        $normalized = normalizeName($row['name']);
        foreach ($needles as $needle) {
            $needle = normalizeName($needle);
            if ($needle === '' || $normalized === '') {
                continue;
            }
            if ($normalized === $needle || str_contains($normalized, $needle) || str_contains($needle, $normalized)) {
                return true;
            }
        }
    }
    return false;
}

function uniqueSlug(PDO $pdo, string $name): string {
    $base = slug($name);
    $slug = $base;
    $i = 0;
    $check = $pdo->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
    while (true) {
        $check->execute([$slug]);
        if (!$check->fetch()) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
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

    $parents = [];
    foreach ($pdo->query('SELECT id, slug FROM categories') as $row) {
        $parents[$row['slug']] = (int) $row['id'];
    }

    $existingProducts = $pdo->query('SELECT id, name, slug FROM products')->fetchAll(PDO::FETCH_ASSOC);

    $catInsert = $pdo->prepare(
        'INSERT INTO categories (name, slug, description, icon, parent_id, sort_order, is_active)
         VALUES (?, ?, ?, ?, ?, ?, 1)'
    );
    $catSelect = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
    $prodInsert = $pdo->prepare(
        'INSERT INTO products (category_id, name, slug, short_desc, description, type, price, status, featured, delivery_type)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)'
    );

    $addedCategories = 0;
    $addedProducts = 0;
    $skippedProducts = 0;
    $renamedProducts = 0;

    $renameMap = [
        'Netflix Premium 1 Mois' => 'Compte personnel Netflix',
        'Spotify Premium 3 Mois' => 'Compte personnel Spotify',
        'Canva Pro 1 An' => 'Compte personnel Canva Pro',
        'NordVPN 1 An' => 'Compte NordVPN',
        'Microsoft Office 2024' => 'Licence Office',
    ];

    $renameStmt = $pdo->prepare('UPDATE products SET name = ? WHERE id = ?');
    foreach ($existingProducts as &$row) {
        if (isset($renameMap[$row['name']])) {
            $newName = $renameMap[$row['name']];
            $renameStmt->execute([$newName, $row['id']]);
            echo "Renommé : « {$row['name']} » → « {$newName} »\n";
            $row['name'] = $newName;
            $renamedProducts++;
        }
    }
    unset($row);

    foreach ($catalog as $slug => $group) {
        $parentId = $parents[$group['parent_slug']] ?? null;
        if (!$parentId) {
            echo "WARN: parent « {$group['parent_slug']} » introuvable pour « {$group['name']} »\n";
            continue;
        }

        $catSelect->execute([$slug]);
        $categoryId = (int) ($catSelect->fetchColumn() ?: 0);
        if (!$categoryId) {
            $catInsert->execute([
                $group['name'],
                $slug,
                $group['description'],
                $group['icon'],
                $parentId,
                $group['sort_order'],
            ]);
            $categoryId = (int) $pdo->lastInsertId();
            $addedCategories++;
            echo "Catégorie créée : {$group['name']}\n";
        }

        foreach ($group['products'] as $product) {
            if (productExists($existingProducts, $product['name'], $existingAliases)) {
                echo "Ignoré (existe) : {$product['name']}\n";
                $skippedProducts++;
                continue;
            }

            $delivery = in_array($product['type'], ['gift_card', 'software'], true) ? 'instant' : 'instant';
            $shortDesc = $product['name'] . ' — livraison instantanée.';
            $description = '<p>' . e($product['name']) . '. Service digital livré rapidement après validation de commande.</p>';
            $productSlug = uniqueSlug($pdo, $product['name']);

            $prodInsert->execute([
                $categoryId,
                $product['name'],
                $productSlug,
                $shortDesc,
                $description,
                $product['type'],
                $product['price'],
                'active',
                $delivery,
            ]);

            $existingProducts[] = ['id' => (int) $pdo->lastInsertId(), 'name' => $product['name'], 'slug' => $productSlug];
            echo "Ajouté : {$product['name']}\n";
            $addedProducts++;
        }
    }

    echo "\nRésumé : {$addedCategories} catégorie(s), {$addedProducts} produit(s) ajouté(s), {$skippedProducts} ignoré(s), {$renamedProducts} renommé(s).\n";
} catch (PDOException $e) {
    echo 'ERREUR : ' . $e->getMessage() . "\n";
    exit(1);
}
