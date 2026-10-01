<?php

namespace App\Services;

use App\Core\Database;

/**
 * Generate order ticket PDF and deliver via email + WhatsApp.
 */
class OrderTicketService {
    private Database $db;

    public function __construct(?Database $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * @return array{ok:bool,pdf:?string,email:bool,whatsapp:bool,errors:string[]}
     */
    public function generateAndSend(int $orderId): array {
        $result = ['ok' => false, 'pdf' => null, 'email' => false, 'whatsapp' => false, 'errors' => []];

        $ctx = $this->loadOrderContext($orderId);
        if (!$ctx) {
            $result['errors'][] = 'Commande introuvable.';
            return $result;
        }
        [$order, $items] = $ctx;

        $pdfPath = $this->buildPdf($order, $items);
        if (!$pdfPath) {
            $result['errors'][] = 'Échec génération PDF.';
            return $result;
        }
        $result['pdf'] = $pdfPath;
        $result['ok'] = true;

        $filename = 'ticket-' . preg_replace('/[^A-Za-z0-9_-]/', '', $order['order_number']) . '.pdf';
        $downloadUrl = $this->publicTicketUrl($order);

        if (!empty($order['customer_email'])) {
            $sent = MailService::send(
                $order['customer_email'],
                'Votre ticket Soft4dz — ' . $order['order_number'],
                $this->emailHtml($order, $items, $downloadUrl),
                $this->emailText($order, $downloadUrl),
                [['path' => $pdfPath, 'name' => $filename, 'mime' => 'application/pdf']]
            );
            $result['email'] = $sent;
            if (!$sent) {
                $result['errors'][] = 'Email non envoyé (vérifiez MAIL_* dans .env).';
            }
        } else {
            $result['errors'][] = 'Client sans email.';
        }

        $phone = trim((string) ($order['customer_phone'] ?? ''));
        if ($phone !== '') {
            $caption = "Soft4dz — Ticket {$order['order_number']}\nTotal: " . number_format((float)$order['total'], 2, ',', ' ') . " {$order['currency']}\nMerci pour votre achat !";
            $waOk = false;
            if (WhatsAppService::isConfigured()) {
                $waOk = WhatsAppService::sendDocument($phone, $pdfPath, $filename, $caption);
                if (!$waOk) {
                    $waOk = WhatsAppService::sendText(
                        $phone,
                        $caption . "\n\nTélécharger le ticket PDF :\n" . $downloadUrl
                    );
                }
            }
            $result['whatsapp'] = $waOk;
            if (!$waOk) {
                $result['errors'][] = WhatsAppService::isConfigured()
                    ? 'WhatsApp API: envoi échoué.'
                    : 'WhatsApp non configuré (WHATSAPP_TOKEN / WHATSAPP_PHONE_NUMBER_ID).';
            }
        } else {
            $result['errors'][] = 'Client sans téléphone (WhatsApp ignoré).';
        }

        return $result;
    }

    public function ensurePdf(int $orderId): ?string {
        $ctx = $this->loadOrderContext($orderId);
        if (!$ctx) {
            return null;
        }
        [$order, $items] = $ctx;
        $path = $this->ticketPath($order);
        if (is_file($path)) {
            return $path;
        }
        return $this->buildPdf($order, $items);
    }

    /** @return array{0:array,1:array}|null */
    private function loadOrderContext(int $orderId): ?array {
        $order = $this->db->fetch(
            "SELECT o.*, u.name AS customer, u.email AS customer_email, u.phone AS customer_phone
             FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE o.id = ?",
            [$orderId]
        );
        if (!$order) {
            return null;
        }
        $items = $this->db->fetchAll(
            'SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC',
            [$orderId]
        );
        return [$order, $items];
    }

    public function ticketPath(array $order): string {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $order['order_number']) ?: ('order-' . $order['id']);
        return UPLOAD_PATH . 'tickets/' . $safe . '.pdf';
    }

    public function publicTicketUrl(array $order): string {
        $token = $this->ticketToken((int) $order['id'], $order['order_number']);
        return rtrim(APP_URL, '/') . '/tickets/' . (int) $order['id'] . '?t=' . urlencode($token);
    }

    public function ticketToken(int $orderId, string $orderNumber): string {
        return hash_hmac('sha256', $orderId . '|' . $orderNumber, APP_SECRET);
    }

    public function verifyToken(int $orderId, string $orderNumber, string $token): bool {
        return hash_equals($this->ticketToken($orderId, $orderNumber), $token);
    }

