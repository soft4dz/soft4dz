<?php $pageTitle = 'Support'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
  <h2>Mes tickets de support</h2>
  <button class="btn btn-primary" onclick="document.getElementById('newTicketModal').style.display='flex'">
    <i class="bi bi-plus"></i> Nouveau ticket
  </button>
</div>

<?php if (empty($tickets)): ?>
<div style="text-align:center;padding:3rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
  <i class="bi bi-headset" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:1rem"></i>
  <h3>Aucun ticket</h3>
  <p style="color:var(--text-secondary);margin-top:0.5rem">Besoin d'aide ? Créez votre premier ticket de support.</p>
</div>
<?php else: ?>
<div class="data-table-wrap">
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead><tr><th>N° Ticket</th><th>Sujet</th><th>Priorité</th><th>Statut</th><th>Réponses</th><th>Mis à jour</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr>
          <td style="font-family:monospace;font-weight:700"><?= e($t['ticket_no']) ?></td>
          <td><?= e(truncate($t['subject'], 60)) ?></td>
          <td><?= statusBadge($t['priority']) ?></td>
          <td><?= statusBadge($t['status']) ?></td>
          <td><?= $t['reply_count'] ?></td>
          <td><?= timeAgo($t['updated_at']) ?></td>
          <td><a href="<?= url('dashboard/tickets/' . $t['id']) ?>" class="btn btn-outline btn-sm"><i class="bi bi-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- New Ticket Modal -->
<div id="newTicketModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)this.style.display='none'">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Nouveau ticket de support</h3>
      <button class="modal-close" onclick="document.getElementById('newTicketModal').style.display='none'">✕</button>
    </div>
    <form action="<?= url('dashboard/tickets') ?>" method="POST">
      <?= csrf() ?>
      <div class="form-group">
        <label class="form-label">Sujet *</label>
        <input type="text" name="subject" class="form-control" placeholder="Résumez votre problème" required>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">Priorité</label>
          <select name="priority" class="form-control">
            <option value="low">Basse</option>
            <option value="normal" selected>Normale</option>
            <option value="high">Haute</option>
            <option value="urgent">Urgente</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Commande liée (optionnel)</label>
          <input type="text" name="order_id" class="form-control" placeholder="ID de commande">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Description *</label>
        <textarea name="body" class="form-control" rows="4" required placeholder="Décrivez votre problème en détail..."></textarea>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary" style="flex:1">Créer le ticket</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('newTicketModal').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>
