<?php $pageTitle = e($user['name']); ?>
<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/users') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1><?= e($user['name']) ?></h1>
    <?= statusBadge($user['status']) ?>
    <?= statusBadge($user['role']) ?>
  </div>
</div>

<div class="admin-grid-2 admin-grid-2--user">
  <div class="admin-stack">
    <div class="card">
      <div class="author-avatar" style="width:64px;height:64px;font-size:1.5rem;margin:0 auto 1rem"><?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?></div>
      <div style="text-align:center">
        <div style="font-weight:700;font-size:1.1rem"><?= e($user['name']) ?></div>
        <div style="color:var(--text-muted);font-size:0.875rem"><?= e($user['email']) ?></div>
        <?php if ($user['phone']): ?><div style="color:var(--text-muted);font-size:0.875rem;margin-top:0.25rem"><?= e($user['phone']) ?></div><?php endif; ?>
      </div>
      <hr class="divider">
      <div style="font-size:0.78rem;display:flex;flex-direction:column;gap:0.5rem">
        <div style="color:var(--text-muted)">Inscrit le : <strong style="color:var(--text-primary)"><?= formatDate($user['created_at']) ?></strong></div>
        <div style="color:var(--text-muted)">Dernière connexion : <strong style="color:var(--text-primary)"><?= $user['last_login'] ? timeAgo($user['last_login']) : 'Jamais' ?></strong></div>
      </div>
    </div>

    <div class="form-card">
      <div class="form-section-title">Actions admin</div>
      <form action="<?= url('admin/users/' . $user['id'] . '/update') ?>" method="POST">
        <?= csrf() ?>
        <div class="form-group">
          <label class="form-label">Rôle</label>
          <select name="role" class="form-control">
            <?php foreach (['customer'=>'Client','vendor'=>'Vendeur','admin'=>'Admin'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $user['role'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="status" class="form-control">
            <?php foreach (['active'=>'Actif','inactive'=>'Inactif','banned'=>'Banni'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $user['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary w-full" onclick="return confirm('Confirmer la modification ?')">Enregistrer</button>
      </form>
    </div>
  </div>

  <div class="data-table-wrap">
    <div class="data-table-header"><div class="data-table-title">Dernières commandes</div></div>
    <div class="table-wrap" style="border:none;border-radius:0">
      <table>
        <thead><tr><th>N°</th><th>Total</th><th>Paiement</th><th>Date</th></tr></thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr><td colspan="4" class="admin-empty">Aucune commande.</td></tr>
          <?php else: ?>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><a href="<?= url('admin/orders/' . $o['id']) ?>" style="font-family:monospace;font-weight:700;color:var(--primary-light)"><?= e($o['order_number']) ?></a></td>
            <td style="font-weight:700"><?= formatPrice($o['total']) ?></td>
            <td><?= statusBadge($o['payment_status']) ?></td>
            <td style="font-size:0.8rem;color:var(--text-muted)"><?= formatDate($o['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
