<?php
$isEdit = isset($post);
$pageTitle = $isEdit ? 'Modifier article' : 'Nouvel article';
$action = $isEdit ? url('admin/blog/' . $post['id'] . '/edit') : url('admin/blog/create');
$tags = '';
if (!empty($post['tags'])) {
    $decoded = json_decode($post['tags'], true);
    $tags = is_array($decoded) ? implode(', ', $decoded) : '';
}
?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/blog') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1><?= e($pageTitle) ?></h1>
  </div>
</div>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
  <?= csrf() ?>
  <div class="admin-grid-2">
    <div class="admin-stack">
      <div class="form-card">
        <div class="form-section-title">Contenu</div>
        <div class="form-group">
          <label class="form-label">Titre *</label>
          <input type="text" name="title" class="form-control" value="<?= e($post['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control" value="<?= e($post['slug'] ?? '') ?>" placeholder="Auto si vide">
        </div>
        <div class="form-group">
          <label class="form-label">Extrait</label>
          <textarea name="excerpt" class="form-control" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Corps *</label>
          <textarea name="body" class="form-control" rows="14" required><?= e($post['body'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="form-card">
        <div class="form-section-title">SEO</div>
        <div class="form-group">
          <label class="form-label">Meta title</label>
          <input type="text" name="meta_title" class="form-control" value="<?= e($post['meta_title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Meta description</label>
          <textarea name="meta_desc" class="form-control" rows="2"><?= e($post['meta_desc'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="admin-stack">
      <div class="form-card">
        <div class="form-section-title">Publication</div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="status" class="form-control">
            <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
          </select>
        </div>
        <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;margin-bottom:1rem">
          <input type="checkbox" name="featured" value="1" <?= ($post['featured'] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--primary)">
          <span style="font-weight:600;font-size:0.875rem">Article en vedette</span>
        </label>
        <div class="form-group">
          <label class="form-label">Tags (séparés par des virgules)</label>
          <input type="text" name="tags" class="form-control" value="<?= e($tags) ?>" placeholder="softwares, guides">
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Image</div>
        <?php if ($isEdit && !empty($post['image'])): ?>
        <div class="admin-product-image-preview" style="max-width:100%;height:140px">
          <img src="<?= UPLOAD_URL . e($post['image']) ?>" alt="">
        </div>
        <label class="admin-product-image-remove">
          <input type="checkbox" name="remove_image" value="1"> Supprimer l'image
        </label>
        <?php endif; ?>
        <div class="form-group">
          <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
        </div>
      </div>

      <button type="submit" class="btn btn-gradient w-full">
        <?= $isEdit ? 'Mettre à jour' : 'Créer l\'article' ?>
      </button>
    </div>
  </div>
</form>
