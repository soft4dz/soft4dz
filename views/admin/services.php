<?php $pageTitle = 'Demandes de services'; ?>
<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Demandes de services</div>
    <div class="data-table-actions admin-filters">
      <?php foreach (['new'=>'Nouvelles','reviewing'=>'En revue','quoted'=>'Devis','accepted'=>'Acceptées','in_progress'=>'En cours','completed'=>'Terminées','rejected'=>'Rejetées'] as $k=>$v): ?>
      <a href="<?= url('admin/services?status=' . $k) ?>" class="btn btn-sm <?= $status === $k ? 'btn-primary' : 'btn-outline' ?>"><?= $v ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead><tr><th>Nom</th><th>Email</th><th>Type</th><th>Titre</th><th>Budget</th><th>Statut</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="8" class="admin-empty">Aucune demande.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $s): ?>
        <tr style="cursor:pointer" onclick="window.location='<?= url('admin/services/' . $s['id']) ?>'">
          <td style="font-weight:700"><?= e($s['name']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= e($s['email']) ?></td>
          <td><span class="badge badge-info"><?= e($s['service_type']) ?></span></td>
          <td><?= e(truncate($s['title'], 50)) ?></td>
          <td style="font-size:0.8rem"><?= e($s['budget_range'] ?? '—') ?></td>
          <td><?= statusBadge($s['status']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= formatDate($s['created_at']) ?></td>
          <td><a href="<?= url('admin/services/' . $s['id']) ?>" class="btn btn-outline btn-sm" onclick="event.stopPropagation()"><i class="bi bi-eye"></i></a></td>
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
