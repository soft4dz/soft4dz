<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ProductController extends Controller {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 24;
        $category = $_GET['category'] ?? '';
        $sort     = $_GET['sort'] ?? 'popular';
        $minPrice = $_GET['min_price'] ?? '';
        $maxPrice = $_GET['max_price'] ?? '';
        $search   = trim($_GET['q'] ?? '');
        $type     = $_GET['type'] ?? '';
        $onSale   = !empty($_GET['on_sale']);

        $where  = ["p.status = 'active'"];
        $params = [];

        if ($search) {
            $where[]  = "MATCH(p.name, p.short_desc) AGAINST(? IN BOOLEAN MODE)";
            $params[] = $search . '*';
        }
        if ($category) {
            $catIds = categoryFilterIds($category);
            if ($catIds) {
                $placeholders = implode(',', array_fill(0, count($catIds), '?'));
                $where[]      = "p.category_id IN ($placeholders)";
                $params       = array_merge($params, $catIds);
            } else {
                $where[] = '1=0';
            }
        }
        if ($type && array_key_exists($type, productTypes())) {
            $where[]  = "p.type = ?";
            $params[] = $type;
        } elseif ($type) {
            $type = '';
        }
        if ($onSale) {
            $where[] = 'p.sale_price IS NOT NULL AND p.sale_price > 0 AND p.sale_price < p.price';
        }
        if ($minPrice !== '') {
            $where[]  = "COALESCE(p.sale_price, p.price) >= ?";
            $params[] = (float) $minPrice;
        }
        if ($maxPrice !== '') {
            $where[]  = "COALESCE(p.sale_price, p.price) <= ?";
            $params[] = (float) $maxPrice;
        }

        $orderBy = match ($sort) {
            'newest'    => 'p.created_at DESC',
            'price_asc' => 'COALESCE(p.sale_price, p.price) ASC',
            'price_desc'=> 'COALESCE(p.sale_price, p.price) DESC',
            'rating'    => 'p.rating_avg DESC',
            default     => 'p.sales_count DESC',
        };

        $whereStr = implode(' AND ', $where);
        $sql      = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     WHERE $whereStr
                     ORDER BY $orderBy";

        $result = $this->db->paginate($sql, $params, $page, $perPage);

        $categoryTree = categoryTree(true);

        $this->view('products.index', array_merge($result, [
            'categories'   => $categoryTree,
            'categoryTree' => $categoryTree,
            'filters'      => compact('search', 'category', 'sort', 'minPrice', 'maxPrice', 'type', 'onSale'),
        ]));
    }

    public function show(string $slug): void {
        $product = $this->db->fetch(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    v.store_name AS vendor_name, v.store_slug AS vendor_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN vendor_profiles v ON v.user_id = p.vendor_id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );

        if (!$product) $this->abort(404);

        // Track view
        $this->db->query("UPDATE products SET views_count = views_count + 1 WHERE id = ?", [$product['id']]);

        $reviews = $this->db->fetchAll(
            "SELECT r.*, u.name AS user_name, u.avatar
             FROM product_reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.product_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC
             LIMIT 10",
            [$product['id']]
        );

        $related = $this->db->fetchAll(
            "SELECT id, name, slug, price, sale_price, image, rating_avg
             FROM products
             WHERE category_id = ? AND id != ? AND status = 'active'
             ORDER BY sales_count DESC LIMIT 4",
            [$product['category_id'], $product['id']]
        );

        $this->view('products.show', compact('product', 'reviews', 'related'));
    }

    public function byCategory(string $slug): void {
        $_GET['category'] = $slug;
        $this->index();
    }
}