    private function buildPdf(array $order, array $items): ?string {
        $pdf = new SimplePdf('Ticket ' . $order['order_number']);
        $pdf->line(APP_NAME, 18, 'B');
        $pdf->line('Ticket de commande / Order ticket', 10, 'I');
        $pdf->spacer(6);
        $pdf->hr();
        $pdf->pair('N commande', $order['order_number']);
        $pdf->pair('Date', date('d/m/Y H:i', strtotime($order['paid_at'] ?? $order['created_at'])));
        $pdf->pair('Client', $order['customer'] ?? '');
        $pdf->pair('Email', $order['customer_email'] ?? '');
        if (!empty($order['customer_phone'])) {
            $pdf->pair('Telephone', $order['customer_phone']);
        }
        $pdf->pair('Paiement', strtoupper((string) $order['payment_method']) . ' / ' . $order['payment_status']);
        $pdf->pair('Statut', $order['status']);
        $pdf->spacer(4);
        $pdf->hr();
        $pdf->line('Articles', 12, 'B');
        $pdf->spacer(2);

        foreach ($items as $item) {
            $pdf->line($item['product_name'], 11, 'B');
            $pdf->line(
                'Qte: ' . $item['quantity'] . '  |  Prix: ' . number_format((float)$item['price'], 2, ',', ' ') . ' ' . $order['currency']
                . '  |  Sous-total: ' . number_format((float)$item['subtotal'], 2, ',', ' ') . ' ' . $order['currency'],
                9
            );
            if (!empty($item['delivery_data'])) {
                $pdf->line('Livraison / codes :', 9, 'B');
                foreach (preg_split('/\r\n|\r|\n/', (string) $item['delivery_data']) as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $pdf->line('  ' . $line, 9);
                    }
                }
            }
            $pdf->spacer(4);
        }

        $pdf->hr();
        $pdf->pair('Sous-total', number_format((float)$order['subtotal'], 2, ',', ' ') . ' ' . $order['currency']);
        if ((float)$order['discount'] > 0) {
            $pdf->pair('Remise', '-' . number_format((float)$order['discount'], 2, ',', ' ') . ' ' . $order['currency']);
        }
        if ((float)$order['tax'] > 0) {
            $pdf->pair('TVA', number_format((float)$order['tax'], 2, ',', ' ') . ' ' . $order['currency']);
        }
        $pdf->pair('TOTAL', number_format((float)$order['total'], 2, ',', ' ') . ' ' . $order['currency'], 12);
        $pdf->spacer(10);
        $pdf->hr();
        $pdf->line('Merci pour votre confiance.', 10, 'I');
        $pdf->line('Support: ' . ($this->setting('site_email', MAIL_FROM_ADDRESS) ?: MAIL_FROM_ADDRESS), 9);
        $phone = $this->setting('site_phone', '');
        if ($phone) {
            $pdf->line('Tel / WhatsApp: ' . $phone, 9);
        }
        $pdf->line(rtrim(APP_URL, '/'), 8, 'I');

        $path = $this->ticketPath($order);
        return $pdf->save($path) ? $path : null;
    }

    private function setting(string $key, string $default = ''): string {
        static $cache = [];
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        try {
            $row = $this->db->fetch('SELECT value FROM settings WHERE `key` = ? LIMIT 1', [$key]);
            $cache[$key] = (string) ($row['value'] ?? $default);
        } catch (\Throwable $e) {
            $cache[$key] = $default;
        }
        return $cache[$key];
    }

    private function emailHtml(array $order, array $items, string $downloadUrl): string {
        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr><td style="padding:8px;border-bottom:1px solid #eee">' . htmlspecialchars($item['product_name'])
                . '</td><td style="padding:8px;border-bottom:1px solid #eee">' . (int)$item['quantity']
                . '</td><td style="padding:8px;border-bottom:1px solid #eee;text-align:right">'
                . number_format((float)$item['subtotal'], 2, ',', ' ') . ' ' . htmlspecialchars($order['currency'])
                . '</td></tr>';
        }
        $name = htmlspecialchars($order['customer'] ?? '');
        $num = htmlspecialchars($order['order_number']);
        $total = number_format((float)$order['total'], 2, ',', ' ') . ' ' . htmlspecialchars($order['currency']);
        $url = htmlspecialchars($downloadUrl);
        return <<<HTML
<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;color:#111;line-height:1.5">
  <h2 style="color:#2563EB">Votre ticket Soft4dz</h2>
  <p>Bonjour {$name},</p>
  <p>Votre commande <strong>{$num}</strong> est confirmée. Le ticket PDF est joint à cet email.</p>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <thead><tr style="background:#f8fafc;text-align:left">
      <th style="padding:8px">Produit</th><th style="padding:8px">Qté</th><th style="padding:8px;text-align:right">Montant</th>
    </tr></thead>
    <tbody>{$rows}</tbody>
  </table>
  <p><strong>Total : {$total}</strong></p>
  <p><a href="{$url}" style="display:inline-block;background:#2563EB;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none">Télécharger le ticket PDF</a></p>
  <p style="color:#64748b;font-size:13px">Conservez ce ticket. Les codes de livraison y figurent également.</p>
  <p>— L'équipe Soft4dz</p>
</body></html>
HTML;
    }

    private function emailText(array $order, string $downloadUrl): string {
        return "Bonjour {$order['customer']},\n\n"
            . "Votre commande {$order['order_number']} est confirmée.\n"
            . "Total: " . number_format((float)$order['total'], 2, ',', ' ') . " {$order['currency']}\n\n"
            . "Télécharger le ticket PDF:\n{$downloadUrl}\n\n"
            . "— Soft4dz\n";
    }
}
