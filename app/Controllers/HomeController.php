<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller {
    public function index(): void {
        $db = Database::getInstance();

        $promoSetting = $db->fetch("SELECT value FROM settings WHERE `key` = 'home_promo_product_ids' LIMIT 1");
        $promoIds = [];
        if (!empty($promoSetting['value'])) {
            $decoded = json_decode((string) $promoSetting['value'], true);
            if (is_array($decoded)) {
                $promoIds = array_values(array_unique(array_filter(array_map('intval', $decoded), static fn($id) => $id > 0)));
            }
        }

        $promoProducts = [];
        if (!empty($promoIds)) {
            $placeholders = implode(',', array_fill(0, count($promoIds), '?'));
            $rows = $db->fetchAll(
                "SELECT p.*, c.name AS category_name
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 WHERE p.status = 'active' AND p.id IN ($placeholders) AND p.sale_price IS NOT NULL AND p.sale_price > 0
                 LIMIT 30",
                $promoIds
            );
            $byId = [];
            foreach ($rows as $row) {
                $byId[(int) $row['id']] = $row;
            }
            foreach ($promoIds as $id) {
                if (isset($byId[$id])) {
                    $promoProducts[] = $byId[$id];
                }
            }
            $promoProducts = array_slice($promoProducts, 0, 6);
        }

        if (empty($promoProducts)) {
            $promoProducts = $db->fetchAll(
                "SELECT p.*, c.name AS category_name
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 WHERE p.status = 'active' AND p.sale_price IS NOT NULL AND p.sale_price > 0
                 ORDER BY (p.price - p.sale_price) DESC, p.sales_count DESC
                 LIMIT 6"
            );
        }

        $featured = $db->fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND p.featured = 1
             ORDER BY p.sales_count DESC
             LIMIT 6"
        );

        $categories = $db->fetchAll(
            "SELECT c.*, COUNT(p.id) AS product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
             WHERE c.is_active = 1 AND c.parent_id IS NULL
             GROUP BY c.id
             ORDER BY c.sort_order"
        );

        $stats = [
            'products' => $db->count("SELECT COUNT(*) FROM products WHERE status = 'active'"),
            'users'    => $db->count("SELECT COUNT(*) FROM users WHERE role = 'customer'"),
            'orders'   => $db->count("SELECT COUNT(*) FROM orders WHERE status = 'completed'"),
        ];

        $latestPosts = $db->fetchAll(
            "SELECT id, title, slug, excerpt, image, published_at
             FROM blog_posts
             WHERE status = 'published'
             ORDER BY published_at DESC
             LIMIT 3"
        );

        $rowsByCat = $db->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.id AS category_id, c.sort_order AS cat_sort
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id AND c.is_active = 1
             WHERE p.status = 'active'
             ORDER BY c.sort_order ASC, p.sales_count DESC, p.id DESC"
        );
        $bucket = [];
        foreach ($rowsByCat as $row) {
            $cid = (int) $row['category_id'];
            if (!isset($bucket[$cid])) {
                $bucket[$cid] = [];
            }
            if (count($bucket[$cid]) < 4) {
                $bucket[$cid][] = $row;
            }
        }
        $productsByCategory = [];
        foreach ($categories as $cat) {
            $cid = (int) $cat['id'];
            if (!empty($bucket[$cid])) {
                $productsByCategory[] = [
                    'category' => $cat,
                    'products' => $bucket[$cid],
                ];
            }
        }

        $this->view('home.index', compact('featured', 'promoProducts', 'categories', 'stats', 'latestPosts', 'productsByCategory'));
    }
}
