<?php $pageTitle = e($ticket['ticket_no']); ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
  <a href="<?= url('dashboard/tickets') ?>" class="btn btn-ghost btn-sm">← Retour</a>
  <h2 style="font-size:1.1rem"><?= e($ticket['subject']) ?></h2>
  <?= statusBadge($ticket['status']) ?>
  <?= statusBadge($ticket['priority']) ?>
</div>

<!-- Thread -->
<div style="display:flex;flex-direction:column;gap:1rem;max-height:500px;overflow-y:auto;padding:0.5rem;margin-bottom:1.5rem">
  <!-- Original message -->
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.25rem">
    <div style="display:flex;justify-content:space-between;margin-bottom:0.75rem">
      <div style="font-weight:700;font-size:0.875rem">Votre message</div>
      <div style="font-size:0.75rem;color:var(--text-muted)"><?= formatDate($ticket['created_at'], 'd/m/Y H:i') ?></div>
    </div>
    <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.7"><?= e($ticket['body']) ?></p>
  </div>

  <?php foreach ($replies as $reply): ?>
  <div style="background:<?= $reply['is_staff'] ? 'rgba(124,58,237,0.08)' : 'var(--bg-card)' ?>;border:1px solid <?= $reply['is_staff'] ? 'rgba(124,58,237,0.2)' : 'var(--border)' ?>;border-radius:var(--radius-lg);padding:1.25rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
      <div style="display:flex;align-items:center;gap:0.625rem">
        <div class="author-avatar" style="width:32px;height:32px;font-size:0.8rem;background:<?= $reply['is_staff'] ? 'var(--grad-primary)' : 'var(--bg-surface)' ?>">
          <?= mb_strtoupper(mb_substr($reply['name'], 0, 1)) ?>
        </div>
        <div>
          <div style="font-weight:700;font-size:0.875rem"><?= e($reply['name']) ?></div>
          <?php if ($reply['is_staff']): ?><span class="badge badge-primary" style="font-size:0.62rem">Support</span><?php endif; ?>
        </div>
      </div>
      <div style="font-size:0.75rem;color:var(--text-muted)"><?= formatDate($reply['created_at'], 'd/m/Y H:i') ?></div>
    </div>
    <p style="color:var(--text-secondary);font-size:0.875rem;line-height:1.7"><?= e($reply['body']) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Reply Form -->
<?php if ($ticket['status'] !== 'closed'): ?>
<div class="card">
  <h4 style="margin-bottom:1rem">Ajouter une réponse</h4>
  <form action="<?= url('dashboard/tickets/' . $ticket['id'] . '/reply') ?>" method="POST">
    <?= csrf() ?>
    <div class="form-group">
      <textarea name="body" class="form-control" rows="4" placeholder="Votre réponse..." required></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer</button>
  </form>
</div>
<?php else: ?>
<div class="alert alert-info"><i class="bi bi-info-circle"></i> Ce ticket est fermé.</div>
<?php endif; ?>
