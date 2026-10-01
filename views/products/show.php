<?php $pageTitle = e($product['name']); ?>
<?php
$galleryImages = [];
if (!empty($product['image'])) $galleryImages[] = productImageUrl($product['image']);
if (!empty($related)) {
  foreach (array_slice($related, 0, 3) as $rel) {
    if (!empty($rel['image'])) $galleryImages[] = productImageUrl($rel['image']);
  }
}
$galleryImages = array_values(array_unique($galleryImages));
if (empty($galleryImages)) $galleryImages[] = productImageUrl(null);
?>

<div class="page-shell">
  <div class="container page-section">
    <nav class="breadcrumb">
      <a href="<?= url() ?>">Accueil</a>
      <i class="bi bi-chevron-right breadcrumb-separator"></i>
      <a href="<?= url('products') ?>"><?= e(__('product.crumb_catalog')) ?></a>
      <?php if ($product['category_name']): ?>
      <i class="bi bi-chevron-right breadcrumb-separator"></i>
      <a href="<?= url('categories/' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
      <?php endif; ?>
      <i class="bi bi-chevron-right breadcrumb-separator"></i>
      <span class="breadcrumb-current"><?= e($product['name']) ?></span>
    </nav>

    <div class="page-grid-2 page-grid-2--product">
      <div>
        <div class="product-gallery product-gallery--spaced">
          <div class="product-gallery-main">
            <img id="productGalleryMain" src="<?= e($galleryImages[0]) ?>" alt="<?= e($product['name']) ?>">
          </div>
          <div class="product-gallery-thumbs">
            <?php foreach ($galleryImages as $idx => $img): ?>
            <button type="button" class="product-gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" data-gallery-thumb data-gallery-src="<?= e($img) ?>" aria-label="Image produit <?= $idx + 1 ?>">
              <img src="<?= e($img) ?>" alt="">
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card panel product-details-panel">
          <div class="product-tabs">
            <button type="button" class="product-tab-btn active" data-product-tab="desc"><?= e(__('product.tab_desc')) ?></button>
            <button type="button" class="product-tab-btn" data-product-tab="reviews"><?= e(__('product.tab_reviews')) ?> (<?= count($reviews ?? []) ?>)</button>
          </div>
          <div class="product-tab-panel active" data-product-panel="desc">
            <div class="product-description-body">
              <?= $product['description'] ?: '<p>' . e(__('product.no_desc')) . '</p>' ?>
            </div>
          </div>
          <div class="product-tab-panel" data-product-panel="reviews">
            <?php if (!empty($reviews)): ?>
              <h3 class="product-reviews-title"><?= e(__('catalog.reviews_title')) ?></h3>
              <div class="product-reviews-list">
                <?php foreach ($reviews as $r): ?>
                <div class="product-review-item">
                  <div class="product-review-head">
                    <div class="author-avatar product-review-avatar">
                      <?= mb_strtoupper(mb_substr($r['user_name'], 0, 1)) ?>
                    </div>
                    <div>
                      <div class="product-review-author"><?= e($r['user_name']) ?></div>
                      <div class="product-rating product-review-rating">
                        <?php for ($i = 0; $i < $r['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                        <span class="product-review-time"><?= timeAgo($r['created_at']) ?></span>
                      </div>
                    </div>
                  </div>
                  <?php if ($r['title']): ?><p class="product-review-title"><?= e($r['title']) ?></p><?php endif; ?>
                  <p class="product-review-body"><?= e($r['body']) ?></p>
                </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-secondary"><?= e(__('product.no_reviews')) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="sticky-panel">
        <div class="buy-panel">
          <div class="buy-panel-category">
            <?= e($product['category_name'] ?? '') ?>
          </div>
          <h1 class="buy-panel-title"><?= e($product['name']) ?></h1>
          <?php if ($product['rating_avg'] > 0): ?>
          <div class="product-rating buy-panel-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star<?= $i <= round($product['rating_avg']) ? '-fill' : '' ?>"></i>
            <?php endfor; ?>
            <span class="buy-panel-rating-meta"><?= number_format($product['rating_avg'], 1) ?> (<?= $product['rating_count'] ?> avis)</span>
          </div>
          <?php endif; ?>
          <p class="buy-panel-summary">
            <?= e($product['short_desc'] ?? '') ?>
          </p>
          <div class="buy-panel-price-row">
            <?php if ($product['sale_price']): ?>
              <span class="buy-panel-price"><?= formatPrice($product['sale_price']) ?></span>
              <span class="buy-panel-price-old"><?= formatPrice($product['price']) ?></span>
              <span class="buy-panel-badge">-<?= round((1 - $product['sale_price']/$product['price']) * 100) ?>%</span>
            <?php else: ?>
              <span class="buy-panel-price"><?= formatPrice($product['price']) ?></span>
            <?php endif; ?>
          </div>
          <div class="buy-panel-delivery">
            <div class="buy-panel-delivery-title">
              <i class="bi bi-lightning-charge-fill"></i>
              <?= e($product['delivery_type'] === 'instant' ? __('product.delivery_instant') : __('product.delivery_manual')) ?>
            </div>
            <p class="buy-panel-delivery-text">
              <?= e($product['delivery_type'] === 'instant' ? __('product.delivery_instant_hint') : __('product.delivery_manual_hint')) ?>
            </p>
          </div>

          <div class="buy-panel-qty">
            <span class="buy-panel-qty-label"><?= e(__('product.qty')) ?></span>
            <div class="buy-panel-qty-ctrl">
              <button type="button" onclick="changeQty(-1)" class="buy-panel-qty-btn">−</button>
              <input type="number" id="qty" value="1" min="1" max="10" class="form-control buy-panel-qty-input">
              <button type="button" onclick="changeQty(1)" class="buy-panel-qty-btn">+</button>
            </div>
          </div>

          <div class="buy-panel-actions">
            <button class="btn btn-cta w-full btn-lg add-to-cart" data-id="<?= $product['id'] ?>">
              <i class="bi bi-bag-plus"></i> <?= e(__('product.add_cart')) ?>
            </button>
            <a href="<?= url('checkout') ?>" class="btn btn-outline w-full" onclick="addToCartFirst(event, <?= $product['id'] ?>)">
              <i class="bi bi-lightning-charge"></i> <?= e(__('product.buy_now')) ?>
            </a>
          </div>

          <div class="buy-panel-trust">
            <div class="buy-panel-trust-item"><i class="bi bi-shield-check"></i> Garantie remboursement 7 jours</div>
            <div class="buy-panel-trust-item"><i class="bi bi-lock"></i> Paiement 100% sécurisé</div>
            <div class="buy-panel-trust-item"><i class="bi bi-headset"></i> Support disponible 24h/7j</div>
          </div>
        </div>
      </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="related-products">
      <h2 class="related-products-title">Produits similaires</h2>
      <div class="grid-auto">
        <?php foreach ($related as $r): ?>
        <div class="product-card">
          <div class="product-image product-image--related">
            <img src="<?= productImageUrl($r['image']) ?>" alt="<?= e($r['name']) ?>" loading="lazy">
          </div>
          <div class="product-body">
            <h3 class="product-name"><?= e($r['name']) ?></h3>
            <div class="product-footer"><div class="product-price"><span class="price-current"><?= formatPrice($r['sale_price'] ?? $r['price']) ?></span></div></div>
            <a href="<?= url('products/' . $r['slug']) ?>" class="btn btn-outline btn-sm w-full mt-4">Voir le produit</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function changeQty(d) {
  const i = document.getElementById('qty');
  i.value = Math.max(1, Math.min(10, parseInt(i.value) + d));
}
function addToCartFirst(e, id) {
  e.preventDefault();
  fetch('<?= url('cart/add') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({product_id: id, qty: document.getElementById('qty').value, _csrf: '<?= \App\Core\Auth::csrfToken() ?>'})
  }).then(() => window.location.href = '<?= url('checkout') ?>');
}
document.querySelectorAll('[data-product-tab]').forEach((btn) => {
  btn.addEventListener('click', () => {
    const name = btn.getAttribute('data-product-tab');
    document.querySelectorAll('[data-product-tab]').forEach((b) => b.classList.toggle('active', b === btn));
    document.querySelectorAll('[data-product-panel]').forEach((p) => p.classList.toggle('active', p.getAttribute('data-product-panel') === name));
  });
});
</script>
