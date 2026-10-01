<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class CartController extends Controller {
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
        $coupon  = $_SESSION['coupon'] ?? null;
        $summary = $this->calculateSummary($items, $coupon);
        $this->view('cart.index', compact('items', 'coupon', 'summary'));
    }

    public function add(): void {
        $this->denyAdmin();
        $productId = (int) $this->input('product_id');
        $qty       = max(1, (int) $this->input('qty', 1));

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
                $this->db->update('cart_items', ['quantity' => $existing['quantity'] + $qty], 'id = ?', ['id' => $existing['id']]);
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
            $_SESSION['cart'][$productId]['qty'] += $qty;
        }

        $this->json(['success' => true, 'cart_count' => cartCount(), 'message' => 'Produit ajouté au panier']);
    }

    public function update(): void {
        $this->denyAdmin();
        $productId = (int) $this->input('product_id');
        $qty       = max(0, (int) $this->input('qty'));

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
        $coupon = $this->db->fetch(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1
             AND (expires_at IS NULL OR expires_at > NOW())
             AND (max_uses IS NULL OR used_count < max_uses)",
            [$code]
        );

        if (!$coupon) {
            $this->json(['success' => false, 'message' => 'Code promo invalide ou expiré']);
        }

        $_SESSION['coupon'] = $coupon;
        $this->json(['success' => true, 'message' => 'Code promo appliqué !', 'coupon' => $coupon]);
    }

    public function getCartItems(): array {
        if (Auth::check()) {
            return $this->db->fetchAll(
                "SELECT ci.*, p.name, p.image, p.slug, p.stock,
                        COALESCE(p.sale_price, p.price) AS unit_price
                 FROM cart_items ci
                 JOIN products p ON p.id = ci.product_id
                 WHERE ci.user_id = ?",
                [Auth::id()]
            );
        }
        $items = [];
        foreach ($_SESSION['cart'] ?? [] as $pid => $data) {
            $p = $this->db->fetch("SELECT id, name, image, slug, price, sale_price FROM products WHERE id = ?", [$pid]);
            if ($p) {
                $items[] = array_merge($p, [
                    'quantity'   => $data['qty'],
                    'unit_price' => $p['sale_price'] ?? $p['price'],
                ]);
            }
        }
        return $items;
    }

    public function calculateSummary(array $items, ?array $coupon): array {
        $subtotal = array_sum(array_map(fn($i) => ($i['unit_price'] ?? $i['price']) * $i['quantity'], $items));
        $discount = 0;

        if ($coupon) {
            $discount = $coupon['type'] === 'percent'
                ? $subtotal * ($coupon['value'] / 100)
                : min($coupon['value'], $subtotal);
        }

        $taxRate = 0;
        $after   = $subtotal - $discount;
        $tax     = round($after * ($taxRate / 100), 2);
        $total   = $after + $tax;

        return compact('subtotal', 'discount', 'tax', 'total');
    }
}
