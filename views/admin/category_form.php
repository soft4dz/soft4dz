<?php
$isEdit = isset($category);
$pageTitle = $isEdit ? 'Modifier : ' . e($category['name']) : 'Nouvelle rubrique';
$action = $isEdit ? url('admin/categories/' . $category['id'] . '/edit') : url('admin/categories/create');
$prefillParent = (int) ($_GET['parent_id'] ?? ($category['parent_id'] ?? 0));
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
  <a href="<?= url('admin/categories') ?>" class="btn btn-ghost btn-sm">← Retour</a>
  <h1 style="font-size:1.25rem"><?= $pageTitle ?></h1>
</div>

<form action="<?= $action ?>" method="POST">
  <?= csrf() ?>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">
    <div class="form-card">
      <div class="form-section-title">Informations</div>

      <div class="form-group">
        <label class="form-label">Nom *</label>
        <input type="text" name="name" class="form-control" value="<?= e($category['name'] ?? '') ?>" required placeholder="Ex: Logiciels, Windows & Office…">
      </div>

      <div class="form-group">
        <label class="form-label">Slug URL</label>
        <input type="text" name="slug" class="form-control" value="<?= e($category['slug'] ?? '') ?>" placeholder="Auto-généré si vide (ex: logiciels)">
        <div class="form-hint" style="font-size:0.78rem;color:var(--text-muted);margin-top:0.35rem">
          Utilisé dans l’URL : /categories/<strong>logiciels</strong>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description courte</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Texte affiché dans le menu catalogue…"><?= e($category['description'] ?? '') ?></textarea>
      </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="form-card">
        <div class="form-section-title">Organisation</div>

        <div class="form-group">
          <label class="form-label">Type</label>
          <select name="parent_id" class="form-control" id="parentSelect">
            <option value="">— Rubrique principale —</option>
            <?php foreach ($parents as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $prefillParent == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-hint" style="font-size:0.78rem;color:var(--text-muted);margin-top:0.35rem">
            Choisissez une rubrique parente pour créer une <strong>sous-rubrique</strong>.
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Ordre d’affichage</label>
          <input type="number" name="sort_order" class="form-control" value="<?= e($category['sort_order'] ?? '0') ?>" min="0" step="1">
        </div>

        <div class="form-group">
          <label class="form-label">Icône Bootstrap</label>
          <input type="text" name="icon" class="form-control" value="<?= e($category['icon'] ?? 'bi-box-seam') ?>" placeholder="bi-box-seam">
          <div class="form-hint" style="font-size:0.78rem;color:var(--text-muted);margin-top:0.35rem">
            Ex: bi-window, bi-gift, bi-cloud-check — voir <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a>
          </div>
        </div>

        <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:0.5rem;border-radius:var(--radius)">
          <input type="checkbox" name="is_active" value="1" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--primary)">
          <div>
            <div style="font-size:0.875rem;font-weight:600">Rubrique active</div>
            <div style="font-size:0.75rem;color:var(--text-muted)">Visible sur le site et dans le menu</div>
          </div>
        </label>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-gradient" style="flex:1">
          <i class="bi bi-<?= $isEdit ? 'check' : 'plus' ?>"></i>
          <?= $isEdit ? 'Enregistrer' : 'Créer' ?>
        </button>
        <a href="<?= url('admin/categories') ?>" class="btn btn-outline">Annuler</a>
      </div>
    </div>
  </div>
</form>
