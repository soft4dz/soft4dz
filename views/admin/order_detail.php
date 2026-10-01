<?php $pageTitle = 'Commande ' . e($order['order_number']); ?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/orders') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <div>
      <h1><?= e($order['order_number']) ?></h1>
      <p style="color:var(--text-secondary);font-size:0.875rem;margin:0.25rem 0 0"><?= formatDate($order['created_at'], 'd/m/Y H:i') ?></p>
    </div>
  </div>
  <div class="flex gap-2">
    <?= statusBadge($order['payment_status']) ?>
    <?= statusBadge($order['status']) ?>
  </div>
</div>

<div class="admin-grid-2">
  <div class="admin-stack">
    <div class="data-table-wrap">
      <div class="data-table-header"><div class="data-table-title">Articles commandés</div></div>
      <div class="table-wrap" style="border:none;border-radius:0">
        <table>
          <thead><tr><th>Produit</th><th>Qté</th><th>Prix</th><th>Sous-total</th><th>Livraison</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td style="font-weight:600"><?= e($item['product_name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td><?= formatPrice($item['price']) ?></td>
              <td style="font-weight:700"><?= formatPrice($item['subtotal']) ?></td>
              <td>
                <?php if ($item['delivered']): ?>
                  <span class="badge badge-success">✓ Livré</span>
                  <?php if ($item['delivery_data']): ?>
                  <div class="admin-key-mono" style="margin-top:0.25rem"><?= e($item['delivery_data']) ?></div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge badge-secondary">En attente</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($order['payment_proof']): ?>
    <div class="card">
      <h3 style="margin-bottom:1rem">Justificatif de paiement</h3>
      <?php
      $ext = pathinfo($order['payment_proof'], PATHINFO_EXTENSION);
      $src = UPLOAD_URL . 'proofs/' . $order['payment_proof'];
      ?>
      <?php if (in_array($ext, ['jpg','jpeg','png','webp','gif'], true)): ?>
        <img src="<?= $src ?>" alt="Preuve" style="max-width:100%;border-radius:var(--radius);border:1px solid var(--border)">
      <?php else: ?>
        <a href="<?= $src ?>" target="_blank" class="btn btn-outline"><i class="bi bi-file-pdf"></i> Voir le fichier</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="admin-stack">
    <div class="card">
      <h4 style="margin-bottom:1rem">Client</h4>
      <div style="font-weight:700"><?= e($order['customer']) ?></div>
      <div style="font-size:0.875rem;color:var(--text-muted)"><?= e($order['customer_email']) ?></div>
      <?php if ($order['customer_phone']): ?><div style="font-size:0.875rem;color:var(--text-muted)"><?= e($order['customer_phone']) ?></div><?php endif; ?>
      <a href="<?= url('admin/users/' . $order['user_id']) ?>" class="btn btn-ghost btn-sm" style="margin-top:0.75rem">Voir le profil →</a>
    </div>

    <div class="card">
      <h4 style="margin-bottom:1rem">Totaux</h4>
      <div class="admin-stack" style="gap:0.625rem">
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)">
          <span>Sous-total</span><span><?= formatPrice($order['subtotal']) ?></span>
        </div>
        <?php if ($order['discount'] > 0): ?>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--success)">
          <span>Remise</span><span>-<?= formatPrice($order['discount']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($order['tax'] > 0): ?>
        <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)">
          <span>TVA</span><span><?= formatPrice($order['tax']) ?></span>
        </div>
        <?php endif; ?>
        <hr class="divider">
        <div style="display:flex;justify-content:space-between;font-weight:800">
          <span>Total</span><span><?= formatPrice($order['total']) ?></span>
        </div>
      </div>
    </div>

    <?php if ($order['payment_status'] === 'pending'): ?>
    <div class="card">
      <h4 style="margin-bottom:1rem">Valider le paiement</h4>
      <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:1rem">Confirme la réception et livre les produits.</p>
      <form action="<?= url('admin/orders/' . $order['id'] . '/validate') ?>" method="POST">
        <?= csrf() ?>
        <button type="submit" class="btn btn-success w-full" onclick="return confirm('Confirmer la validation du paiement ?')">
          <i class="bi bi-check-circle"></i> Valider le paiement
        </button>
      </form>
    </div>
    <?php endif; ?>

    <?php if ($order['payment_status'] === 'paid' || $order['status'] === 'completed'): ?>
    <?php
      $ticketSvc = new \App\Services\OrderTicketService();
      $ticketUrl = $ticketSvc->publicTicketUrl($order);
      $waLink = !empty($order['customer_phone'])
        ? \App\Services\WhatsAppService::waMeLink(
            $order['customer_phone'],
            "Bonjour, voici votre ticket Soft4dz {$order['order_number']} : {$ticketUrl}"
          )
        : null;
    ?>
    <div class="card">
      <h4 style="margin-bottom:1rem">Ticket PDF</h4>
      <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:1rem">
        Envoyé automatiquement par email<?= \App\Services\WhatsAppService::isConfigured() ? ' et WhatsApp' : '' ?> à la validation.
      </p>
      <div class="admin-stack" style="gap:0.5rem">
        <a href="<?= e($ticketUrl) ?>" class="btn btn-outline w-full" target="_blank">
          <i class="bi bi-file-earmark-pdf"></i> Télécharger le PDF
        </a>
        <form action="<?= url('admin/orders/' . $order['id'] . '/resend-ticket') ?>" method="POST">
          <?= csrf() ?>
          <button type="submit" class="btn btn-primary w-full">
            <i class="bi bi-send"></i> Renvoyer email / WhatsApp
          </button>
        </form>
        <?php if ($waLink): ?>
        <a href="<?= e($waLink) ?>" class="btn btn-success w-full" target="_blank" rel="noopener">
          <i class="bi bi-whatsapp"></i> Ouvrir WhatsApp (manuel)
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <h4 style="margin-bottom:1rem">Statut & notes</h4>
      <form action="<?= url('admin/orders/' . $order['id'] . '/status') ?>" method="POST">
        <?= csrf() ?>
        <div class="form-group">
          <label class="form-label">Statut commande</label>
          <select name="status" class="form-control">
            <?php foreach (['pending'=>'En attente','paid'=>'Payée','processing'=>'En traitement','completed'=>'Terminée','cancelled'=>'Annulée','refunded'=>'Remboursée'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Notes internes / client..."><?= e($order['notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-full"><i class="bi bi-check"></i> Enregistrer</button>
      </form>
    </div>
  </div>
</div>
