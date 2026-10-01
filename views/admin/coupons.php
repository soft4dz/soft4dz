<?php $pageTitle = 'Coupons'; ?>

<div class="admin-page-header">
  <h1>Coupons</h1>
  <a href="<?= url('admin/coupons/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Nouveau coupon
  </a>
</div>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Codes promo</div>
  </div>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>Code</th>
          <th>Type</th>
          <th>Valeur</th>
          <th>Min.</th>
          <th>Utilisations</th>
          <th>Expire</th>
          <th>Actif</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($coupons)): ?>
        <tr><td colspan="8" class="admin-empty"><i class="bi bi-ticket-perforated"></i>Aucun coupon.</td></tr>
        <?php else: ?>
        <?php foreach ($coupons as $c): ?>
        <tr>
          <td style="font-family:monospace;font-weight:700"><?= e($c['code']) ?></td>
          <td><span class="badge badge-info"><?= $c['type'] === 'percent' ? '%' : 'Fixe' ?></span></td>
          <td style="font-weight:700">
            <?= $c['type'] === 'percent' ? ((float)$c['value'] . '%') : formatPrice($c['value']) ?>
          </td>
          <td><?= $c['min_amount'] !== null ? formatPrice($c['min_amount']) : '—' ?></td>
          <td><?= (int)$c['used_count'] ?><?= $c['max_uses'] !== null ? ' / ' . (int)$c['max_uses'] : '' ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= $c['expires_at'] ? formatDate($c['expires_at']) : '—' ?></td>
          <td><?= (int)$c['is_active'] ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-secondary">Non</span>' ?></td>
          <td>
            <div class="flex gap-2">
              <a href="<?= url('admin/coupons/' . $c['id'] . '/edit') ?>" class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
              <form action="<?= url('admin/coupons/' . $c['id'] . '/delete') ?>" method="POST" style="display:inline">
                <?= csrf() ?>
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="return confirm('Supprimer ce coupon ?')">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
