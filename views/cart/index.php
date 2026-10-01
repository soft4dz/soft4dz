<?php $pageTitle = 'Mon Panier'; ?>

<div class="page-shell">
  <div class="container page-section" style="padding-bottom:4rem">
    <h1 class="page-title" style="font-size:1.85rem;margin-bottom:2rem">Mon Panier
      <?php if (!empty($items)): ?><span style="font-size:1rem;color:var(--text-muted);font-weight:400">(<?= count($items) ?> article<?= count($items) > 1 ? 's' : '' ?>)</span><?php endif; ?>
    </h1>

    <?php if (empty($items)): ?>
    <div class="panel empty-state">
      <div class="empty-state-icon"><i class="bi bi-cart3"></i></div>
      <h2 style="margin-bottom:0.75rem">Votre panier est vide</h2>
      <p style="color:var(--text-secondary);margin-bottom:2rem">Découvrez notre catalogue et ajoutez des produits à votre panier.</p>
      <a href="<?= url('products') ?>" class="btn btn-gradient btn-lg">Explorer le catalogue</a>
    </div>
    <?php else: ?>

    <div class="split-layout-wide">
      <!-- Cart Items -->
      <div>
        <div class="card" style="padding:0">
          <?php foreach ($items as $i => $item): ?>
          <div style="display:flex;align-items:center;gap:1.25rem;padding:1.25rem 1.5rem;border-bottom:<?= $i < count($items)-1 ? '1px solid var(--border)' : 'none' ?>">
            <img src="<?= productImageUrl($item['image']) ?>" alt="<?= e($item['name']) ?>"
                 style="width:72px;height:72px;border-radius:var(--radius);object-fit:cover;flex-shrink:0">
            <div style="flex:1;min-width:0">
              <a href="<?= url('products/' . $item['slug']) ?>" style="font-weight:700;display:block;margin-bottom:0.25rem;color:var(--text-primary)"><?= e($item['name']) ?></a>
              <div style="font-size:0.8rem;color:var(--text-muted)"><?= formatPrice($item['unit_price'] ?? $item['price']) ?> / unité</div>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;flex-shrink:0">
              <button class="btn btn-outline btn-sm qty-btn" data-id="<?= (int) $item['product_id'] ?>" data-action="minus" style="width:30px;height:30px;padding:0">−</button>
              <span style="font-weight:700;min-width:24px;text-align:center"><?= $item['quantity'] ?></span>
              <button class="btn btn-outline btn-sm qty-btn" data-id="<?= (int) $item['product_id'] ?>" data-action="plus" style="width:30px;height:30px;padding:0">+</button>
            </div>
            <div style="font-weight:800;min-width:100px;text-align:right;flex-shrink:0">
              <?= formatPrice(($item['unit_price'] ?? $item['price']) * $item['quantity']) ?>
            </div>
            <button class="btn btn-ghost btn-sm remove-item" data-id="<?= (int) $item['product_id'] ?>" style="color:var(--danger);padding:0.25rem">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <div style="display:flex;justify-content:space-between;margin-top:1rem">
          <a href="<?= url('products') ?>" class="btn btn-ghost">
            <i class="bi bi-arrow-left"></i> Continuer les achats
          </a>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="card summary-sidebar">
        <h3 style="margin-bottom:1.5rem">Récapitulatif</h3>

        <!-- Coupon -->
        <div style="margin-bottom:1.5rem">
          <div style="font-size:0.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.5rem">Code promo</div>
          <div style="display:flex;gap:0.5rem">
            <input type="text" id="couponInput" class="form-control" placeholder="Entrez votre code" style="flex:1">
            <button class="btn btn-outline" id="applyCoupon"><i class="bi bi-tag"></i></button>
          </div>
          <?php if ($coupon): ?>
          <div class="alert alert-success mt-2" style="padding:0.5rem 0.75rem;font-size:0.8rem">
            <i class="bi bi-check-circle"></i> Code "<?= e($coupon['code']) ?>" appliqué !
          </div>
          <?php endif; ?>
        </div>

        <hr class="divider">

        <!-- Totals -->
        <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem">
          <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)">
            <span>Sous-total</span><span><?= formatPrice($summary['subtotal']) ?></span>
          </div>
          <?php if ($summary['discount'] > 0): ?>
          <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--success)">
            <span>Remise</span><span>-<?= formatPrice($summary['discount']) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($summary['tax'] > 0): ?>
          <div style="display:flex;justify-content:space-between;font-size:0.875rem;color:var(--text-secondary)">
            <span>TVA</span><span><?= formatPrice($summary['tax']) ?></span>
          </div>
          <?php endif; ?>
          <hr class="divider">
          <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.2rem">
            <span>Total</span><span class="gradient-text"><?= formatPrice($summary['total']) ?></span>
          </div>
        </div>

        <a href="<?= url('checkout') ?>" class="btn btn-gradient btn-lg w-full">
          <i class="bi bi-lock"></i> Passer la commande
        </a>

        <div style="text-align:center;font-size:0.75rem;color:var(--text-muted);margin-top:1rem">
          <i class="bi bi-shield-check"></i> Paiement 100% sécurisé
        </div>

        <!-- Payment methods -->
        <div style="display:flex;justify-content:center;gap:0.75rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
          <span class="pill-tag">CIB</span>
          <span class="pill-tag">Edahabia</span>
          <span class="pill-tag">Virement</span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll('.qty-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id, action = btn.dataset.action;
    const qtyEl = btn.parentElement.querySelector('span');
    const newQty = parseInt(qtyEl.textContent) + (action === 'plus' ? 1 : -1);
    fetch('<?= url('cart/update') ?>', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({product_id:id, qty:newQty, _csrf:'<?= e(csrf_token()) ?>'})
    }).then(() => location.reload());
  });
});
document.querySelectorAll('.remove-item').forEach(btn => {
  btn.addEventListener('click', () => {
    if (!confirm('Retirer ce produit ?')) return;
    fetch('<?= url('cart/remove') ?>', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({product_id:btn.dataset.id, _csrf:'<?= e(csrf_token()) ?>'})
    }).then(() => location.reload());
  });
});
document.getElementById('applyCoupon')?.addEventListener('click', () => {
  const code = document.getElementById('couponInput').value;
  if (!code) return;
  fetch('<?= url('cart/coupon') ?>', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({code, _csrf:'<?= e(csrf_token()) ?>'})
  }).then(r=>r.json()).then(d => {
    if (d.success) location.reload();
    else alert(d.message);
  });
});
</script>
