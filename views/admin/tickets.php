<?php $pageTitle = 'Tickets de support'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Tickets de support</div>
    <div class="data-table-actions">
      <?php foreach (['open'=>'Ouverts','replied'=>'Répondus','resolved'=>'Résolus','closed'=>'Fermés'] as $k=>$v): ?>
      <a href="<?= url('admin/tickets?status=' . $k) ?>" class="btn btn-sm <?= $status === $k ? 'btn-primary' : 'btn-outline' ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead><tr><th>N° Ticket</th><th>Utilisateur</th><th>Sujet</th><th>Priorité</th><th>Statut</th><th>Mis à jour</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">Aucun ticket.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $t): ?>
        <tr>
          <td style="font-family:monospace;font-weight:700"><?= e($t['ticket_no']) ?></td>
          <td><?= e($t['user_name']) ?></td>
          <td><?= e(truncate($t['subject'], 60)) ?></td>
          <td><?= statusBadge($t['priority']) ?></td>
          <td><?= statusBadge($t['status']) ?></td>
          <td><?= timeAgo($t['updated_at']) ?></td>
          <td><a href="<?= url('admin/tickets/' . $t['id']) ?>" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($last_page > 1): ?>
  <div class="data-table-footer">
    <div class="table-info"><?= $total ?> tickets</div>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
