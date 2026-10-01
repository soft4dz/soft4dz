<?php $pageTitle = 'Utilisateurs'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Tous les utilisateurs</div>
    <div class="data-table-actions admin-filters">
      <form action="<?= url('admin/users') ?>" method="GET">
        <input type="text" name="q" class="table-search" placeholder="Rechercher nom / email..." value="<?= e($search ?? '') ?>">
        <select name="role" class="form-control">
          <option value="">Tous rôles</option>
          <?php foreach (['customer'=>'Client','vendor'=>'Vendeur'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= ($role ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <select name="status" class="form-control">
          <option value="">Tous statuts</option>
          <?php foreach (['active'=>'Actif','inactive'=>'Inactif','banned'=>'Banni'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= ($status ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline btn-sm"><i class="bi bi-funnel"></i></button>
      </form>
    </div>
  </div>

  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th>Inscrit le</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
        <tr><td colspan="7" class="admin-empty">Aucun utilisateur.</td></tr>
        <?php else: ?>
        <?php foreach ($data as $u): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:0.875rem">
              <div class="author-avatar" style="width:36px;height:36px;font-size:0.875rem;flex-shrink:0">
                <?= mb_strtoupper(mb_substr($u['name'], 0, 1)) ?>
              </div>
              <div style="font-weight:700;font-size:0.875rem"><?= e($u['name']) ?></div>
            </div>
          </td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= e($u['email']) ?></td>
          <td><?= statusBadge($u['role']) ?></td>
          <td><?= statusBadge($u['status']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= $u['last_login'] ? timeAgo($u['last_login']) : 'Jamais' ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= formatDate($u['created_at']) ?></td>
          <td><a href="<?= url('admin/users/' . $u['id']) ?>" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($last_page > 1): ?>
  <div class="data-table-footer">
    <div class="table-info"><?= $total ?> utilisateurs</div>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
         class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
