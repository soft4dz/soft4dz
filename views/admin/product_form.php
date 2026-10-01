<?php
$isEdit = isset($product);
$pageTitle = $isEdit ? 'Modifier : ' . e($product['name']) : 'Nouveau produit';
$action = $isEdit ? url('admin/products/' . $product['id'] . '/edit') : url('admin/products/create');
?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/products') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1><?= $pageTitle ?></h1>
  </div>
  <?php if ($isEdit): ?>
  <a href="<?= url('admin/products/' . $product['id'] . '/keys') ?>" class="btn btn-outline btn-sm">
    <i class="bi bi-key"></i> Gérer les clés (<?= (int)($keysAvailable ?? 0) ?>)
  </a>
  <?php endif; ?>
</div>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data">
  <?= csrf() ?>

  <div class="admin-grid-2">
    <div class="admin-stack">
      <div class="form-card">
        <div class="form-section-title">Informations générales</div>
        <div class="form-group">
          <label class="form-label">Nom du produit *</label>
          <input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? '') ?>" required placeholder="Ex: Netflix Premium 1 Mois">
        </div>
        <div class="form-group">
          <label class="form-label">Description courte</label>
          <textarea name="short_desc" class="form-control" rows="2" placeholder="Résumé en 1-2 phrases..."><?= e($product['short_desc'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Description complète</label>
          <textarea name="description" class="form-control" rows="8" placeholder="Description détaillée HTML..."><?= e($product['description'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">SEO</div>
        <div class="form-group">
          <label class="form-label">Meta title</label>
          <input type="text" name="meta_title" class="form-control" value="<?= e($product['meta_title'] ?? '') ?>" maxlength="255">
        </div>
        <div class="form-group">
          <label class="form-label">Meta description</label>
          <textarea name="meta_desc" class="form-control" rows="2" maxlength="500"><?= e($product['meta_desc'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="admin-stack">
      <div class="form-card admin-product-image-card">
        <div class="form-section-title">Image du produit</div>
        <?php if ($isEdit && !empty($product['image'])): ?>
        <div class="admin-product-image-preview" id="productImagePreview">
          <img src="<?= productImageUrl($product['image']) ?>" alt="Image actuelle de <?= e($product['name']) ?>">
        </div>
        <label class="admin-product-image-remove">
          <input type="checkbox" name="remove_image" value="1" id="removeImageCheck">
          Supprimer l'image actuelle
        </label>
        <?php else: ?>
        <div class="admin-product-image-preview admin-product-image-preview--empty" id="productImagePreview">
          <span><i class="bi bi-image"></i> Aucune image</span>
        </div>
        <?php endif; ?>
        <div class="form-group admin-product-image-upload">
          <label class="form-label" for="productImageInput"><?= $isEdit ? 'Remplacer l\'image' : 'Ajouter une image' ?></label>
          <input type="file" name="image" id="productImageInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/gif">
          <p class="admin-product-image-hint">JPG, PNG, WEBP ou GIF — max 3 Mo.</p>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Classification</div>
        <div class="form-group">
          <label class="form-label">Catégorie</label>
          <select name="category_id" class="form-control">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($product['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
              <?= $c['depth'] ? '↳ ' : '' ?><?= e($c['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Type de sortie *</label>
          <select name="type" class="form-control" required>
            <?php foreach (productTypes() as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= ($product['type'] ?? '') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="admin-product-image-hint" style="margin-top:0.4rem">Tu choisis le format livré : top-up, compte, carte cadeau, abonnement, etc.</p>
        </div>
        <div class="form-group">
          <label class="form-label">Livraison</label>
          <select name="delivery_type" class="form-control">
            <?php foreach (['instant'=>'Instantanée','manual'=>'Manuelle','download'=>'Téléchargement','access_link'=>'Lien d\'accès'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($product['delivery_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Tarification & stock</div>
        <div class="form-group">
          <label class="form-label">Prix (DZD) *</label>
          <input type="number" name="price" id="productPrice" class="form-control" value="<?= e($product['price'] ?? '') ?>" step="0.01" min="0" required placeholder="1500.00">
        </div>
        <?php
          $discountPercent = $product['discount_percent'] ?? null;
          if (($discountPercent === null || $discountPercent === '') && !empty($product['price']) && !empty($product['sale_price']) && (float)$product['price'] > 0) {
              $discountPercent = round((1 - ((float)$product['sale_price'] / (float)$product['price'])) * 100, 2);
          }
        ?>
        <div class="form-group">
          <label class="form-label">Promotion (%)</label>
          <div class="admin-promo-row">
            <input type="number" name="discount_percent" id="productDiscountPercent" class="form-control" value="<?= e($discountPercent ?? '') ?>" step="0.01" min="0" max="100" placeholder="Ex: 20">
            <span class="admin-promo-suffix">%</span>
          </div>
          <p class="admin-product-image-hint">Laissez vide pour aucune promo. Le prix soldé est calculé automatiquement.</p>
        </div>
        <div class="form-group">
          <label class="form-label">Prix soldé (calculé)</label>
          <input type="text" id="productSalePriceDisplay" class="form-control" value="<?= !empty($product['sale_price']) ? e(number_format((float)$product['sale_price'], 2, '.', '')) . ' DZD' : '—' ?>" readonly>
          <input type="hidden" name="sale_price" id="productSalePrice" value="<?= e($product['sale_price'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Stock</label>
          <input type="number" name="stock" class="form-control" value="<?= e($product['stock'] ?? '') ?>" min="0" placeholder="Vide = illimité">
          <p class="admin-product-image-hint">Pour livraison instantanée, le stock se synchronise avec les clés disponibles.</p>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Statut & Options</div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="status" class="form-control">
            <?php foreach (['active'=>'Actif','draft'=>'Brouillon','inactive'=>'Inactif','out_of_stock'=>'Rupture de stock'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($product['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:0.5rem;border-radius:var(--radius)">
          <input type="checkbox" name="featured" value="1" <?= ($product['featured'] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--primary)">
          <div>
            <div style="font-size:0.875rem;font-weight:600">Produit en vedette</div>
            <div style="font-size:0.75rem;color:var(--text-muted)">Apparaît sur la page d'accueil</div>
          </div>
        </label>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="btn btn-gradient" style="flex:1">
          <i class="bi bi-<?= $isEdit ? 'check' : 'plus' ?>"></i>
          <?= $isEdit ? 'Mettre à jour' : 'Créer le produit' ?>
        </button>
        <a href="<?= url('admin/products') ?>" class="btn btn-outline">Annuler</a>
      </div>
    </div>
  </div>
</form>

<script>
(function () {
  const input = document.getElementById('productImageInput');
  const preview = document.getElementById('productImagePreview');
  const removeCheck = document.getElementById('removeImageCheck');

  if (input && preview) {
    input.addEventListener('change', function () {
      const file = input.files && input.files[0];
      if (!file) return;
      if (removeCheck) removeCheck.checked = false;
      const reader = new FileReader();
      reader.onload = function (e) {
        preview.classList.remove('admin-product-image-preview--empty');
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Apercu de la nouvelle image">';
      };
      reader.readAsDataURL(file);
    });

    removeCheck?.addEventListener('change', function () {
      if (!removeCheck.checked) return;
      input.value = '';
      preview.classList.add('admin-product-image-preview--empty');
      preview.innerHTML = '<span><i class="bi bi-image"></i> Image sera supprimee</span>';
    });
  }

  const priceEl = document.getElementById('productPrice');
  const percentEl = document.getElementById('productDiscountPercent');
  const saleHidden = document.getElementById('productSalePrice');
  const saleDisplay = document.getElementById('productSalePriceDisplay');

  function recalcSalePrice() {
    if (!priceEl || !percentEl || !saleHidden || !saleDisplay) return;
    const price = parseFloat(priceEl.value);
    const percent = parseFloat(percentEl.value);
    if (!isFinite(price) || price <= 0 || !isFinite(percent) || percent <= 0) {
      saleHidden.value = '';
      saleDisplay.value = '—';
      return;
    }
    const pct = Math.min(100, Math.max(0, percent));
    const sale = Math.round(price * (1 - pct / 100) * 100) / 100;
    saleHidden.value = sale.toFixed(2);
    saleDisplay.value = sale.toLocaleString('fr-DZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DZD (−' + pct + '%)';
  }

  priceEl?.addEventListener('input', recalcSalePrice);
  percentEl?.addEventListener('input', recalcSalePrice);
  recalcSalePrice();
})();
</script>
