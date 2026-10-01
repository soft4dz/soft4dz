<?php $pageTitle = 'Mes Commandes'; ?>
<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Mes commandes</div>
    <a href="<?= url('products') ?>" class="btn btn-primary btn-sm"><i class="bi bi-bag-plus"></i> Nouvelle commande</a>
  </div>
  <?php if (empty($data)): ?>
  <div style="text-align:center;padding:3rem;color:var(--text-muted)">
    <i class="bi bi-bag" style="font-size:2.5rem;display:block;margin-bottom:1rem"></i>
    Aucune commande pour le moment.
    <br><a href="<?= url('products') ?>" class="btn btn-gradient btn-sm" style="margin-top:1.5rem">Explorer le catalogue</a>
  </div>
  <?php else: ?>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead><tr><th>N° Commande</th><th>Articles</th><th>Total</th><th>Paiement</th><th>Statut</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($data as $o): ?>
        <tr>
          <td style="font-family:monospace;font-weight:700;color:var(--text-primary)"><?= e($o['order_number']) ?></td>
          <td><?= $o['item_count'] ?> article<?= $o['item_count'] > 1 ? 's' : '' ?></td>
          <td style="font-weight:700"><?= formatPrice($o['total']) ?></td>
          <td><?= statusBadge($o['payment_status']) ?></td>
          <td><?= statusBadge($o['status']) ?></td>
          <td><?= formatDate($o['created_at']) ?></td>
          <td><a href="<?= url('dashboard/orders/' . $o['id']) ?>" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($last_page > 1): ?>
  <div class="data-table-footer">
    <div class="table-info"><?= $total ?> commandes</div>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?page=<?= $i ?>" class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
