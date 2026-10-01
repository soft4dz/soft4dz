<?php $pageTitle = 'Mes Abonnements'; ?>

<h2 style="margin-bottom:1.5rem">Mes abonnements</h2>

<?php if (empty($subs)): ?>
<div style="text-align:center;padding:3rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
  <i class="bi bi-calendar-check" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:1rem"></i>
  <h3>Aucun abonnement actif</h3>
  <p style="color:var(--text-secondary);margin-top:0.5rem">Explorez notre catalogue d'abonnements.</p>
  <a href="<?= url('products?type=subscription') ?>" class="btn btn-gradient btn-sm" style="margin-top:1.5rem">Voir les abonnements</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:1rem">
  <?php foreach ($subs as $sub): ?>
  <div class="card" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
    <div>
      <div style="font-weight:700;font-size:1rem"><?= e($sub['name']) ?></div>
      <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem"><?= ucfirst($sub['billing_cycle']) ?></div>
      <?php if ($sub['features']): ?>
      <div style="display:flex;flex-wrap:wrap;gap:0.375rem;margin-top:0.75rem">
        <?php foreach (json_decode($sub['features'], true) ?? [] as $f): ?>
        <span style="font-size:0.72rem;background:rgba(16,185,129,0.1);color:#34d399;border:1px solid rgba(16,185,129,0.2);border-radius:4px;padding:0.2rem 0.5rem">✓ <?= e($f) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div style="text-align:right">
      <?= statusBadge($sub['status']) ?>
      <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.375rem">Depuis le <?= formatDate($sub['starts_at']) ?></div>
      <?php if ($sub['ends_at']): ?>
      <div style="font-size:0.78rem;color:var(--warning)">Expire le <?= formatDate($sub['ends_at']) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
