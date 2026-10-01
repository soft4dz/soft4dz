<?php $pageTitle = 'Gestion des commandes'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Toutes les commandes</div>
    <div class="data-table-actions admin-filters">
      <form action="<?= url('admin/orders') ?>" method="GET">
        <?php if (!empty($status)): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <input type="text" name="q" class="table-search" placeholder="N°, email, client..." value="<?= e($search ?? '') ?>">
        <button type="submit" class="btn btn-outline btn-sm"><i class="bi bi-search"></i></button>
      </form>
      <?php foreach ([''=>'Toutes', 'unpaid'=>'Non payées', 'pending'=>'En attente', 'paid'=>'Payées', 'failed'=>'Échouées', 'refunded'=>'Remboursées'] as $k => $v): ?>
      <a href="<?= url('admin/orders' . ($k ? '?status=' . $k : '') . (!empty($search) ? ($k ? '&' : '?') . 'q=' . urlencode($search) : '')) ?>"
         class="btn btn-sm <?= ($status ?? '') === $k ? 'btn-primary' : 'btn-outline' ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>N° Commande</th>
          <th>Client</th>
          <th>Email</th>
          <th>Total</th>
          <th>Méthode</th>
          <th>Paiement</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="9" class="admin-empty">Aucune commande trouvée.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $o): ?>
        <tr>
          <td><a href="<?= url('admin/orders/' . $o['id']) ?>" style="font-family:monospace;font-weight:700;color:var(--primary-light)"><?= e($o['order_number']) ?></a></td>
          <td style="font-weight:600"><?= e($o['customer']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= e($o['customer_email']) ?></td>
          <td style="font-weight:700"><?= formatPrice($o['total']) ?></td>
          <td><span class="badge badge-secondary"><?= e(str_replace('_', ' ', $o['payment_method'])) ?></span></td>
          <td><?= statusBadge($o['payment_status']) ?></td>
          <td><?= statusBadge($o['status']) ?></td>
          <td><?= formatDate($o['created_at']) ?></td>
          <td>
            <a href="<?= url('admin/orders/' . $o['id']) ?>" class="btn btn-outline btn-sm">
              <i class="bi bi-eye"></i> Voir
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($last_page > 1): ?>
  <div class="data-table-footer">
    <div class="table-info">Affichage de <?= count($data) ?> sur <?= $total ?></div>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
         class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
