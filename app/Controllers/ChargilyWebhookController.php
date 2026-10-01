<?php

namespace App\Controllers;

use App\Core\Database;
use App\Services\ChargilyPayService;

/**
 * Webhook Chargily Pay (POST JSON, header signature).
 */
class ChargilyWebhookController {
    public function handle(): void {
        header('Content-Type: application/json; charset=UTF-8', true);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '{}';
            return;
        }

        $raw = file_get_contents('php://input') ?: '';
        $sig = $this->getSignatureHeader();

        if (!ChargilyPayService::verifyWebhookSignature($raw, $sig)) {
            http_response_code(403);
            echo '{}';
            return;
        }

        $event = json_decode($raw, true);
        if (!is_array($event) || empty($event['type'])) {
            http_response_code(400);
            echo '{}';
            return;
        }

        $db = Database::getInstance();

        switch ($event['type']) {
            case 'checkout.paid':
                $this->onCheckoutPaid($db, is_array($event['data'] ?? null) ? $event['data'] : []);
                break;
            case 'checkout.failed':
            case 'checkout.canceled':
                $this->onCheckoutFailed($db, is_array($event['data'] ?? null) ? $event['data'] : []);
                break;
        }

        http_response_code(200);
        echo '{}';
    }

    private function getSignatureHeader(): ?string {
        $h = $_SERVER['HTTP_SIGNATURE'] ?? null;
        if (is_string($h) && $h !== '') {
            return $h;
        }
        if (function_exists('getallheaders')) {
            foreach (getallheaders() ?: [] as $name => $value) {
                if (strtolower((string) $name) === 'signature') {
                    return is_string($value) ? $value : null;
                }
            }
        }
        return null;
    }

    private function onCheckoutPaid(Database $db, array $data): void {
        $checkoutId = (string) ($data['id'] ?? '');
        $meta        = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $orderId     = isset($meta['order_id']) ? (int) $meta['order_id'] : 0;

        $order = null;
        if ($orderId > 0) {
            $order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
        }
        if (!$order && $checkoutId !== '') {
            $order = $db->fetch("SELECT * FROM orders WHERE payment_ref = ?", [$checkoutId]);
        }
        if (!$order || $order['payment_method'] !== 'chargily') {
            return;
        }
        if ($order['payment_status'] === 'paid') {
            return;
        }

        if ($checkoutId !== '' && (string) ($order['payment_ref'] ?? '') === '') {
            $db->update('orders', ['payment_ref' => $checkoutId], 'id = ?', ['id' => (int) $order['id']]);
        }

        $checkout = new CheckoutController();
        $checkout->deliverOrder((int) $order['id']);

        $db->insert('notifications', [
            'user_id' => $order['user_id'],
            'type'    => 'payment_confirmed',
            'title'   => 'Paiement confirmé',
            'body'    => 'Votre commande ' . ($order['order_number'] ?? '') . ' a été payée via Chargily Pay.',
            'url'     => '/dashboard/orders/' . $order['id'],
        ]);
    }

    private function onCheckoutFailed(Database $db, array $data): void {
        $checkoutId = (string) ($data['id'] ?? '');
        $meta        = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $orderId     = isset($meta['order_id']) ? (int) $meta['order_id'] : 0;

        $order = null;
        if ($orderId > 0) {
            $order = $db->fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);
        }
        if (!$order && $checkoutId !== '') {
            $order = $db->fetch("SELECT * FROM orders WHERE payment_ref = ?", [$checkoutId]);
        }
        if (!$order || $order['payment_method'] !== 'chargily' || $order['payment_status'] === 'paid') {
            return;
        }

        $db->update('orders', [
            'payment_status' => 'failed',
        ], 'id = ?', ['id' => (int) $order['id']]);
    }
}
