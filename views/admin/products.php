<?php $pageTitle = 'Produits'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Gestion des produits</div>
    <div class="data-table-actions admin-filters">
      <form action="<?= url('admin/products') ?>" method="GET">
        <input type="text" name="q" class="table-search" placeholder="Rechercher..." value="<?= e($search ?? '') ?>">
        <select name="category_id" class="form-control">
          <option value="">Toutes catégories</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((int)($catId ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
            <?= $c['depth'] ? '↳ ' : '' ?><?= e($c['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="form-control">
          <option value="">Tous statuts</option>
          <?php foreach (['active'=>'Actif','draft'=>'Brouillon','inactive'=>'Inactif','out_of_stock'=>'Rupture'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= ($status ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <select name="type" class="form-control">
          <option value="">Tous types</option>
          <?php foreach (productTypes() as $k => $v): ?>
          <option value="<?= e($k) ?>" <?= ($type ?? '') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline btn-sm"><i class="bi bi-funnel"></i></button>
      </form>
      <a href="<?= url('admin/products/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouveau produit
      </a>
    </div>
  </div>

  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th>Catégorie</th>
          <th>Type</th>
          <th>Prix</th>
          <th>Stock / Clés</th>
          <th>Statut</th>
          <th>Ventes</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="8" class="admin-empty">Aucun produit.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $p): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:0.875rem">
              <img src="<?= productImageUrl($p['image']) ?>" style="width:40px;height:40px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0" alt="">
              <div>
                <div style="font-weight:700;font-size:0.875rem"><?= e($p['name']) ?></div>
                <div style="font-size:0.72rem;color:var(--text-muted)"><?= e(truncate($p['short_desc'] ?? '', 50)) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-primary"><?= e($p['cat'] ?? 'N/A') ?></span></td>
          <td><span class="badge badge-info"><?= e(productTypeLabel($p['type'] ?? '')) ?></span></td>
          <td>
            <?php if ($p['sale_price']): ?>
              <?php
                $pct = $p['discount_percent'] ?? null;
                if (($pct === null || $pct === '') && (float)$p['price'] > 0) {
                    $pct = round((1 - ((float)$p['sale_price'] / (float)$p['price'])) * 100);
                }
              ?>
              <div style="font-weight:700;color:var(--danger)"><?= formatPrice($p['sale_price']) ?></div>
              <div style="font-size:0.72rem;color:var(--text-muted);text-decoration:line-through"><?= formatPrice($p['price']) ?></div>
              <?php if ($pct): ?><span class="badge badge-danger" style="margin-top:0.2rem">−<?= e(rtrim(rtrim(number_format((float)$pct, 2, '.', ''), '0'), '.')) ?>%</span><?php endif; ?>
            <?php else: ?>
              <div style="font-weight:700"><?= formatPrice($p['price']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-size:0.8rem"><?= $p['stock'] === null ? '∞' : (int)$p['stock'] ?></div>
            <a href="<?= url('admin/products/' . $p['id'] . '/keys') ?>" style="font-size:0.72rem;color:var(--primary-light)">
              <?= (int)($p['keys_available'] ?? 0) ?> clés dispo
            </a>
          </td>
          <td><?= statusBadge($p['status']) ?></td>
          <td><?= number_format($p['sales_count']) ?></td>
          <td>
            <div class="flex gap-2">
              <a href="<?= url('products/' . $p['slug']) ?>" class="btn btn-ghost btn-sm" target="_blank" title="Voir">
                <i class="bi bi-eye"></i>
              </a>
              <a href="<?= url('admin/products/' . $p['id'] . '/keys') ?>" class="btn btn-ghost btn-sm" title="Clés">
                <i class="bi bi-key"></i>
              </a>
              <a href="<?= url('admin/products/' . $p['id'] . '/edit') ?>" class="btn btn-outline btn-sm">
                <i class="bi bi-pencil"></i>
              </a>
              <form action="<?= url('admin/products/' . $p['id'] . '/delete') ?>" method="POST" style="display:inline">
                <?= csrf() ?>
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                        onclick="return confirm('Désactiver ce produit ?')">
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

  <?php if ($last_page > 1): ?>
  <div class="data-table-footer">
    <div class="table-info"><?= $total ?> produits</div>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
         class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
