<?php $pageTitle = 'Clés — ' . e($product['name']); ?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/licenses') ?>" class="btn btn-ghost btn-sm">← Licences</a>
    <h1><?= e($product['name']) ?></h1>
  </div>
  <span class="badge badge-success"><?= (int)$available ?> disponibles</span>
</div>

<div class="admin-grid-2">
  <div class="data-table-wrap">
    <div class="data-table-header">
      <div class="data-table-title">Clés enregistrées</div>
    </div>
    <div class="table-wrap" style="border:none;border-radius:0">
      <table>
        <thead>
          <tr><th>Clé</th><th>Statut</th><th>Commande</th><th>Utilisée le</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($keys)): ?>
          <tr><td colspan="5" class="admin-empty">Aucune clé. Importez-en à droite.</td></tr>
          <?php else: ?>
          <?php foreach ($keys as $k): ?>
          <tr>
            <td><div class="admin-key-mono"><?= e($k['key_value']) ?></div></td>
            <td><?= (int)$k['is_used'] ? '<span class="badge badge-secondary">Utilisée</span>' : '<span class="badge badge-success">Disponible</span>' ?></td>
            <td>
              <?php if ($k['order_id']): ?>
              <a href="<?= url('admin/orders/' . $k['order_id']) ?>">#<?= (int)$k['order_id'] ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="font-size:0.8rem;color:var(--text-muted)"><?= $k['used_at'] ? formatDate($k['used_at'], 'd/m/Y H:i') : '—' ?></td>
            <td>
              <?php if (!(int)$k['is_used']): ?>
              <form action="<?= url('admin/products/' . $product['id'] . '/keys/' . $k['id'] . '/delete') ?>" method="POST" style="display:inline">
                <?= csrf() ?>
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="return confirm('Supprimer cette clé ?')">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="form-card">
    <div class="form-section-title">Import bulk</div>
    <p style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:1rem">Une clé par ligne. Les lignes vides sont ignorées.</p>
    <form action="<?= url('admin/products/' . $product['id'] . '/keys') ?>" method="POST">
      <?= csrf() ?>
      <div class="form-group">
        <textarea name="keys" class="form-control" rows="12" placeholder="XXXX-XXXX-XXXX&#10;YYYY-YYYY-YYYY" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-full">
        <i class="bi bi-upload"></i> Importer les clés
      </button>
    </form>
  </div>
</div>
