<?php $pageTitle = 'Rubriques & sous-rubriques'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Rubriques & sous-rubriques</div>
    <div class="data-table-actions">
      <a href="<?= url('admin/categories/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Nouvelle rubrique
      </a>
    </div>
  </div>

  <p style="padding:0 1.25rem 1rem;color:var(--text-muted);font-size:0.875rem;margin:0">
    Gérez vos <strong>rubriques principales</strong> (menu catalogue) et leurs <strong>sous-rubriques</strong>.
    Les produits se rattachent à une rubrique ou sous-rubrique.
  </p>

  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Icône</th>
          <th>Nom</th>
          <th>Slug</th>
          <th>Parent</th>
          <th>Ordre</th>
          <th>Produits</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($categories)): ?>
        <tr>
          <td colspan="9" style="text-align:center;padding:2rem;color:var(--text-muted)">
            Aucune rubrique. <a href="<?= url('admin/categories/create') ?>">Créer la première</a>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($categories as $c): ?>
        <?php $isChild = !empty($c['parent_id']); ?>
        <tr>
          <td>
            <?php if ($isChild): ?>
              <span class="badge badge-secondary">Sous-rubrique</span>
            <?php else: ?>
              <span class="badge badge-info">Rubrique</span>
            <?php endif; ?>
          </td>
          <td><i class="bi <?= e($c['icon'] ?? 'bi-box-seam') ?>" style="font-size:1.2rem;color:var(--primary-light)"></i></td>
          <td style="font-weight:700;<?= $isChild ? 'padding-left:1.5rem' : '' ?>">
            <?= $isChild ? '↳ ' : '' ?><?= e($c['name']) ?>
          </td>
          <td style="font-family:monospace;font-size:0.8rem"><?= e($c['slug']) ?></td>
          <td style="font-size:0.85rem;color:var(--text-muted)"><?= e($c['parent_name'] ?? '—') ?></td>
          <td><?= (int) $c['sort_order'] ?></td>
          <td><?= (int) $c['product_count'] ?></td>
          <td><?= $c['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>' ?></td>
          <td>
            <div style="display:flex;gap:0.35rem;flex-wrap:wrap">
              <a href="<?= url('admin/categories/' . $c['id'] . '/edit') ?>" class="btn btn-ghost btn-sm" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <?php if (!$isChild): ?>
              <a href="<?= url('admin/categories/create?parent_id=' . $c['id']) ?>" class="btn btn-ghost btn-sm" title="Ajouter une sous-rubrique">
                <i class="bi bi-diagram-3"></i>
              </a>
              <?php endif; ?>
              <button type="button" class="btn btn-ghost btn-sm text-danger" title="Supprimer / désactiver"
                      onclick="deleteCategory(<?= (int) $c['id'] ?>, '<?= e(addslashes($c['name'])) ?>')">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function deleteCategory(id, name) {
  if (!confirm('Supprimer ou désactiver « ' + name + ' » ?\n\nSi des produits ou sous-rubriques existent, la rubrique sera désactivée.')) return;
  fetch('<?= url('admin/categories') ?>/' + id + '/delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: '_csrf=<?= csrf_token() ?>'
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message || 'Erreur');
    }
  });
}
</script>
