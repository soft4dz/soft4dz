<?php $pageTitle = 'Demande #' . (int)$service['id']; ?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/services') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1><?= e($service['title']) ?></h1>
    <?= statusBadge($service['status']) ?>
  </div>
</div>

<div class="admin-grid-2">
  <div class="admin-stack">
    <div class="card">
      <h4 style="margin-bottom:1rem">Description</h4>
      <p style="color:var(--text-secondary);line-height:1.7;white-space:pre-wrap;margin:0"><?= e($service['description']) ?></p>
    </div>
    <div class="card">
      <h4 style="margin-bottom:1rem">Détails</h4>
      <div class="admin-stack" style="gap:0.75rem;font-size:0.875rem">
        <div><span style="color:var(--text-muted)">Type :</span> <strong><?= e($service['service_type']) ?></strong></div>
        <div><span style="color:var(--text-muted)">Budget :</span> <strong><?= e($service['budget_range'] ?? '—') ?></strong></div>
        <div><span style="color:var(--text-muted)">Deadline :</span> <strong><?= $service['deadline'] ? formatDate($service['deadline']) : '—' ?></strong></div>
        <div><span style="color:var(--text-muted)">Société :</span> <strong><?= e($service['company'] ?? '—') ?></strong></div>
        <div><span style="color:var(--text-muted)">Créée le :</span> <strong><?= formatDate($service['created_at'], 'd/m/Y H:i') ?></strong></div>
      </div>
    </div>
  </div>

  <div class="admin-stack">
    <div class="card">
      <h4 style="margin-bottom:1rem">Contact</h4>
      <div style="font-weight:700"><?= e($service['name']) ?></div>
      <div style="font-size:0.875rem;color:var(--text-muted)"><?= e($service['email']) ?></div>
      <?php if ($service['phone']): ?><div style="font-size:0.875rem;color:var(--text-muted)"><?= e($service['phone']) ?></div><?php endif; ?>
      <?php if ($service['user_id']): ?>
      <a href="<?= url('admin/users/' . $service['user_id']) ?>" class="btn btn-ghost btn-sm" style="margin-top:0.75rem">Voir le compte →</a>
      <?php endif; ?>
    </div>

    <div class="form-card">
      <div class="form-section-title">Gestion</div>
      <form action="<?= url('admin/services/' . $service['id']) ?>" method="POST">
        <?= csrf() ?>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="status" class="form-control">
            <?php foreach (['new'=>'Nouvelle','reviewing'=>'En revue','quoted'=>'Devis envoyé','accepted'=>'Acceptée','in_progress'=>'En cours','completed'=>'Terminée','rejected'=>'Rejetée'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $service['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Montant devis (DZD)</label>
          <input type="number" name="quoted_amount" class="form-control" step="0.01" min="0" value="<?= e($service['quoted_amount'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Notes admin</label>
          <textarea name="admin_notes" class="form-control" rows="4"><?= e($service['admin_notes'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-full">Enregistrer</button>
      </form>
    </div>
  </div>
</div>
