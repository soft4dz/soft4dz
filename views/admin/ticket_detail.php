<?php $pageTitle = 'Ticket ' . e($ticket['ticket_no']); ?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/tickets') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1 style="font-size:1.1rem"><?= e($ticket['subject']) ?></h1>
    <?= statusBadge($ticket['status']) ?>
    <?= statusBadge($ticket['priority']) ?>
  </div>
</div>

<div class="admin-grid-2 admin-grid-2--ticket">
  <div>
    <div class="admin-stack" style="margin-bottom:1.5rem">
      <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;gap:1rem;flex-wrap:wrap">
          <div style="font-weight:700"><?= e($ticket['user_name']) ?></div>
          <div style="font-size:0.75rem;color:var(--text-muted)"><?= formatDate($ticket['created_at'], 'd/m/Y H:i') ?></div>
        </div>
        <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.7;margin:0"><?= e($ticket['body']) ?></p>
      </div>
      <?php foreach ($replies as $r): ?>
      <div style="background:<?= $r['is_staff'] ? 'color-mix(in srgb, var(--primary) 8%, transparent)' : 'var(--bg-card)' ?>;border:1px solid <?= $r['is_staff'] ? 'color-mix(in srgb, var(--primary) 22%, transparent)' : 'var(--border)' ?>;border-radius:var(--radius-lg);padding:1.25rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem;gap:1rem;flex-wrap:wrap">
          <div style="font-weight:700;font-size:0.875rem">
            <?= e($r['name']) ?> <?= $r['is_staff'] ? '<span class="badge badge-primary" style="font-size:0.62rem">Staff</span>' : '' ?>
          </div>
          <div style="font-size:0.75rem;color:var(--text-muted)"><?= formatDate($r['created_at'], 'd/m/Y H:i') ?></div>
        </div>
        <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.7;margin:0"><?= e($r['body']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <h4 style="margin-bottom:1rem">Répondre</h4>
      <form action="<?= url('admin/tickets/' . $ticket['id'] . '/reply') ?>" method="POST">
        <?= csrf() ?>
        <div class="form-group">
          <textarea name="body" class="form-control" rows="4" placeholder="Votre réponse..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer la réponse</button>
      </form>
    </div>
  </div>

  <div class="admin-stack">
    <div class="card">
      <h4 style="margin-bottom:1rem">Informations</h4>
      <div style="font-size:0.875rem;display:flex;flex-direction:column;gap:0.75rem">
        <div>
          <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.25rem">Utilisateur</div>
          <a href="<?= url('admin/users/' . $ticket['user_id']) ?>" style="font-weight:700"><?= e($ticket['user_name']) ?></a>
          <div style="color:var(--text-muted);font-size:0.78rem"><?= e($ticket['user_email']) ?></div>
        </div>
        <div><div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.25rem">N° Ticket</div><code><?= e($ticket['ticket_no']) ?></code></div>
        <div><div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.25rem">Créé le</div><?= formatDate($ticket['created_at'], 'd/m/Y H:i') ?></div>
      </div>
    </div>

    <div class="card">
      <h4 style="margin-bottom:1rem">Statut & priorité</h4>
      <form action="<?= url('admin/tickets/' . $ticket['id'] . '/status') ?>" method="POST">
        <?= csrf() ?>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="status" class="form-control">
            <?php foreach (['open'=>'Ouvert','replied'=>'Répondu','resolved'=>'Résolu','closed'=>'Fermé'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $ticket['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Priorité</label>
          <select name="priority" class="form-control">
            <?php foreach (['low'=>'Basse','normal'=>'Normale','high'=>'Haute','urgent'=>'Urgente'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $ticket['priority'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-outline w-full">Mettre à jour</button>
      </form>
    </div>
  </div>
</div>
