<?php $pageTitle = 'Commande ' . e($order['order_number']); ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:2rem">
  <a href="<?= url('dashboard/orders') ?>" class="btn btn-ghost btn-sm">← Retour</a>
  <h1 style="font-size:1.25rem"><?= e($order['order_number']) ?></h1>
  <?= statusBadge($order['payment_status']) ?>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem">
  <div class="data-table-wrap">
    <div class="data-table-header"><div class="data-table-title">Articles</div></div>
    <table>
      <thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Livraison</th></tr></thead>
      <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
          <td style="font-weight:600"><?= e($item['product_name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= formatPrice($item['subtotal']) ?></td>
          <td>
            <?php if ($item['delivered'] && $item['delivery_data']): ?>
            <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:0.5rem;font-size:0.78rem;font-family:monospace;color:#34d399;word-break:break-all">
              <?= e($item['delivery_data']) ?>
            </div>
            <?php elseif ($item['delivered']): ?>
            <span class="badge badge-success">Livré</span>
            <?php else: ?>
            <span class="badge badge-warning">En attente</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h4 style="margin-bottom:1rem">Récapitulatif</h4>
    <div style="display:flex;flex-direction:column;gap:0.625rem;margin-bottom:1.25rem">
      <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)"><span>Sous-total</span><span><?= formatPrice($order['subtotal']) ?></span></div>
      <?php if ($order['discount'] > 0): ?><div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--success)"><span>Remise</span><span>-<?= formatPrice($order['discount']) ?></span></div><?php endif; ?>
      <hr class="divider">
      <div style="display:flex;justify-content:space-between;font-weight:800"><span>Total</span><span><?= formatPrice($order['total']) ?></span></div>
    </div>
    <div style="font-size:0.78rem;color:var(--text-muted);display:flex;flex-direction:column;gap:0.375rem">
      <div>Méthode : <strong><?= e(str_replace('_', ' ', $order['payment_method'])) ?></strong></div>
      <div>Date : <strong><?= formatDate($order['created_at'], 'd/m/Y H:i') ?></strong></div>
      <?php if ($order['paid_at']): ?><div>Payé le : <strong><?= formatDate($order['paid_at'], 'd/m/Y H:i') ?></strong></div><?php endif; ?>
    </div>
    <?php if ($order['payment_status'] === 'paid' || $order['status'] === 'completed'): ?>
    <?php $ticketUrl = (new \App\Services\OrderTicketService())->publicTicketUrl($order); ?>
    <a href="<?= e($ticketUrl) ?>" class="btn btn-outline w-full" style="margin-top:1rem">
      <i class="bi bi-file-earmark-pdf"></i> Télécharger le ticket PDF
    </a>
    <?php endif; ?>
    <?php if ($order['payment_status'] === 'pending'): ?>
    <div class="alert alert-warning mt-4" style="font-size:0.8rem">
      <i class="bi bi-clock"></i> Votre paiement est en cours de vérification.
    </div>
    <?php endif; ?>
    <a href="<?= url('dashboard/tickets') ?>" class="btn btn-outline btn-sm w-full mt-4">
      <i class="bi bi-headset"></i> Ouvrir un ticket support
    </a>
  </div>
</div>
