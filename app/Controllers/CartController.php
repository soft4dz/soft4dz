<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class CartController extends Controller {
    /** Quantité maximale par article (borne aussi la colonne SMALLINT). */
    private const MAX_QTY = 99;

    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function denyAdmin(): void {
        if (Auth::isAdmin()) {
            $this->json(['success' => false, 'message' => "Le panier n'est pas disponible pour un compte administrateur."], 403);
        }
    }

    public function index(): void {
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $items   = $this->getCartItems();
        $coupon  = $this->currentCoupon($items);
        $summary = $this->calculateSummary($items, $coupon);
        $this->view('cart.index', compact('items', 'coupon', 'summary'));
    }

    public function add(): void {
        $this->denyAdmin();
        $productId = (int) $this->input('product_id');
        $qty       = min(self::MAX_QTY, max(1, (int) $this->input('qty', 1)));

        $product = $this->db->fetch(
            "SELECT id, name, price, sale_price, stock FROM products WHERE id = ? AND status = 'active'",
            [$productId]
        );

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Produit introuvable'], 404);
        }

        $price = $product['sale_price'] ?? $product['price'];

        if (Auth::check()) {
            $existing = $this->db->fetch(
                "SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?",
                [Auth::id(), $productId]
            );
            if ($existing) {
                $this->db->update('cart_items', ['quantity' => min(self::MAX_QTY, $existing['quantity'] + $qty)], 'id = ?', ['id' => $existing['id']]);
            } else {
                $this->db->insert('cart_items', [
                    'user_id'    => Auth::id(),
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'price'      => $price,
                ]);
            }
        } else {
            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = ['product_id' => $productId, 'qty' => 0, 'price' => $price, 'name' => $product['name']];
            }
            $_SESSION['cart'][$productId]['qty'] = min(self::MAX_QTY, $_SESSION['cart'][$productId]['qty'] + $qty);
        }

        $this->json(['success' => true, 'cart_count' => cartCount(), 'message' => 'Produit ajouté au panier']);
    }

    public function update(): void {
        $this->denyAdmin();
        $productId = (int) $this->input('product_id');
        $qty       = min(self::MAX_QTY, max(0, (int) $this->input('qty')));

        if (Auth::check()) {
            if ($qty === 0) {
                $this->db->delete('cart_items', 'user_id = ? AND product_id = ?', [Auth::id(), $productId]);
            } else {
                $this->db->query(
                    "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?",
                    [$qty, Auth::id(), $productId]
                );
            }
        } else {
            if ($qty === 0) {
                unset($_SESSION['cart'][$productId]);
            } elseif (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['qty'] = $qty;
            }
        }

        $this->json(['success' => true, 'cart_count' => cartCount()]);
    }

    public function remove(): void {
        $this->denyAdmin();
        $productId = (int) $this->input('product_id');
        if (Auth::check()) {
            $this->db->delete('cart_items', 'user_id = ? AND product_id = ?', [Auth::id(), $productId]);
        } else {
            unset($_SESSION['cart'][$productId]);
        }
        $this->json(['success' => true, 'cart_count' => cartCount()]);
    }

    public function applyCoupon(): void {
        $this->denyAdmin();
        $code   = strtoupper(trim($this->input('code', '')));
        $coupon = $this->findUsableCoupon($code);

        if (!$coupon) {
            $this->json(['success' => false, 'message' => 'Code promo invalide ou expiré']);
        }

        $subtotal = $this->calculateSummary($this->getCartItems(), null)['subtotal'];
        if ($coupon['min_amount'] !== null && $subtotal < (float) $coupon['min_amount']) {
            $this->json(['success' => false, 'message' => 'Ce code promo nécessite un panier d\'au moins ' . formatPrice((float) $coupon['min_amount']) . '.']);
        }

        $_SESSION['coupon'] = $coupon;
        $this->json(['success' => true, 'message' => 'Code promo appliqué !', 'coupon' => $coupon]);
    }

    /**
     * Articles du panier, même forme pour un compte ou un invité :
     * `id` et `product_id` = id du produit (jamais l'id de la ligne cart_items).
     */
    public function getCartItems(): array {
        if (Auth::check()) {
            return $this->db->fetchAll(
                "SELECT p.id, ci.product_id, ci.quantity, p.name, p.image, p.slug, p.stock, p.price, p.sale_price,
                        COALESCE(p.sale_price, p.price) AS unit_price
                 FROM cart_items ci
                 JOIN products p ON p.id = ci.product_id AND p.status = 'active'
                 WHERE ci.user_id = ?",
                [Auth::id()]
            );
        }
        $items = [];
        foreach ($_SESSION['cart'] ?? [] as $pid => $data) {
            $p = $this->db->fetch(
                "SELECT id, name, image, slug, stock, price, sale_price FROM products WHERE id = ? AND status = 'active'",
                [$pid]
            );
            if ($p) {
                $items[] = array_merge($p, [
                    'product_id' => (int) $p['id'],
                    'quantity'   => $data['qty'],
                    'unit_price' => $p['sale_price'] ?? $p['price'],
                ]);
            }
        }
        return $items;
    }

    /** Après connexion : transfère le panier invité (session) dans le panier du compte. */
    public function mergeGuestCart(): void {
        $guest = $_SESSION['cart'] ?? [];
        unset($_SESSION['cart']);
        if (!$guest || !Auth::check() || Auth::isAdmin()) {
            return;
        }
        foreach ($guest as $pid => $data) {
            $product = $this->db->fetch(
                "SELECT id, price, sale_price FROM products WHERE id = ? AND status = 'active'",
                [(int) $pid]
            );
            if (!$product) {
                continue;
            }
            $qty = min(self::MAX_QTY, max(1, (int) ($data['qty'] ?? 1)));
            $this->db->query(
                "INSERT INTO cart_items (user_id, product_id, quantity, price) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + ?, ?)",
                [Auth::id(), (int) $product['id'], $qty, $product['sale_price'] ?? $product['price'], $qty, self::MAX_QTY]
            );
        }
    }

    private function findUsableCoupon(string $code): ?array {
        if ($code === '') {
            return null;
        }
        return $this->db->fetch(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1
             AND (expires_at IS NULL OR expires_at > NOW())
             AND (max_uses IS NULL OR used_count < max_uses)",
            [$code]
        ) ?: null;
    }

    /**
     * Coupon de la session, relu en base : retiré s'il a expiré, est épuisé
     * ou si le panier n'atteint plus le montant minimum.
     */
    public function currentCoupon(array $items): ?array {
        $code = (string) ($_SESSION['coupon']['code'] ?? '');
        if ($code === '') {
            return null;
        }
        $coupon   = $this->findUsableCoupon($code);
        $subtotal = $this->calculateSummary($items, null)['subtotal'];
        if (!$coupon || ($coupon['min_amount'] !== null && $subtotal < (float) $coupon['min_amount'])) {
            unset($_SESSION['coupon']);
            return null;
        }
        $_SESSION['coupon'] = $coupon;
        return $coupon;
    }

    public function calculateSummary(array $items, ?array $coupon): array {
        $subtotal = array_sum(array_map(fn($i) => ($i['unit_price'] ?? $i['price']) * $i['quantity'], $items));
        $discount = 0;

        if ($coupon) {
            $discount = $coupon['type'] === 'percent'
                ? $subtotal * (min(100, (float) $coupon['value']) / 100)
                : min((float) $coupon['value'], $subtotal);
        }

        $taxRate = 0;
        $after   = $subtotal - $discount;
        $tax     = round($after * ($taxRate / 100), 2);
        $total   = $after + $tax;

        return compact('subtotal', 'discount', 'tax', 'total');
    }
}
