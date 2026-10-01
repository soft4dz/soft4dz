<?php $pageTitle = 'Licences & clés'; ?>

<div class="admin-page-header">
  <h1>Licences & clés produits</h1>
  <a href="<?= url('admin/products') ?>" class="btn btn-outline btn-sm">Voir les produits</a>
</div>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Stock de clés par produit</div>
  </div>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th>Livraison</th>
          <th>Stock</th>
          <th>Clés dispo</th>
          <th>Utilisées</th>
          <th>Total</th>
          <th>Statut</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
        <tr><td colspan="8" class="admin-empty">Aucun produit.</td></tr>
        <?php else: ?>
        <?php foreach ($products as $p): ?>
        <tr>
          <td style="font-weight:600"><?= e($p['name']) ?></td>
          <td><span class="badge badge-secondary"><?= e($p['delivery_type']) ?></span></td>
          <td><?= $p['stock'] === null ? '∞' : (int)$p['stock'] ?></td>
          <td style="font-weight:700;color:var(--success)"><?= (int)$p['available_keys'] ?></td>
          <td><?= (int)$p['used_keys'] ?></td>
          <td><?= (int)$p['total_keys'] ?></td>
          <td><?= statusBadge($p['status']) ?></td>
          <td>
            <a href="<?= url('admin/products/' . $p['id'] . '/keys') ?>" class="btn btn-outline btn-sm">
              <i class="bi bi-key"></i> Gérer
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
