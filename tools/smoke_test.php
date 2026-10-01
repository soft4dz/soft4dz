<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
chdir(dirname(__DIR__));
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
spl_autoload_register(function (string $class): void {
    $file = ROOT_PATH . '/' . str_replace(['App\\', '\\'], ['app/', '/'], $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$errors = [];

try {
    $db = App\Core\Database::getInstance();
} catch (Throwable $e) {
    echo "FAIL DB connect: " . $e->getMessage() . "\n";
    exit(1);
}

$tests = [
    'home_featured' => function () use ($db) {
        $db->fetchAll("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.status = 'active' AND p.featured = 1 ORDER BY p.sales_count DESC LIMIT 6");
    },
    'home_categories' => function () use ($db) {
        $db->fetchAll(
            "SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active' WHERE c.is_active = 1 GROUP BY c.id ORDER BY c.sort_order"
        );
    },
    'home_posts' => function () use ($db) {
        $db->fetchAll("SELECT id, title, slug FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 3");
    },
    'update_mixed_params' => function () use ($db) {
        $db->update('orders', ['notes' => null], 'id = ?', [0]);
    },
];

foreach ($tests as $name => $fn) {
    try {
        $fn();
        echo "OK  $name\n";
    } catch (Throwable $e) {
        echo "FAIL $name: " . $e->getMessage() . "\n";
        $errors[] = $name;
    }
}

exit($errors ? 1 : 0);
