<?php $pageTitle = 'Blog'; ?>

<div class="admin-page-header">
  <h1>Articles du blog</h1>
  <a href="<?= url('admin/blog/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Nouvel article
  </a>
</div>

<div class="data-table-wrap">
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead><tr><th>Titre</th><th>Auteur</th><th>Statut</th><th>Vues</th><th>Publié le</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($posts)): ?>
        <tr><td colspan="6" class="admin-empty"><i class="bi bi-file-text"></i>Aucun article. Créez le premier.</td></tr>
        <?php else: ?>
        <?php foreach ($posts as $p): ?>
        <tr>
          <td style="font-weight:600"><?= e($p['title']) ?></td>
          <td><?= e($p['author']) ?></td>
          <td><?= statusBadge($p['status']) ?></td>
          <td><?= number_format($p['views_count']) ?></td>
          <td style="font-size:0.8rem;color:var(--text-muted)"><?= $p['published_at'] ? formatDate($p['published_at']) : '—' ?></td>
          <td>
            <div class="flex gap-2">
              <a href="<?= url('admin/blog/' . $p['id'] . '/edit') ?>" class="btn btn-outline btn-sm"><i class="bi bi-pencil"></i></a>
              <form action="<?= url('admin/blog/' . $p['id'] . '/delete') ?>" method="POST" style="display:inline">
                <?= csrf() ?>
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)" onclick="return confirm('Supprimer cet article ?')">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
