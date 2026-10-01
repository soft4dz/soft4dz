<?php
$pageTitle = __('catalog.title');

$cat_slug  = $filters['category'] ?? '';
$type_slug = $filters['type'] ?? '';
$on_sale   = !empty($filters['onSale']);
$search_q  = $filters['search'] ?? '';
$sort_val  = $filters['sort'] ?? 'popular';
$min_price = $filters['minPrice'] ?? '';
$max_price = $filters['maxPrice'] ?? '';

$types = [];
foreach (productTypes() as $val => $label) {
  if ($val === 'service') continue;
  $types[$val] = [
    'label' => $label,
    'icon'  => productTypeIcons()[$val] ?? 'bi-box-seam',
  ];
}

$has_filter = $cat_slug || $type_slug || $min_price !== '' || $max_price !== '' || $on_sale || $search_q !== '';
$shown = is_countable($data ?? null) ? count($data) : 0;

$activeParent = null;
foreach ($categoryTree ?? [] as $rubrique) {
  if ($cat_slug === $rubrique['slug']) {
    $activeParent = $rubrique;
  }
  foreach ($rubrique['children'] ?? [] as $child) {
    if ($cat_slug === $child['slug']) {
      $activeParent = $rubrique;
    }
  }
}
?>

<div class="catalogue-page catalogue-shop page-shell">
  <div class="container catalogue-shop-wrap">

    <button type="button" class="catalogue-filters-toggle btn btn-outline" id="catalogueFiltersToggle" aria-expanded="false" aria-controls="catalogueSidebar">
      <i class="bi bi-sliders" aria-hidden="true"></i>
      <?= e(__('catalog.filter_title')) ?>
    </button>

    <aside class="catalogue-sidebar" id="catalogueSidebar">
      <form action="<?= url('products') ?>" method="GET" class="catalogue-filter-card" id="catalogueFilterForm">
        <header class="catalogue-filter-head">
          <div>
            <h2 class="catalogue-filter-title"><?= e(__('catalog.filter_title')) ?></h2>
            <p class="catalogue-filter-sub"><?= e(__('catalog.filter_sub')) ?></p>
          </div>
          <button type="button" class="catalogue-sidebar-close" id="catalogueSidebarClose" aria-label="<?= e(__('catalog.close_filters')) ?>">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
          </button>
        </header>

        <section class="catalogue-filter-block">
          <h3 class="catalogue-filter-label">
            <i class="bi bi-search" aria-hidden="true"></i>
            <?= e(__('catalog.search_products')) ?>
          </h3>
          <div class="catalogue-filter-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="<?= e($search_q) ?>"
                   placeholder="<?= e(__('catalog.search_placeholder_long')) ?>"
                   autocomplete="off">
          </div>
        </section>

        <section class="catalogue-filter-block">
          <h3 class="catalogue-filter-label">
            <i class="bi bi-cash-stack" aria-hidden="true"></i>
            <?= e(__('catalog.price_range')) ?>
          </h3>
          <div class="catalogue-price-row">
            <input type="number" name="min_price" min="0" step="1" placeholder="<?= e(__('catalog.price_min')) ?>" value="<?= e($min_price) ?>">
            <input type="number" name="max_price" min="0" step="1" placeholder="<?= e(__('catalog.price_max')) ?>" value="<?= e($max_price) ?>">
          </div>
        </section>

        <section class="catalogue-filter-block">
          <h3 class="catalogue-filter-label">
            <i class="bi bi-controller" aria-hidden="true"></i>
            <?= e(__('catalog.product_type')) ?>
          </h3>
          <div class="catalogue-type-list">
            <label class="catalogue-type-option <?= $type_slug === '' ? 'is-active' : '' ?>">
              <input type="radio" name="type" value="" <?= $type_slug === '' ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="catalogue-type-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span>
              <span><?= e(__('catalog.all_types')) ?></span>
            </label>
            <?php foreach ($types as $val => $meta): ?>
            <label class="catalogue-type-option <?= $type_slug === $val ? 'is-active' : '' ?>">
              <input type="radio" name="type" value="<?= e($val) ?>" <?= $type_slug === $val ? 'checked' : '' ?> onchange="this.form.submit()">
              <span class="catalogue-type-icon"><i class="bi <?= e($meta['icon']) ?>" aria-hidden="true"></i></span>
              <span><?= e($meta['label']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="catalogue-filter-block catalogue-filter-block--toggle">
          <label class="catalogue-sale-toggle">
            <span class="catalogue-sale-toggle-text">
              <i class="bi bi-tag" aria-hidden="true"></i>
              <?= e(__('catalog.on_sale_only')) ?>
            </span>
            <input type="checkbox" name="on_sale" value="1" <?= $on_sale ? 'checked' : '' ?> onchange="this.form.submit()">
            <span class="catalogue-switch" aria-hidden="true"></span>
          </label>
        </section>

        <section class="catalogue-filter-block">
          <h3 class="catalogue-filter-label">
            <i class="bi bi-folder2" aria-hidden="true"></i>
            <?= e(__('catalog.categories')) ?>
          </h3>
          <ul class="catalogue-cat-tree">
            <li>
              <label class="catalogue-cat-item <?= $cat_slug === '' ? 'is-active' : '' ?>">
                <input type="radio" name="category" value="" <?= $cat_slug === '' ? 'checked' : '' ?> onchange="this.form.submit()">
                <i class="bi bi-folder" aria-hidden="true"></i>
                <span><?= e(__('catalog.all_categories')) ?></span>
              </label>
            </li>
            <?php foreach ($categoryTree ?? [] as $rubrique):
              $parentOpen = $activeParent && $activeParent['slug'] === $rubrique['slug'];
              $parentActive = $cat_slug === $rubrique['slug'];
            ?>
            <li class="<?= $parentOpen || $parentActive ? 'is-open' : '' ?>">
              <label class="catalogue-cat-item <?= $parentActive ? 'is-active' : '' ?>">
                <input type="radio" name="category" value="<?= e($rubrique['slug']) ?>" <?= $parentActive ? 'checked' : '' ?> onchange="this.form.submit()">
                <i class="bi <?= e($rubrique['icon'] ?? 'bi-folder') ?>" aria-hidden="true"></i>
                <span><?= e($rubrique['name']) ?></span>
              </label>
              <?php if (!empty($rubrique['children'])): ?>
              <ul class="catalogue-cat-children">
                <?php foreach ($rubrique['children'] as $child): ?>
                <li>
                  <label class="catalogue-cat-item catalogue-cat-item--child <?= $cat_slug === $child['slug'] ? 'is-active' : '' ?>">
                    <input type="radio" name="category" value="<?= e($child['slug']) ?>" <?= $cat_slug === $child['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                    <i class="bi bi-folder2-open" aria-hidden="true"></i>
                    <span><?= e($child['name']) ?></span>
                  </label>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
        </section>

        <div class="catalogue-filter-actions">
          <input type="hidden" name="sort" value="<?= e($sort_val) ?>">
          <button type="submit" class="btn btn-primary w-full"><?= e(__('catalog.apply_filters')) ?></button>
          <?php if ($has_filter): ?>
          <a href="<?= url('products') ?>" class="btn btn-ghost w-full"><?= e(__('catalog.reset')) ?></a>
          <?php endif; ?>
        </div>
      </form>
    </aside>

    <div class="catalogue-sidebar-backdrop" id="catalogueSidebarBackdrop" hidden></div>

    <main class="catalogue-main">
      <div class="catalogue-main-bar">
        <p class="catalogue-showing">
          <?= e(__('catalog.showing')) ?>
          <strong><?= (int) $shown ?></strong>
          <?= e(__('catalog.of')) ?>
          <strong><?= (int) $total ?></strong>
          <?= e(__('catalog.products_word')) ?>
        </p>
        <form action="<?= url('products') ?>" method="GET" class="catalogue-sort-form">
          <?php if ($search_q !== ''): ?><input type="hidden" name="q" value="<?= e($search_q) ?>"><?php endif; ?>
          <?php if ($cat_slug !== ''): ?><input type="hidden" name="category" value="<?= e($cat_slug) ?>"><?php endif; ?>
          <?php if ($type_slug !== ''): ?><input type="hidden" name="type" value="<?= e($type_slug) ?>"><?php endif; ?>
          <?php if ($min_price !== ''): ?><input type="hidden" name="min_price" value="<?= e($min_price) ?>"><?php endif; ?>
          <?php if ($max_price !== ''): ?><input type="hidden" name="max_price" value="<?= e($max_price) ?>"><?php endif; ?>
          <?php if ($on_sale): ?><input type="hidden" name="on_sale" value="1"><?php endif; ?>
          <label class="visually-hidden" for="catalogueSort"><?= e(__('catalog.sort_label')) ?></label>
          <select name="sort" id="catalogueSort" class="sort-select" onchange="this.form.submit()">
            <option value="popular" <?= $sort_val === 'popular' ? 'selected' : '' ?>><?= e(__('catalog.sort_popular')) ?></option>
            <option value="newest" <?= $sort_val === 'newest' ? 'selected' : '' ?>><?= e(__('catalog.sort_newest')) ?></option>
            <option value="price_asc" <?= $sort_val === 'price_asc' ? 'selected' : '' ?>><?= e(__('catalog.sort_price_asc')) ?></option>
            <option value="price_desc" <?= $sort_val === 'price_desc' ? 'selected' : '' ?>><?= e(__('catalog.sort_price_desc')) ?></option>
            <option value="rating" <?= $sort_val === 'rating' ? 'selected' : '' ?>><?= e(__('catalog.sort_rating')) ?></option>
          </select>
        </form>
      </div>

      <?php if (empty($data)): ?>
      <div class="panel empty-state">
        <div class="empty-state-icon"><i class="bi bi-search"></i></div>
        <h3 class="empty-state-title"><?= e(__('catalog.empty')) ?></h3>
        <p class="empty-state-desc"><?= e(__('catalog.empty_desc')) ?></p>
        <a href="<?= url('products') ?>" class="btn btn-primary empty-state-cta">
          <i class="bi bi-grid"></i> <?= e(__('catalog.view_all')) ?>
        </a>
      </div>
      <?php else: ?>
      <div class="catalogue-grid">
        <?php foreach ($data as $idx => $p):
          $isInstant = ($p['delivery_type'] ?? '') === 'instant';
          $delay = min($idx, 11) * 40;
        ?>
        <a href="<?= url('products/' . $p['slug']) ?>" class="catalog-card" style="animation-delay: <?= $delay ?>ms">
          <div class="catalog-card-media">
            <img src="<?= productImageUrl($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="320" height="200">
            <?php if ($isInstant): ?>
            <span class="catalog-card-badge catalog-card-badge--instant"><?= e(__('catalog.badge_instant')) ?></span>
            <?php endif; ?>
            <?php if (!empty($p['sale_price'])): ?>
            <span class="catalog-card-badge catalog-card-badge--sale">-<?= (int) round((1 - $p['sale_price'] / $p['price']) * 100) ?>%</span>
            <?php endif; ?>
          </div>
          <div class="catalog-card-body">
            <div class="catalog-card-brand"><?= e($p['category_name'] ?? productTypeLabel($p['type'] ?? '')) ?></div>
            <h3 class="catalog-card-title"><?= e($p['name']) ?></h3>
            <div class="catalog-card-price">
              <span class="catalog-card-amount"><?= formatPrice($p['sale_price'] ?? $p['price']) ?></span>
              <?php if (!empty($p['sale_price'])): ?>
              <span class="catalog-card-old"><?= formatPrice($p['price']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if ($last_page > 1): ?>
      <div class="pagination">
        <?php if ($current_page > 1): ?>
        <a href="?<?= e(http_build_query(array_merge($_GET, ['page' => $current_page - 1]))) ?>" class="page-btn">
          <i class="bi bi-chevron-left"></i>
        </a>
        <?php endif; ?>
        <?php for ($i = max(1, $current_page - 2); $i <= min($last_page, $current_page + 2); $i++): ?>
        <a href="?<?= e(http_build_query(array_merge($_GET, ['page' => $i]))) ?>"
           class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($current_page < $last_page): ?>
        <a href="?<?= e(http_build_query(array_merge($_GET, ['page' => $current_page + 1]))) ?>" class="page-btn">
          <i class="bi bi-chevron-right"></i>
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>
</div>

<script>
(function () {
  var toggle = document.getElementById('catalogueFiltersToggle');
  var sidebar = document.getElementById('catalogueSidebar');
  var backdrop = document.getElementById('catalogueSidebarBackdrop');
  var closeBtn = document.getElementById('catalogueSidebarClose');
  if (!toggle || !sidebar) return;

  function openFilters() {
    sidebar.classList.add('is-open');
    if (backdrop) { backdrop.hidden = false; backdrop.classList.add('is-visible'); }
    toggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('catalogue-filters-open');
  }
  function closeFilters() {
    sidebar.classList.remove('is-open');
    if (backdrop) { backdrop.hidden = true; backdrop.classList.remove('is-visible'); }
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('catalogue-filters-open');
  }

  toggle.addEventListener('click', function () {
    sidebar.classList.contains('is-open') ? closeFilters() : openFilters();
  });
  if (closeBtn) closeBtn.addEventListener('click', closeFilters);
  if (backdrop) backdrop.addEventListener('click', closeFilters);
})();
</script>
