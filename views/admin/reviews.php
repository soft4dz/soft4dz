<?php $pageTitle = 'Avis produits'; ?>

<div class="admin-page-header">
  <h1>Modération des avis</h1>
  <div class="admin-filters">
    <?php foreach (['pending'=>'En attente','approved'=>'Approuvés','rejected'=>'Rejetés','all'=>'Tous'] as $k=>$v): ?>
    <a href="<?= url('admin/reviews?status=' . $k) ?>" class="btn btn-sm <?= ($status ?? '') === $k ? 'btn-primary' : 'btn-outline' ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="data-table-wrap">
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>Produit</th>
          <th>Client</th>
          <th>Note</th>
          <th>Avis</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="7" class="admin-empty"><i class="bi bi-star"></i>Aucun avis.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $r): ?>
        <tr>
          <td>
            <a href="<?= url('products/' . $r['product_slug']) ?>" target="_blank" style="font-weight:600"><?= e($r['product_name']) ?></a>
          </td>
          <td>
            <div style="font-weight:600;font-size:0.875rem"><?= e($r['user_name']) ?></div>
            <div style="font-size:0.72rem;color:var(--text-muted)"><?= e($r['user_email']) ?></div>
          </td>
          <td>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star<?= $i <= (int)$r['rating'] ? '-fill' : '' ?>" style="color:var(--warning);font-size:0.8rem"></i>
            <?php endfor; ?>
          </td>
          <td style="max-width:280px">
            <?php if ($r['title']): ?><div style="font-weight:600;font-size:0.85rem"><?= e($r['title']) ?></div><?php endif; ?>
            <div style="font-size:0.8rem;color:var(--text-secondary)"><?= e(truncate($r['body'] ?? '', 120)) ?></div>
          </td>
          <td><?= statusBadge($r['status']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= formatDate($r['created_at']) ?></td>
          <td>
            <div class="flex gap-2">
              <?php if ($r['status'] !== 'approved'): ?>
              <form action="<?= url('admin/reviews/' . $r['id'] . '/approve') ?>" method="POST">
                <?= csrf() ?>
                <button type="submit" class="btn btn-success btn-sm" title="Approuver"><i class="bi bi-check-lg"></i></button>
              </form>
              <?php endif; ?>
              <?php if ($r['status'] !== 'rejected'): ?>
              <form action="<?= url('admin/reviews/' . $r['id'] . '/reject') ?>" method="POST">
                <?= csrf() ?>
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" title="Rejeter"><i class="bi bi-x-lg"></i></button>
              </form>
              <?php endif; ?>
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
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
