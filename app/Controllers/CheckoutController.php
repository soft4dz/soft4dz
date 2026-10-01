<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\ChargilyPayService;

class CheckoutController extends Controller {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        $this->requireAuth();
        $cart = new CartController();
        $items = $cart->getCartItems();

        if (empty($items)) {
            setFlash('error', 'Votre panier est vide.');
            $this->redirect('/cart');
        }

        $coupon  = $cart->currentCoupon($items);
        $summary = $cart->calculateSummary($items, $coupon);

        $bankInfo = json_decode(
            $this->db->fetch("SELECT value FROM settings WHERE `key` = 'bank_account'"
        )['value'] ?? '{}', true);

        $chargilyEnabled = ChargilyPayService::isConfigured();

        $this->view('checkout.index', compact('items', 'summary', 'coupon', 'bankInfo', 'chargilyEnabled'));
    }

    public function process(): void {
        $this->requireAuth();

        if (!Auth::verifyCsrf($this->input('_csrf', ''))) {
            setFlash('error', 'Token invalide.');
            $this->redirect('/checkout');
        }

        $cart    = new CartController();
        $items   = $cart->getCartItems();
        if (empty($items)) $this->redirect('/cart');

        $hadCoupon = !empty($_SESSION['coupon']);
        $coupon    = $cart->currentCoupon($items);
        if ($hadCoupon && !$coupon) {
            setFlash('error', "Votre code promo n'est plus valable : vérifiez le nouveau total avant de confirmer.");
            $this->redirect('/checkout');
        }
        $summary = $cart->calculateSummary($items, $coupon);
        $method  = $this->input('payment_method', 'bank_transfer');
        $notes   = trim($this->input('notes', ''));

        $allowedMethods = ['bank_transfer', 'cib', 'edahabia'];
        if (ChargilyPayService::isConfigured()) {
            $allowedMethods[] = 'chargily';
        }
        if (!in_array($method, $allowedMethods, true)) {
            $method = 'bank_transfer';
        }
        if ($method === 'chargily' && (float) $summary['total'] <= 0) {
            $method = 'bank_transfer';
        }

        $orderNumber = 'S4DZ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $paymentStatusInsert = ($method === 'chargily') ? 'pending' : 'unpaid';

        $this->db->beginTransaction();
        try {
            if ($coupon) {
                // Réservation atomique : deux commandes simultanées ne peuvent pas dépasser max_uses
                $reserved = $this->db->query(
                    "UPDATE coupons SET used_count = used_count + 1
                     WHERE id = ? AND is_active = 1 AND (max_uses IS NULL OR used_count < max_uses)",
                    [$coupon['id']]
                )->rowCount();
                if ($reserved === 0) {
                    $this->db->rollback();
                    unset($_SESSION['coupon']);
                    setFlash('error', "Ce code promo n'est plus disponible. Vérifiez le total avant de confirmer.");
                    $this->redirect('/cart');
                }
            }

            $orderId = $this->db->insert('orders', [
                'order_number'   => $orderNumber,
                'user_id'        => Auth::id(),
                'status'         => 'pending',
                'subtotal'       => $summary['subtotal'],
                'discount'       => $summary['discount'],
                'tax'            => $summary['tax'],
                'total'          => $summary['total'],
                'currency'       => 'DZD',
                'coupon_id'      => $coupon['id'] ?? null,
                'payment_method' => $method,
                'payment_status' => $paymentStatusInsert,
                'notes'          => $notes,
            ]);

            foreach ($items as $item) {
                $this->db->insert('order_items', [
                    'order_id'    => $orderId,
                    'product_id'  => $item['product_id'],
                    'product_name'=> $item['name'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['unit_price'] ?? $item['price'],
                    'subtotal'    => ($item['unit_price'] ?? $item['price']) * $item['quantity'],
                ]);
                $this->db->query(
                    "UPDATE products SET sales_count = sales_count + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }

            $this->db->insert('notifications', [
                'user_id' => Auth::id(),
                'type'    => 'order_created',
                'title'   => 'Commande créée',
                'body'    => "Votre commande {$orderNumber} est bien enregistrée.",
                'url'     => '/dashboard/orders/' . $orderId,
            ]);

            // Clear cart
            $this->db->delete('cart_items', 'user_id = ?', [Auth::id()]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            error_log('Checkout failed: ' . $e->getMessage());
            setFlash('error', 'Erreur lors de la commande. Réessayez.');
            $this->redirect('/checkout');
        }

        // La commande est enregistrée : les étapes suivantes ne doivent plus tenter de rollback.
        unset($_SESSION['cart'], $_SESSION['coupon']);

        if ((float) $summary['total'] <= 0) {
            $this->deliverOrder((int) $orderId);
            $this->redirect('/checkout/success/' . $orderId);
        }

        if ($method === 'chargily') {
            $amountDzd = (int) max(1, (int) round((float) $summary['total']));
            $base      = rtrim(APP_URL, '/');
            $successU  = $base . '/checkout/chargily-return?order=' . (int) $orderId;
            $failureU  = $base . '/checkout/chargily-return?order=' . (int) $orderId . '&status=failed';
            $webhookU  = $base . '/webhook/chargily';

            $cp = ChargilyPayService::createCheckout(
                $amountDzd,
                $successU,
                $failureU,
                $webhookU,
                [
                    'order_id'     => (string) $orderId,
                    'order_number' => $orderNumber,
                ],
                'Commande ' . $orderNumber
            );

            if ($cp === null || empty($cp['checkout_url'])) {
                setFlash('error', 'Paiement en ligne indisponible pour le moment. Choisissez le virement ou réessayez plus tard. Votre commande est enregistrée (n° ' . $orderNumber . ').');
                $this->redirect('/dashboard/orders/' . $orderId);
            }

            $this->db->update('orders', [
                'payment_ref' => (string) ($cp['id'] ?? ''),
            ], 'id = ?', ['id' => (int) $orderId]);

            header('Location: ' . $cp['checkout_url']);
            exit;
        }

        $this->redirect('/checkout/success/' . $orderId);
    }

    public function success(string $orderId): void {
        $this->requireAuth();
        $order = $this->db->fetch(
            "SELECT o.*, GROUP_CONCAT(p.name SEPARATOR ', ') AS product_names
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE o.id = ? AND o.user_id = ?
             GROUP BY o.id",
            [(int)$orderId, Auth::id()]
        );

        if (!$order) $this->abort(404);

        $items = $this->db->fetchAll(
            "SELECT oi.*, p.image FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?",
            [(int)$orderId]
        );

        $bankInfo = json_decode(
            $this->db->fetch("SELECT value FROM settings WHERE `key` = 'bank_account'"
        )['value'] ?? '{}', true);

        $this->view('checkout.success', compact('order', 'items', 'bankInfo'));
    }

    /**
     * Retour utilisateur après paiement Chargily (success_url / failure_url).
     */
    public function chargilyReturn(): void {
        $this->requireAuth();
        $orderId = (int) ($this->input('order', '') ?: ($_GET['order'] ?? 0));
        $failed  = ($this->input('status', '') ?: ($_GET['status'] ?? '')) === 'failed';

        if ($orderId < 1) {
            $this->redirect('/dashboard/orders');
        }

        $order = $this->db->fetch('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$orderId, Auth::id()]);
        if (!$order) {
            $this->abort(404);
        }

        if ($failed) {
            setFlash('error', 'Paiement annulé ou refusé. Vous pouvez réessayer depuis votre espace ou choisir un autre mode de paiement.');
            $this->redirect('/dashboard/orders/' . $orderId);
        }

        if ($order['payment_status'] === 'paid') {
            setFlash('success', 'Paiement confirmé !');
        } else {
            setFlash('info', 'Si vous venez de payer, la confirmation peut prendre quelques secondes. Actualisez cette page si besoin.');
        }

        $this->redirect('/checkout/success/' . $orderId);
    }

    public function uploadProof(): void {
        $this->requireAuth();
        $orderId = (int) $this->input('order_id');
        $order   = $this->db->fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, Auth::id()]);
        if (!$order) $this->json(['success' => false], 404);

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Fichier invalide']);
        }

        $ext      = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowed)) {
            $this->json(['success' => false, 'message' => 'Format non supporté']);
        }
        if (($_FILES['proof']['size'] ?? 0) > 5 * 1024 * 1024) {
            $this->json(['success' => false, 'message' => 'Fichier trop volumineux (5 Mo maximum)']);
        }

        $filename = 'proof_' . $orderId . '_' . time() . '.' . $ext;
        $destDir  = UPLOAD_PATH . 'proofs/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        move_uploaded_file($_FILES['proof']['tmp_name'], $destDir . $filename);

        $this->db->update('orders', ['payment_proof' => $filename, 'payment_status' => 'pending'], 'id = ?', ['id' => $orderId]);
        $this->json(['success' => true, 'message' => 'Justificatif envoyé. En attente de validation.']);
    }

    /**
     * Marque la commande payée et attribue les clés aux articles pas encore livrés.
     * Idempotent : un nouvel appel (double clic admin, webhook rejoué) ne livre que les articles
     * restants et ne consomme jamais une clé deux fois. Commande « completed » seulement si tout est livré.
     *
     * @return array{found:bool,newly_paid:bool,delivered:int,pending:int}
     */
    public function deliverOrder(int $orderId): array {
        $result = ['found' => false, 'newly_paid' => false, 'delivered' => 0, 'pending' => 0];

        $this->db->beginTransaction();
        try {
            $order = $this->db->fetch(
                'SELECT id, status, payment_status, paid_at FROM orders WHERE id = ? FOR UPDATE',
                [$orderId]
            );
            if (!$order || in_array($order['status'], ['cancelled', 'refunded'], true)) {
                $this->db->rollback();
                return $result;
            }
            $result['found']      = true;
            $result['newly_paid'] = $order['payment_status'] !== 'paid';

            $items = $this->db->fetchAll(
                'SELECT id, product_id, quantity FROM order_items WHERE order_id = ? AND delivered = 0',
                [$orderId]
            );
            foreach ($items as $item) {
                $qty  = (int) $item['quantity'];
                $keys = $item['product_id'] === null ? [] : $this->db->fetchAll(
                    "SELECT id, key_value FROM product_keys
                     WHERE product_id = ? AND is_used = 0 ORDER BY id LIMIT ? FOR UPDATE",
                    [$item['product_id'], $qty]
                );
                if (count($keys) < $qty) {
                    // Clés insuffisantes ou livraison manuelle : l'article reste à livrer
                    $result['pending']++;
                    continue;
                }
                $this->db->query(
                    "UPDATE order_items SET delivered = 1, delivered_at = NOW(), delivery_data = ? WHERE id = ?",
                    [implode("\n", array_column($keys, 'key_value')), $item['id']]
                );
                foreach ($keys as $k) {
                    $this->db->query(
                        "UPDATE product_keys SET is_used = 1, order_id = ?, used_at = NOW() WHERE id = ?",
                        [$orderId, $k['id']]
                    );
                }
                $this->db->query(
                    "UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ? AND stock IS NOT NULL",
                    [$qty, $item['product_id']]
                );
                $result['delivered']++;
            }

            $this->db->update('orders', [
                'status'         => $result['pending'] > 0 ? 'processing' : 'completed',
                'payment_status' => 'paid',
                'paid_at'        => $order['paid_at'] ?? date('Y-m-d H:i:s'),
            ], 'id = ?', [$orderId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        // Ticket PDF → email + WhatsApp : au premier paiement, puis à chaque nouvelle livraison
        if ($result['newly_paid'] || $result['delivered'] > 0) {
            try {
                (new \App\Services\OrderTicketService($this->db))->generateAndSend($orderId);
            } catch (\Throwable $e) {
                error_log('Order ticket send failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    public function downloadTicket(string $id): void {
        $orderId = (int) $id;
        $token = (string) ($_GET['t'] ?? '');
        $order = $this->db->fetch('SELECT * FROM orders WHERE id = ?', [$orderId]);
        if (!$order) {
            $this->abort(404);
        }

        $svc = new \App\Services\OrderTicketService($this->db);
        $allowed = $svc->verifyToken($orderId, $order['order_number'], $token);
        if (!$allowed && \App\Core\Auth::check()) {
            $uid = (int) \App\Core\Auth::id();
            $allowed = ((int) $order['user_id'] === $uid) || \App\Core\Auth::isAdmin();
        }
        if (!$allowed) {
            $this->abort(403);
        }

        $path = $svc->ensurePdf($orderId);
        if (!$path || !is_file($path)) {
            $this->abort(404);
        }

        $filename = 'ticket-' . preg_replace('/[^A-Za-z0-9_-]/', '', $order['order_number']) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($path);
        exit;
    }
}
