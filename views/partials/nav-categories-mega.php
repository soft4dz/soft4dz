<?php
/** Mega-menu catalogue — rubriques & sous-rubriques depuis la BDD */
$navCategories = categoryTree(true);
?>
<div class="mega-menu mega-menu--catalog" role="menu">
  <div class="mega-menu-grid mega-menu-grid--rubriques">
    <?php if (empty($navCategories)): ?>
    <a href="<?= url('products') ?>" class="mega-menu-item" role="menuitem">
      <div class="mega-menu-item-icon"><i class="bi bi-grid-3x3-gap"></i></div>
      <div>
        <div class="mega-menu-item-label">Catalogue</div>
        <div class="mega-menu-item-desc">Parcourir tous les produits</div>
      </div>
    </a>
    <?php else: ?>
    <?php foreach ($navCategories as $rubrique): ?>
    <div class="mega-menu-col">
      <a href="<?= url('categories/' . $rubrique['slug']) ?>" class="mega-menu-col-head" role="menuitem">
        <span class="mega-menu-item-icon"><i class="bi <?= e($rubrique['icon'] ?? 'bi-box-seam') ?>"></i></span>
        <span class="mega-menu-item-label"><?= e($rubrique['name']) ?></span>
      </a>
      <?php if (!empty($rubrique['children'])): ?>
      <ul class="mega-menu-children">
        <?php foreach (array_slice($rubrique['children'], 0, 5) as $child): ?>
        <li>
          <a href="<?= url('categories/' . $child['slug']) ?>" role="menuitem"><?= e($child['name']) ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <p class="mega-menu-item-desc mega-menu-col-desc"><?= e(truncate($rubrique['description'] ?? 'Voir les produits', 48)) ?></p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="mega-menu-footer">
    <span class="mega-menu-footer-text"><?= e(__('product.delivery_instant')) ?> · <?= e(__('home.feat_secure_title')) ?></span>
    <a href="<?= url('products') ?>" class="btn btn-primary btn-sm">
      <?= e(__('catalog.all')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</div>
