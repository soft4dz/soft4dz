<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class AdminController extends Controller {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requireAdmin();
    }

    public function index(): void {
        $today = date('Y-m-d');
        $stats = [
            'revenue_today'  => (float)($this->db->fetch("SELECT COALESCE(SUM(total),0) AS v FROM orders WHERE payment_status='paid' AND DATE(paid_at)=?", [$today])['v'] ?? 0),
            'revenue_month'  => (float)($this->db->fetch("SELECT COALESCE(SUM(total),0) AS v FROM orders WHERE payment_status='paid' AND DATE_FORMAT(paid_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")['v'] ?? 0),
            'orders_today'   => $this->db->count("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=?", [$today]),
            'orders_month'   => $this->db->count("SELECT COUNT(*) FROM orders WHERE DATE_FORMAT(created_at,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')"),
            'total_users'    => $this->db->count("SELECT COUNT(*) FROM users WHERE role='customer'"),
            'new_users_week' => $this->db->count("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'pending_orders' => $this->db->count("SELECT COUNT(*) FROM orders WHERE payment_status='pending'"),
            'open_tickets'   => $this->db->count("SELECT COUNT(*) FROM tickets WHERE status='open'"),
            'total_products' => $this->db->count("SELECT COUNT(*) FROM products WHERE status='active'"),
        ];

        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, u.name AS customer FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        $chartData = $this->db->fetchAll(
            "SELECT DATE(created_at) AS date, SUM(total) AS revenue, COUNT(*) AS orders
             FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND payment_status='paid'
             GROUP BY DATE(created_at) ORDER BY date ASC"
        );

        $topProducts = $this->db->fetchAll(
            "SELECT name, sales_count, price FROM products ORDER BY sales_count DESC LIMIT 5"
        );

        $this->view('admin.index', compact('stats', 'recentOrders', 'chartData', 'topProducts'), 'admin');
    }

    /* ── PRODUCTS ── */

    public function products(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '');
        $catId  = (int)($_GET['category_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $type   = trim($_GET['type'] ?? '');
        $params = [];
        $where  = '1=1';

        if ($search !== '') {
            $where .= ' AND p.name LIKE ?';
            $params[] = "%$search%";
        }
        if ($catId > 0) {
            $where .= ' AND p.category_id = ?';
            $params[] = $catId;
        }
        if ($status !== '') {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        if ($type !== '') {
            $where .= ' AND p.type = ?';
            $params[] = $type;
        }

        $result = $this->db->paginate(
            "SELECT p.*, c.name AS cat,
                    (SELECT COUNT(*) FROM product_keys pk WHERE pk.product_id = p.id AND pk.is_used = 0) AS keys_available
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE $where
             ORDER BY p.created_at DESC",
            $params,
            $page,
            20
        );
        $categories = categoriesForSelect(true);
        $this->view('admin.products', array_merge($result, compact('categories', 'search', 'catId', 'status', 'type')), 'admin');
    }

    public function createProduct(): void {
        $categories = categoriesForSelect(true);
        $this->view('admin.product_form', compact('categories'), 'admin');
    }

    public function storeProduct(): void {
        $data = $this->buildProductData();
        $image = $this->handleProductImageUpload();
        if ($image !== null) {
            $data['image'] = $image;
        }
        $this->db->insert('products', $data);
        setFlash('success', 'Produit créé.');
        $this->redirect('/admin/products');
    }

    public function editProduct(string $id): void {
        $product = $this->db->fetch('SELECT * FROM products WHERE id = ?', [(int)$id]);
        if (!$product) {
            $this->abort(404);
        }
        $categories = categoriesForSelect(true);
        $keysAvailable = $this->db->count('SELECT COUNT(*) FROM product_keys WHERE product_id = ? AND is_used = 0', [(int)$id]);
        $this->view('admin.product_form', compact('product', 'categories', 'keysAvailable'), 'admin');
    }

    public function updateProduct(string $id): void {
        $product = $this->db->fetch('SELECT * FROM products WHERE id = ?', [(int)$id]);
        if (!$product) {
            $this->abort(404);
        }

        $data = $this->buildProductData();
        $data['slug'] = $product['slug'];
        $data['image'] = $this->handleProductImageUpload($product['image'] ?? null);

        $this->db->update('products', $data, 'id = ?', [(int)$id]);
        setFlash('success', 'Produit mis à jour.');
        $this->redirect('/admin/products');
    }

    public function deleteProduct(string $id): void {
        $this->db->update('products', ['status' => 'inactive'], 'id = ?', [(int)$id]);
        setFlash('success', 'Produit désactivé.');
        $this->redirect('/admin/products');
    }

    private function handleProductImageUpload(?string $existing = null): ?string {
        if (!empty($_POST['remove_image'])) {
            if ($existing) {
                $path = UPLOAD_PATH . 'products/' . $existing;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            return null;
        }

        if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }

        if (($_FILES['image']['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            setFlash('error', 'Erreur lors du televersement de l\'image.');
            $this->back();
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            setFlash('error', 'Format image non supporte (JPG, PNG, WEBP, GIF).');
            $this->back();
        }

        if (($_FILES['image']['size'] ?? 0) > 3 * 1024 * 1024) {
            setFlash('error', 'Image trop volumineuse (max 3 Mo).');
            $this->back();
        }

        $destDir = UPLOAD_PATH . 'products/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destDir . $filename)) {
            setFlash('error', 'Impossible d\'enregistrer l\'image.');
            $this->back();
        }

        if ($existing) {
            $oldPath = $destDir . $existing;
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return $filename;
    }

    private function buildProductData(): array {
        $name = trim($this->input('name', ''));
        $stockRaw = $this->input('stock', '');
        $stock = ($stockRaw === '' || $stockRaw === null) ? null : (int)$stockRaw;

        $price = round((float)$this->input('price', 0), 2);
        $percentRaw = $this->input('discount_percent', '');
        $percent = ($percentRaw === '' || $percentRaw === null) ? null : round((float)$percentRaw, 2);

        $salePrice = null;
        if ($percent !== null && $percent > 0 && $price > 0) {
            if ($percent > 100) {
                $percent = 100.0;
            }
            $salePrice = round($price * (1 - ($percent / 100)), 2);
            if ($salePrice < 0) {
                $salePrice = 0.0;
            }
            // Si 0% effectif ou prix identique, pas de promo
            if ($percent <= 0 || $salePrice >= $price) {
                $salePrice = null;
                $percent = null;
            }
        }

        $type = $this->input('type', 'software');
        if (!array_key_exists($type, productTypes())) {
            $type = 'software';
        }

        return [
            'category_id'       => $this->input('category_id') ?: null,
            'name'              => $name,
            'slug'              => slug($name) . '-' . substr(uniqid(), -4),
            'short_desc'        => trim($this->input('short_desc', '')),
            'description'       => $this->input('description', ''),
            'type'              => $type,
            'price'             => $price,
            'discount_percent'  => $percent,
            'sale_price'        => $salePrice,
            'status'            => $this->input('status', 'active'),
            'featured'          => (int)(bool)$this->input('featured'),
            'delivery_type'     => $this->input('delivery_type', 'instant'),
            'stock'             => $stock,
            'meta_title'        => trim($this->input('meta_title', '')) ?: null,
            'meta_desc'         => trim($this->input('meta_desc', '')) ?: null,
        ];
    }

    /* ── LICENSES / KEYS ── */

    public function licenses(): void {
        $products = $this->db->fetchAll(
            "SELECT p.id, p.name, p.slug, p.stock, p.delivery_type, p.status,
                    SUM(CASE WHEN pk.is_used = 0 THEN 1 ELSE 0 END) AS available_keys,
                    SUM(CASE WHEN pk.is_used = 1 THEN 1 ELSE 0 END) AS used_keys,
                    COUNT(pk.id) AS total_keys
             FROM products p
             LEFT JOIN product_keys pk ON pk.product_id = p.id
             WHERE p.status IN ('active','draft','out_of_stock')
             GROUP BY p.id
             ORDER BY p.name ASC"
        );
        $this->view('admin.licenses', compact('products'), 'admin');
    }

    public function productKeys(string $id): void {
        $product = $this->db->fetch('SELECT * FROM products WHERE id = ?', [(int)$id]);
        if (!$product) {
            $this->abort(404);
        }
        $keys = $this->db->fetchAll(
            'SELECT * FROM product_keys WHERE product_id = ? ORDER BY is_used ASC, id DESC',
            [(int)$id]
        );
        $available = $this->db->count('SELECT COUNT(*) FROM product_keys WHERE product_id = ? AND is_used = 0', [(int)$id]);
        $this->view('admin.product_keys', compact('product', 'keys', 'available'), 'admin');
    }

    public function storeProductKeys(string $id): void {
        $product = $this->db->fetch('SELECT id, delivery_type FROM products WHERE id = ?', [(int)$id]);
        if (!$product) {
            $this->abort(404);
        }

        $raw = (string)$this->input('keys', '');
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $inserted = 0;
        foreach ($lines as $line) {
            $key = trim($line);
            if ($key === '') {
                continue;
            }
            $this->db->insert('product_keys', [
                'product_id' => (int)$id,
                'key_value'  => $key,
                'is_used'    => 0,
            ]);
            $inserted++;
        }

        if ($inserted > 0 && ($product['delivery_type'] ?? '') === 'instant') {
            $available = $this->db->count('SELECT COUNT(*) FROM product_keys WHERE product_id = ? AND is_used = 0', [(int)$id]);
            $this->db->update('products', ['stock' => $available], 'id = ?', [(int)$id]);
        }

        setFlash('success', $inserted > 0 ? "$inserted clé(s) ajoutée(s)." : 'Aucune clé à importer.');
        $this->redirect('/admin/products/' . $id . '/keys');
    }

    public function deleteProductKey(string $id, string $key_id): void {
        $key = $this->db->fetch('SELECT * FROM product_keys WHERE id = ? AND product_id = ?', [(int)$key_id, (int)$id]);
        if (!$key) {
            setFlash('error', 'Clé introuvable.');
            $this->redirect('/admin/products/' . $id . '/keys');
        }
        if ((int)$key['is_used'] === 1) {
            setFlash('error', 'Impossible de supprimer une clé déjà utilisée.');
            $this->redirect('/admin/products/' . $id . '/keys');
        }

        $this->db->delete('product_keys', 'id = ?', [(int)$key_id]);
        $product = $this->db->fetch('SELECT delivery_type FROM products WHERE id = ?', [(int)$id]);
        if (($product['delivery_type'] ?? '') === 'instant') {
            $available = $this->db->count('SELECT COUNT(*) FROM product_keys WHERE product_id = ? AND is_used = 0', [(int)$id]);
            $this->db->update('products', ['stock' => $available], 'id = ?', [(int)$id]);
        }

        setFlash('success', 'Clé supprimée.');
        $this->redirect('/admin/products/' . $id . '/keys');
    }

    /* ── COUPONS ── */

    public function coupons(): void {
        $coupons = $this->db->fetchAll('SELECT * FROM coupons ORDER BY created_at DESC');
        $this->view('admin.coupons', compact('coupons'), 'admin');
    }

    public function createCoupon(): void {
        $this->view('admin.coupon_form', [], 'admin');
    }

    public function storeCoupon(): void {
        $data = $this->buildCouponData();
        $exists = $this->db->fetch('SELECT id FROM coupons WHERE code = ?', [$data['code']]);
        if ($exists) {
            setFlash('error', 'Ce code coupon existe déjà.');
            $this->back();
        }
        $this->db->insert('coupons', $data);
        setFlash('success', 'Coupon créé.');
        $this->redirect('/admin/coupons');
    }

    public function editCoupon(string $id): void {
        $coupon = $this->db->fetch('SELECT * FROM coupons WHERE id = ?', [(int)$id]);
        if (!$coupon) {
            $this->abort(404);
        }
        $this->view('admin.coupon_form', compact('coupon'), 'admin');
    }

    public function updateCoupon(string $id): void {
        $coupon = $this->db->fetch('SELECT id FROM coupons WHERE id = ?', [(int)$id]);
        if (!$coupon) {
            $this->abort(404);
        }
        $data = $this->buildCouponData((int)$id);
        $this->db->update('coupons', $data, 'id = ?', [(int)$id]);
        setFlash('success', 'Coupon mis à jour.');
        $this->redirect('/admin/coupons');
    }

    public function deleteCoupon(string $id): void {
        $this->db->delete('coupons', 'id = ?', [(int)$id]);
        setFlash('success', 'Coupon supprimé.');
        $this->redirect('/admin/coupons');
    }

    private function buildCouponData(?int $excludeId = null): array {
        $code = strtoupper(trim($this->input('code', '')));
        if ($code === '') {
            setFlash('error', 'Le code est obligatoire.');
            $this->back();
        }
        if ($excludeId) {
            $dup = $this->db->fetch('SELECT id FROM coupons WHERE code = ? AND id != ?', [$code, $excludeId]);
            if ($dup) {
                setFlash('error', 'Ce code coupon existe déjà.');
                $this->back();
            }
        }

        $expires = trim($this->input('expires_at', ''));
        $minAmount = $this->input('min_amount', '');
        $maxUses = $this->input('max_uses', '');

        return [
            'code'       => $code,
            'type'       => $this->input('type', 'percent') === 'fixed' ? 'fixed' : 'percent',
            'value'      => (float)$this->input('value', 0),
            'min_amount' => ($minAmount === '' || $minAmount === null) ? null : (float)$minAmount,
            'max_uses'   => ($maxUses === '' || $maxUses === null) ? null : (int)$maxUses,
            'expires_at' => $expires !== '' ? date('Y-m-d H:i:s', strtotime($expires)) : null,
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    /* ── REVIEWS ── */

    public function reviews(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $status = trim($_GET['status'] ?? 'pending');
        $params = [];
        $where  = '1=1';
        if ($status !== '' && $status !== 'all') {
            $where .= ' AND r.status = ?';
            $params[] = $status;
        }

        $result = $this->db->paginate(
            "SELECT r.*, p.name AS product_name, p.slug AS product_slug, u.name AS user_name, u.email AS user_email
             FROM product_reviews r
             JOIN products p ON p.id = r.product_id
             JOIN users u ON u.id = r.user_id
             WHERE $where
             ORDER BY r.created_at DESC",
            $params,
            $page,
            20
        );
        $this->view('admin.reviews', array_merge($result, compact('status')), 'admin');
    }

    public function approveReview(string $id): void {
        $this->setReviewStatus((int)$id, 'approved');
    }

    public function rejectReview(string $id): void {
        $this->setReviewStatus((int)$id, 'rejected');
    }

    private function setReviewStatus(int $id, string $status): void {
        $review = $this->db->fetch('SELECT * FROM product_reviews WHERE id = ?', [$id]);
        if (!$review) {
            $this->abort(404);
        }
        $this->db->update('product_reviews', ['status' => $status], 'id = ?', [$id]);
        $this->recalcProductRating((int)$review['product_id']);
        setFlash('success', $status === 'approved' ? 'Avis approuvé.' : 'Avis rejeté.');
        $this->redirect('/admin/reviews?status=' . $status);
    }

    private function recalcProductRating(int $productId): void {
        $stats = $this->db->fetch(
            "SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS cnt
             FROM product_reviews WHERE product_id = ? AND status = 'approved'",
            [$productId]
        );
        $this->db->update('products', [
            'rating_avg'   => round((float)($stats['avg_rating'] ?? 0), 2),
            'rating_count' => (int)($stats['cnt'] ?? 0),
        ], 'id = ?', [$productId]);
    }

    /* ── ORDERS ── */

    public function orders(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');
        $params = [];
        $where = '1=1';
        if ($status) {
            $where .= ' AND o.payment_status=?';
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= ' AND (o.order_number LIKE ? OR u.email LIKE ? OR u.name LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $result = $this->db->paginate(
            "SELECT o.*, u.name AS customer, u.email AS customer_email
             FROM orders o JOIN users u ON u.id=o.user_id WHERE $where ORDER BY o.created_at DESC",
            $params,
            $page,
            20
        );
        $this->view('admin.orders', array_merge($result, compact('status', 'search')), 'admin');
    }

    public function orderDetail(string $id): void {
        $order = $this->db->fetch(
            "SELECT o.*, u.name AS customer, u.email AS customer_email, u.phone AS customer_phone
             FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?",
            [(int)$id]
        );
        if (!$order) {
            $this->abort(404);
        }
        $items = $this->db->fetchAll(
            'SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?',
            [(int)$id]
        );
        $this->view('admin.order_detail', compact('order', 'items'), 'admin');
    }

    public function validatePayment(string $id): void {
        $order = $this->db->fetch('SELECT * FROM orders WHERE id=?', [(int)$id]);
        if (!$order) {
            $this->json(['success' => false], 404);
        }

        $checkout = new CheckoutController();
        $checkout->deliverOrder((int)$id);

        $this->db->insert('notifications', [
            'user_id' => $order['user_id'],
            'type'    => 'payment_confirmed',
            'title'   => 'Paiement confirmé',
            'body'    => "Votre commande {$order['order_number']} a été confirmée.",
            'url'     => '/dashboard/orders/' . $id,
        ]);

        setFlash('success', 'Paiement validé.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function updateOrderStatus(string $id): void {
        $order = $this->db->fetch('SELECT * FROM orders WHERE id=?', [(int)$id]);
        if (!$order) {
            $this->abort(404);
        }

        $allowed = ['pending', 'paid', 'processing', 'completed', 'cancelled', 'refunded'];
        $status = $this->input('status', $order['status']);
        if (!in_array($status, $allowed, true)) {
            setFlash('error', 'Statut invalide.');
            $this->redirect('/admin/orders/' . $id);
        }

        $data = [
            'status' => $status,
            'notes'  => trim($this->input('notes', $order['notes'] ?? '')),
        ];

        if ($status === 'refunded') {
            $data['payment_status'] = 'refunded';
        }
        if ($status === 'cancelled' && $order['payment_status'] === 'unpaid') {
            $data['payment_status'] = 'failed';
        }

        $this->db->update('orders', $data, 'id = ?', [(int)$id]);
        setFlash('success', 'Commande mise à jour.');
        $this->redirect('/admin/orders/' . $id);
    }

    public function resendTicket(string $id): void {
        $order = $this->db->fetch('SELECT * FROM orders WHERE id=?', [(int)$id]);
        if (!$order) {
            $this->abort(404);
        }
        if ($order['payment_status'] !== 'paid' && $order['status'] !== 'completed') {
            setFlash('error', 'Le ticket n\'est envoyé que pour une commande payée / livrée.');
            $this->redirect('/admin/orders/' . $id);
        }

        $result = (new \App\Services\OrderTicketService($this->db))->generateAndSend((int)$id);
        $parts = [];
        if ($result['email']) {
            $parts[] = 'email OK';
        }
        if ($result['whatsapp']) {
            $parts[] = 'WhatsApp OK';
        }
        if ($result['pdf']) {
            $parts[] = 'PDF généré';
        }
        if ($parts) {
            setFlash('success', 'Ticket renvoyé : ' . implode(', ', $parts) . '.');
        } else {
            setFlash('error', 'Échec envoi ticket. ' . implode(' ', $result['errors']));
        }
        $this->redirect('/admin/orders/' . $id);
    }

    /* ── USERS ── */

    public function users(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '');
        $role   = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $params = [];
        $where = "role != 'admin'";

        if ($search !== '') {
            $where .= ' AND (name LIKE ? OR email LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($role !== '') {
            $where .= ' AND role = ?';
            $params[] = $role;
        }
        if ($status !== '') {
            $where .= ' AND status = ?';
            $params[] = $status;
        }

        $result = $this->db->paginate(
            "SELECT * FROM users WHERE $where ORDER BY created_at DESC",
            $params,
            $page,
            20
        );
        $this->view('admin.users', array_merge($result, compact('search', 'role', 'status')), 'admin');
    }

    public function userDetail(string $id): void {
        $user = $this->db->fetch('SELECT * FROM users WHERE id=?', [(int)$id]);
        if (!$user) {
            $this->abort(404);
        }
        $orders = $this->db->fetchAll(
            'SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 10',
            [(int)$id]
        );
        $this->view('admin.user_detail', compact('user', 'orders'), 'admin');
    }

    public function updateUser(string $id): void {
        $user = $this->db->fetch('SELECT * FROM users WHERE id=?', [(int)$id]);
        if (!$user) {
            $this->abort(404);
        }

        if ((int)$user['id'] === (int)Auth::id()) {
            setFlash('error', 'Vous ne pouvez pas modifier votre propre compte ici.');
            $this->redirect('/admin/users/' . $id);
        }

        $role = $this->input('role', $user['role']);
        $status = $this->input('status', $user['status']);
        $allowedRoles = ['customer', 'vendor', 'admin'];
        $allowedStatus = ['active', 'inactive', 'banned'];

        if (!in_array($role, $allowedRoles, true) || !in_array($status, $allowedStatus, true)) {
            setFlash('error', 'Valeurs invalides.');
            $this->redirect('/admin/users/' . $id);
        }

        $this->db->update('users', [
            'role'   => $role,
            'status' => $status,
        ], 'id = ?', [(int)$id]);

        setFlash('success', 'Utilisateur mis à jour.');
        $this->redirect('/admin/users/' . $id);
    }

    /* ── TICKETS ── */

    public function tickets(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? 'open';
        $result = $this->db->paginate(
            "SELECT t.*, u.name AS user_name FROM tickets t JOIN users u ON u.id=t.user_id
             WHERE t.status=? ORDER BY t.updated_at DESC",
            [$status],
            $page,
            20
        );
        $this->view('admin.tickets', array_merge($result, compact('status')), 'admin');
    }

    public function ticketDetail(string $id): void {
        $ticket = $this->db->fetch(
            'SELECT t.*, u.name AS user_name, u.email AS user_email FROM tickets t JOIN users u ON u.id=t.user_id WHERE t.id=?',
            [(int)$id]
        );
        if (!$ticket) {
            $this->abort(404);
        }
        $replies = $this->db->fetchAll(
            'SELECT tr.*, u.name, u.role FROM ticket_replies tr JOIN users u ON u.id=tr.user_id WHERE tr.ticket_id=? ORDER BY tr.created_at ASC',
            [(int)$id]
        );
        $this->view('admin.ticket_detail', compact('ticket', 'replies'), 'admin');
    }

    public function replyTicket(string $id): void {
        $ticket = $this->db->fetch('SELECT id, ticket_no, user_id FROM tickets WHERE id=?', [(int)$id]);
        if (!$ticket) {
            $this->abort(404);
        }
        $this->db->insert('ticket_replies', [
            'ticket_id' => (int)$id,
            'user_id'   => Auth::id(),
            'body'      => trim($this->input('body', '')),
            'is_staff'  => 1,
        ]);
        $this->db->update('tickets', ['status' => 'replied'], 'id=?', [(int)$id]);
        $this->db->insert('notifications', [
            'user_id' => $ticket['user_id'],
            'type'    => 'ticket_reply',
            'title'   => 'Réponse du support',
            'body'    => 'Vous avez reçu une nouvelle réponse sur le ticket ' . ($ticket['ticket_no'] ?? ('#' . $ticket['id'])) . '.',
            'url'     => '/dashboard/tickets/' . $ticket['id'],
        ]);
        setFlash('success', 'Réponse envoyée.');
        $this->redirect('/admin/tickets/' . $id);
    }

    public function updateTicketStatus(string $id): void {
        $ticket = $this->db->fetch('SELECT id FROM tickets WHERE id=?', [(int)$id]);
        if (!$ticket) {
            $this->abort(404);
        }
        $status = $this->input('status', 'open');
        $priority = $this->input('priority', 'normal');
        $allowedStatus = ['open', 'replied', 'resolved', 'closed'];
        $allowedPriority = ['low', 'normal', 'high', 'urgent'];
        if (!in_array($status, $allowedStatus, true) || !in_array($priority, $allowedPriority, true)) {
            setFlash('error', 'Valeurs invalides.');
            $this->redirect('/admin/tickets/' . $id);
        }
        $this->db->update('tickets', [
            'status'   => $status,
            'priority' => $priority,
        ], 'id = ?', [(int)$id]);
        setFlash('success', 'Ticket mis à jour.');
        $this->redirect('/admin/tickets/' . $id);
    }

    /* ── SERVICES ── */

    public function services(): void {
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? 'new';
        $result = $this->db->paginate(
            'SELECT * FROM service_requests WHERE status=? ORDER BY created_at DESC',
            [$status],
            $page,
            20
        );
        $this->view('admin.services', array_merge($result, compact('status')), 'admin');
    }

    public function serviceDetail(string $id): void {
        $service = $this->db->fetch('SELECT * FROM service_requests WHERE id=?', [(int)$id]);
        if (!$service) {
            $this->abort(404);
        }
        $this->view('admin.service_detail', compact('service'), 'admin');
    }

    public function updateService(string $id): void {
        $service = $this->db->fetch('SELECT id FROM service_requests WHERE id=?', [(int)$id]);
        if (!$service) {
            $this->abort(404);
        }
        $allowed = ['new', 'reviewing', 'quoted', 'accepted', 'in_progress', 'completed', 'rejected'];
        $status = $this->input('status', 'new');
        if (!in_array($status, $allowed, true)) {
            setFlash('error', 'Statut invalide.');
            $this->redirect('/admin/services/' . $id);
        }
        $quoted = $this->input('quoted_amount', '');
        $this->db->update('service_requests', [
            'status'        => $status,
            'quoted_amount' => ($quoted === '' || $quoted === null) ? null : (float)$quoted,
            'admin_notes'   => trim($this->input('admin_notes', '')) ?: null,
        ], 'id = ?', [(int)$id]);
        setFlash('success', 'Demande mise à jour.');
        $this->redirect('/admin/services/' . $id);
    }

    /* ── BLOG ── */

    public function blog(): void {
        $posts = $this->db->fetchAll(
            'SELECT bp.*, u.name AS author FROM blog_posts bp JOIN users u ON u.id=bp.author_id ORDER BY bp.created_at DESC LIMIT 100'
        );
        $this->view('admin.blog', compact('posts'), 'admin');
    }

    public function createBlog(): void {
        $this->view('admin.blog_form', [], 'admin');
    }

    public function storeBlog(): void {
        $data = $this->buildBlogData();
        $data['author_id'] = Auth::id();
        $this->db->insert('blog_posts', $data);
        setFlash('success', 'Article créé.');
        $this->redirect('/admin/blog');
    }

    public function editBlog(string $id): void {
        $post = $this->db->fetch('SELECT * FROM blog_posts WHERE id=?', [(int)$id]);
        if (!$post) {
            $this->abort(404);
        }
        $this->view('admin.blog_form', compact('post'), 'admin');
    }

    public function updateBlog(string $id): void {
        $post = $this->db->fetch('SELECT * FROM blog_posts WHERE id=?', [(int)$id]);
        if (!$post) {
            $this->abort(404);
        }
        $data = $this->buildBlogData((int)$id, $post);
        $this->db->update('blog_posts', $data, 'id = ?', [(int)$id]);
        setFlash('success', 'Article mis à jour.');
        $this->redirect('/admin/blog');
    }

    public function deleteBlog(string $id): void {
        $this->db->delete('blog_posts', 'id = ?', [(int)$id]);
        setFlash('success', 'Article supprimé.');
        $this->redirect('/admin/blog');
    }

    private function buildBlogData(?int $excludeId = null, ?array $existing = null): array {
        $title = trim($this->input('title', ''));
        if ($title === '') {
            setFlash('error', 'Le titre est obligatoire.');
            $this->back();
        }
        $slugInput = trim($this->input('slug', ''));
        $slug = $slugInput !== '' ? slug($slugInput) : slug($title);
        $dupSql = 'SELECT id FROM blog_posts WHERE slug = ?';
        $dupParams = [$slug];
        if ($excludeId) {
            $dupSql .= ' AND id != ?';
            $dupParams[] = $excludeId;
        }
        if ($this->db->fetch($dupSql, $dupParams)) {
            $slug .= '-' . substr(uniqid(), -4);
        }

        $status = $this->input('status', 'draft') === 'published' ? 'published' : 'draft';
        $publishedAt = $existing['published_at'] ?? null;
        if ($status === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }
        if ($status === 'draft') {
            $publishedAt = $existing['published_at'] ?? null;
        }

        $image = $existing['image'] ?? null;
        if (!empty($_POST['remove_image'])) {
            $image = null;
        }
        if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $destDir = UPLOAD_PATH . 'blog/';
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                $filename = 'blog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destDir . $filename)) {
                    $image = 'blog/' . $filename;
                }
            }
        }

        $tagsRaw = trim($this->input('tags', ''));
        $tags = array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));

        return [
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => trim($this->input('excerpt', '')) ?: null,
            'body'         => $this->input('body', ''),
            'image'        => $image,
            'status'       => $status,
            'featured'     => isset($_POST['featured']) ? 1 : 0,
            'tags'         => $tags ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null,
            'meta_title'   => trim($this->input('meta_title', '')) ?: null,
            'meta_desc'    => trim($this->input('meta_desc', '')) ?: null,
            'published_at' => $publishedAt,
        ];
    }

    /* ── ANALYTICS / SETTINGS ── */

    public function analytics(): void {
        $monthly = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, SUM(total) AS revenue, COUNT(*) AS orders
             FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month ORDER BY month ASC"
        );
        $byCategory = $this->db->fetchAll(
            "SELECT c.name, SUM(oi.subtotal) AS revenue, SUM(oi.quantity) AS units
             FROM order_items oi
             JOIN products p ON p.id=oi.product_id
             JOIN categories c ON c.id=p.category_id
             JOIN orders o ON o.id=oi.order_id AND o.payment_status='paid'
             GROUP BY c.id ORDER BY revenue DESC"
        );
        $this->view('admin.analytics', compact('monthly', 'byCategory'), 'admin');
    }

    public function settings(): void {
        $settings = $this->db->fetchAll('SELECT * FROM settings ORDER BY `group`, `key`');
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group']][$s['key']] = $s;
        }
        $promoSetting = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'home_promo_product_ids' LIMIT 1");
        $promoIds = [];
        if (!empty($promoSetting['value'])) {
            $decoded = json_decode((string)$promoSetting['value'], true);
            if (is_array($decoded)) {
                $promoIds = array_values(array_unique(array_map('intval', $decoded)));
            }
        }
        $promoProducts = $this->db->fetchAll(
            "SELECT id, name, price, sale_price, status FROM products WHERE status = 'active' ORDER BY name ASC"
        );
        $this->view('admin.settings', compact('grouped', 'promoProducts', 'promoIds'), 'admin');
    }

    public function updateSettings(): void {
        $promoIdsInput = $_POST['home_promo_product_ids'] ?? [];
        if (!is_array($promoIdsInput)) {
            $promoIdsInput = [];
        }
        $promoIds = array_values(array_unique(array_filter(array_map('intval', $promoIdsInput), static fn($id) => $id > 0)));
        if (count($promoIds) > 6) {
            setFlash('error', 'Veuillez sélectionner au maximum 6 offres promotionnelles.');
            $this->redirect('/admin/settings');
        }

        foreach ($_POST as $key => $value) {
            if ($key === '_csrf' || $key === 'home_promo_product_ids') {
                continue;
            }
            $this->db->query('UPDATE settings SET value=? WHERE `key`=?', [$value, $key]);
        }

        // Unchecked booleans: ensure known boolean keys get 0 if missing
        $boolKeys = $this->db->fetchAll("SELECT `key` FROM settings WHERE type = 'boolean'");
        foreach ($boolKeys as $row) {
            $k = $row['key'];
            if (!array_key_exists($k, $_POST)) {
                $this->db->query('UPDATE settings SET value=? WHERE `key`=?', ['0', $k]);
            }
        }

        $exists = $this->db->fetch("SELECT id FROM settings WHERE `key` = 'home_promo_product_ids' LIMIT 1");
        $promoJson = json_encode($promoIds, JSON_UNESCAPED_UNICODE);
        if ($exists) {
            $this->db->query("UPDATE settings SET value=? WHERE `key`='home_promo_product_ids'", [$promoJson]);
        } else {
            $this->db->insert('settings', [
                'key'   => 'home_promo_product_ids',
                'value' => $promoJson,
                'type'  => 'json',
                'group' => 'homepage',
            ]);
        }

        setFlash('success', 'Paramètres mis à jour.');
        $this->redirect('/admin/settings');
    }

    /* ── CATEGORIES ── */

    public function categories(): void {
        $categories = $this->db->fetchAll(
            "SELECT c.*, p.name AS parent_name,
                    (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count,
                    (SELECT COUNT(*) FROM categories ch WHERE ch.parent_id = c.id) AS child_count
             FROM categories c
             LEFT JOIN categories p ON p.id = c.parent_id
             ORDER BY COALESCE(c.parent_id, c.id), c.parent_id IS NOT NULL, c.sort_order, c.name"
        );
        $this->view('admin.categories', compact('categories'), 'admin');
    }

    public function createCategory(): void {
        $parents = $this->db->fetchAll(
            'SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY sort_order, name'
        );
        $this->view('admin.category_form', compact('parents'), 'admin');
    }

    public function storeCategory(): void {
        $data = $this->buildCategoryData();
        $this->db->insert('categories', $data);
        setFlash('success', 'Rubrique créée.');
        $this->redirect('/admin/categories');
    }

    public function editCategory(string $id): void {
        $category = $this->db->fetch('SELECT * FROM categories WHERE id = ?', [(int)$id]);
        if (!$category) {
            $this->abort(404);
        }
        $parents = $this->db->fetchAll(
            'SELECT id, name FROM categories WHERE parent_id IS NULL AND id != ? ORDER BY sort_order, name',
            [(int)$id]
        );
        $this->view('admin.category_form', compact('category', 'parents'), 'admin');
    }

    public function updateCategory(string $id): void {
        $category = $this->db->fetch('SELECT id FROM categories WHERE id = ?', [(int)$id]);
        if (!$category) {
            $this->abort(404);
        }
        $data = $this->buildCategoryData((int)$id);
        $this->db->update('categories', $data, 'id = ?', [(int)$id]);
        setFlash('success', 'Rubrique mise à jour.');
        $this->redirect('/admin/categories');
    }

    public function deleteCategory(string $id): void {
        $category = $this->db->fetch('SELECT * FROM categories WHERE id = ?', [(int)$id]);
        if (!$category) {
            $this->json(['success' => false, 'message' => 'Rubrique introuvable.'], 404);
        }

        $cid = (int)$category['id'];
        $products = $this->db->count('SELECT COUNT(*) FROM products WHERE category_id = ?', [$cid]);
        $children = $this->db->count('SELECT COUNT(*) FROM categories WHERE parent_id = ?', [$cid]);

        if ($products > 0 || $children > 0) {
            $this->db->update('categories', ['is_active' => 0], 'id = ?', [$cid]);
            $this->json(['success' => true, 'deactivated' => true]);
        }

        $this->db->delete('categories', 'id = ?', [$cid]);
        $this->json(['success' => true, 'deactivated' => false]);
    }

    private function buildCategoryData(?int $excludeId = null): array {
        $name = trim($this->input('name', ''));
        if ($name === '') {
            setFlash('error', 'Le nom est obligatoire.');
            $this->back();
        }

        $parentId = $this->input('parent_id') ? (int)$this->input('parent_id') : null;
        if ($parentId && $excludeId && $parentId === $excludeId) {
            setFlash('error', 'Une rubrique ne peut pas être sa propre parente.');
            $this->back();
        }

        if ($parentId) {
            $parent = $this->db->fetch('SELECT id, parent_id FROM categories WHERE id = ?', [$parentId]);
            if (!$parent || $parent['parent_id']) {
                setFlash('error', 'La rubrique parente doit être une rubrique principale (pas une sous-rubrique).');
                $this->back();
            }
            if ($excludeId) {
                $hasChildren = $this->db->count('SELECT COUNT(*) FROM categories WHERE parent_id = ?', [$excludeId]);
                if ($hasChildren) {
                    setFlash('error', 'Impossible : cette rubrique a déjà des sous-rubriques.');
                    $this->back();
                }
            }
        }

        $slugInput = trim($this->input('slug', ''));
        $slug = $slugInput !== '' ? slug($slugInput) : uniqueCategorySlug($name, $excludeId);

        return [
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim($this->input('description', '')) ?: null,
            'icon'        => trim($this->input('icon', '')) ?: 'bi-box-seam',
            'parent_id'   => $parentId,
            'sort_order'  => (int)$this->input('sort_order', 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
