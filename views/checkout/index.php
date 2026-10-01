<?php $pageTitle = 'Finaliser la commande'; ?>

<div class="page-shell page-shell--checkout">
  <div class="container page-section" style="max-width:960px;padding-bottom:4rem">
    <h1 class="page-title" style="font-size:1.85rem;margin-bottom:0.5rem">Finaliser la commande</h1>
    <p class="page-subtitle" style="text-align:left;margin-inline:0;margin-bottom:2rem">Choisissez votre mode de paiement et confirmez votre commande.</p>

    <form action="<?= url('checkout') ?>" method="POST" id="checkoutForm">
      <?= csrf() ?>

      <div class="split-layout">

        <!-- Payment -->
        <div>
          <div class="card" style="margin-bottom:1.5rem">
            <h3 style="margin-bottom:1.5rem"><i class="bi bi-credit-card" style="color:var(--primary-light)"></i> Mode de paiement</h3>

            <?php
            $methods = [
              ['id'=>'cib',           'icon'=>'bi-credit-card',  'label'=>'Carte CIB',        'desc'=>'Paiement par carte bancaire CIB (Algérie)'],
              ['id'=>'edahabia',      'icon'=>'bi-phone',        'label'=>'Edahabia / BaridiMob', 'desc'=>'Paiement via votre application BaridiMob'],
              ['id'=>'bank_transfer', 'icon'=>'bi-bank',         'label'=>'Virement bancaire', 'desc'=>'Virement sur notre compte, joindre le reçu'],
            ];
            if (!empty($chargilyEnabled)) {
              array_unshift($methods, [
                'id'    => 'chargily',
                'icon'  => 'bi-lightning-charge',
                'label' => 'Chargily Pay',
                'desc'  => 'Paiement en ligne sécurisé (CIB, EDAHABIA, etc.) — redirection vers Chargily Pay',
              ]);
            }
            foreach ($methods as $m):
            ?>
            <label class="payment-method-label" style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;margin-bottom:0.75rem"
                   id="lbl_<?= $m['id'] ?>">
              <input type="radio" name="payment_method" value="<?= $m['id'] ?>" style="accent-color:var(--primary);margin-top:3px;flex-shrink:0"
                     onchange="selectMethod('<?= $m['id'] ?>')" <?= $m['id'] === (!empty($chargilyEnabled) ? 'chargily' : 'bank_transfer') ? 'checked' : '' ?>>
              <div style="display:flex;align-items:center;gap:0.875rem;flex:1">
                <div style="width:44px;height:44px;background:color-mix(in srgb,var(--primary) 10%,transparent);border-radius:var(--radius);display:grid;place-items:center;font-size:1.2rem;color:var(--primary-light)">
                  <i class="bi <?= $m['icon'] ?>"></i>
                </div>
                <div>
                  <div style="font-weight:700;font-size:0.9rem"><?= $m['label'] ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted)"><?= $m['desc'] ?></div>
                </div>
              </div>
            </label>
            <?php endforeach; ?>

            <!-- Bank transfer info -->
            <div id="bankInfo" style="background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:var(--radius);padding:1.25rem;margin-top:0.5rem">
              <div style="font-weight:700;font-size:0.875rem;color:#60a5fa;margin-bottom:0.75rem">
                <i class="bi bi-info-circle"></i> Informations bancaires
              </div>
              <?php if ($bankInfo): ?>
              <div style="font-size:0.875rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:0.375rem">
                <div><strong>Banque :</strong> <?= e($bankInfo['bank'] ?? '') ?></div>
                <div><strong>RIB :</strong> <code style="background:var(--bg-surface);padding:0.1rem 0.5rem;border-radius:4px"><?= e($bankInfo['rib'] ?? '') ?></code></div>
                <div><strong>Bénéficiaire :</strong> <?= e($bankInfo['name'] ?? '') ?></div>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="card">
            <h3 style="margin-bottom:1rem"><i class="bi bi-chat-text" style="color:var(--primary-light)"></i> Notes (optionnel)</h3>
            <textarea name="notes" class="form-control" rows="3" placeholder="Instructions spéciales, remarques..."></textarea>
          </div>
        </div>

        <!-- Summary -->
        <div class="card summary-sidebar">
          <h3 style="margin-bottom:1.5rem">Récapitulatif</h3>
          <?php foreach ($items as $item): ?>
          <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:0.875rem">
            <img src="<?= productImageUrl($item['image']) ?>" style="width:44px;height:44px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0" alt="">
            <div style="flex:1;min-width:0">
              <div style="font-size:0.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($item['name']) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted)">x<?= $item['quantity'] ?></div>
            </div>
            <div style="font-weight:700;font-size:0.875rem;flex-shrink:0"><?= formatPrice(($item['unit_price'] ?? $item['price']) * $item['quantity']) ?></div>
          </div>
          <?php endforeach; ?>

          <hr class="divider">

          <div style="display:flex;flex-direction:column;gap:0.625rem;margin-bottom:1.5rem">
            <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)">
              <span>Sous-total</span><span><?= formatPrice($summary['subtotal']) ?></span>
            </div>
            <?php if ($summary['discount'] > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--success)">
              <span>Remise</span><span>-<?= formatPrice($summary['discount']) ?></span>
            </div>
            <?php endif; ?>
            <hr class="divider">
            <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.3rem">
              <span>Total</span><span class="gradient-text"><?= formatPrice($summary['total']) ?></span>
            </div>
          </div>

          <button type="submit" class="btn btn-gradient btn-lg w-full checkout-submit-desktop">
            <i class="bi bi-lock"></i> Confirmer la commande
          </button>

          <div style="display:flex;flex-direction:column;gap:0.5rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:var(--text-muted)">
              <i class="bi bi-shield-check" style="color:var(--success)"></i> Paiement sécurisé et crypté
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:var(--text-muted)">
              <i class="bi bi-lightning" style="color:var(--warning)"></i> Livraison après confirmation du paiement
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="checkout-sticky-bar" aria-label="Confirmer la commande">
    <div class="checkout-sticky-total">
      <span class="checkout-sticky-label">Total</span>
      <span class="checkout-sticky-amount gradient-text"><?= formatPrice($summary['total']) ?></span>
    </div>
    <button type="submit" form="checkoutForm" class="btn btn-gradient checkout-sticky-btn">
      <i class="bi bi-lock" aria-hidden="true"></i> Confirmer
    </button>
  </div>
</div>

<script>
function selectMethod(id) {
  document.getElementById('bankInfo').style.display = id === 'bank_transfer' ? 'block' : 'none';
}
selectMethod('<?= !empty($chargilyEnabled) ? 'chargily' : 'bank_transfer' ?>');
</script>
