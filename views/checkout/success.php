<?php $pageTitle = 'Commande confirmée'; ?>

<div class="page-shell">
  <div class="container page-section" style="max-width:640px">
    <div class="card text-center">
      <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
      <h1 style="font-size:1.75rem;margin-bottom:0.75rem;color:var(--success)">Commande confirmée !</h1>
      <p style="color:var(--text-secondary);margin-bottom:0.5rem">Numéro de commande : <strong><?= e($order['order_number']) ?></strong></p>
      <p style="color:var(--text-secondary);margin-bottom:2rem">
        Merci pour votre confiance. Votre commande est enregistrée et en attente de traitement.
      </p>

      <?php if ($order['payment_method'] === 'chargily' && $order['payment_status'] !== 'paid'): ?>
      <div class="alert alert-info" style="text-align:left">
        <i class="bi bi-hourglass-split"></i>
        <div>
          <strong>Paiement Chargily Pay</strong>
          <p style="font-size:0.85rem;margin-top:0.35rem">
            <?php if ($order['payment_status'] === 'failed'): ?>
              Le paiement n’a pas abouti. Ouvrez votre commande depuis le tableau de bord pour réessayer.
            <?php else: ?>
              Si vous venez de payer, la confirmation arrive sous quelques instants (webhook). Sinon, actualisez la page ou consultez « Mes commandes ».
            <?php endif; ?>
          </p>
        </div>
      </div>
      <?php elseif ($order['payment_method'] === 'bank_transfer' && $order['payment_status'] !== 'paid'): ?>
      <div class="alert alert-info" style="text-align:left">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          <strong>Action requise : Effectuez votre virement</strong>
          <?php if ($bankInfo): ?>
          <div style="margin-top:0.5rem;font-size:0.85rem">
            Banque : <strong><?= e($bankInfo['bank'] ?? '') ?></strong> —
            RIB : <code><?= e($bankInfo['rib'] ?? '') ?></code>
          </div>
          <?php endif; ?>
          <p style="font-size:0.8rem;margin-top:0.5rem">Joignez votre reçu de virement ci-dessous pour accélérer la validation.</p>
        </div>
      </div>

      <!-- Upload proof -->
      <div style="margin:1.5rem 0;padding:1.5rem;background:var(--bg-surface);border-radius:var(--radius);border:2px dashed var(--border)">
        <h4 style="margin-bottom:0.75rem">Joindre le reçu de paiement</h4>
        <input type="file" id="proofFile" accept=".jpg,.jpeg,.png,.pdf" style="display:none">
        <button class="btn btn-outline" onclick="document.getElementById('proofFile').click()">
          <i class="bi bi-upload"></i> Choisir le fichier
        </button>
        <div id="proofName" style="font-size:0.8rem;color:var(--text-muted);margin-top:0.5rem"></div>
        <button class="btn btn-primary" id="uploadBtn" style="margin-top:1rem;display:none">
          <i class="bi bi-send"></i> Envoyer le justificatif
        </button>
      </div>
      <?php elseif ($order['payment_status'] === 'paid'): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <div>
          <strong>Paiement confirmé !</strong><br>
          <span style="font-size:0.85rem">Vos produits ont été livrés dans votre compte. Un ticket PDF vous a été envoyé par email (et WhatsApp si un numéro est enregistré).</span>
        </div>
      </div>
      <?php
        $ticketSvc = new \App\Services\OrderTicketService();
        $ticketUrl = $ticketSvc->publicTicketUrl($order);
      ?>
      <a href="<?= e($ticketUrl) ?>" class="btn btn-outline" style="margin-bottom:1rem">
        <i class="bi bi-file-earmark-pdf"></i> Télécharger mon ticket PDF
      </a>
      <?php endif; ?>

      <!-- Order items -->
      <div style="text-align:left;margin:2rem 0">
        <h3 style="margin-bottom:1rem">Articles commandés</h3>
        <?php foreach ($items as $item): ?>
        <div style="display:flex;align-items:center;gap:1rem;padding:0.875rem;background:var(--bg-surface);border-radius:var(--radius);margin-bottom:0.5rem">
          <div style="flex:1;font-size:0.875rem">
            <div style="font-weight:700"><?= e($item['product_name']) ?></div>
            <div style="color:var(--text-muted);font-size:0.75rem">x<?= $item['quantity'] ?></div>
          </div>
          <div style="font-weight:700"><?= formatPrice($item['subtotal']) ?></div>
          <?php if ($item['delivered'] && $item['delivery_data']): ?>
          <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:8px;padding:0.5rem 0.875rem;font-size:0.8rem;color:#34d399;font-family:monospace;max-width:200px;word-break:break-all">
            <?= e($item['delivery_data']) ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="font-size:1.5rem;font-weight:800;margin-bottom:2rem">
        Total payé : <span class="gradient-text"><?= formatPrice($order['total']) ?></span>
      </div>

      <div class="flex gap-3 justify-center" style="flex-wrap:wrap">
        <a href="<?= url('dashboard/orders/' . $order['id']) ?>" class="btn btn-primary">
          <i class="bi bi-bag-check"></i> Voir la commande
        </a>
        <a href="<?= url('products') ?>" class="btn btn-outline">
          <i class="bi bi-shop"></i> Continuer les achats
        </a>
      </div>
    </div>
  </div>
</div>

<script>
const proofFile = document.getElementById('proofFile');
const uploadBtn = document.getElementById('uploadBtn');
proofFile?.addEventListener('change', () => {
  document.getElementById('proofName').textContent = proofFile.files[0]?.name || '';
  uploadBtn.style.display = proofFile.files[0] ? 'inline-flex' : 'none';
});
uploadBtn?.addEventListener('click', () => {
  const fd = new FormData();
  fd.append('order_id', '<?= $order['id'] ?>');
  fd.append('proof', proofFile.files[0]);
  uploadBtn.textContent = 'Envoi...';
  fetch('<?= url('checkout/upload-proof') ?>', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        uploadBtn.textContent = 'Envoyé';
        uploadBtn.className = 'btn btn-success';
      } else alert(d.message);
    });
});
</script>
