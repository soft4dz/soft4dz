<?php $pageTitle = __('nav.home_title'); ?>

<?php
/* Live feed — données simulées (noms algériens + produits populaires) */
$feedItems = [
  ['init'=>'YB', 'name'=>'Yacine B.',  'product'=>'Windows 11 Pro',         'time'=>'2 min'],
  ['init'=>'AK', 'name'=>'Amira K.',   'product'=>'Adobe Creative Cloud',   'time'=>'5 min'],
  ['init'=>'HM', 'name'=>'Hicham M.', 'product'=>'Microsoft Office 365',   'time'=>'8 min'],
  ['init'=>'SR', 'name'=>'Sonia R.',   'product'=>'Spotify Premium',        'time'=>'12 min'],
  ['init'=>'KL', 'name'=>'Karim L.',   'product'=>'Netflix 4K',             'time'=>'15 min'],
  ['init'=>'RD', 'name'=>'Rania D.',   'product'=>'Canva Pro',              'time'=>'18 min'],
  ['init'=>'MT', 'name'=>'Mourad T.',  'product'=>'VPN Premium 1 an',       'time'=>'22 min'],
  ['init'=>'FN', 'name'=>'Faiza N.',   'product'=>'Antivirus Total 2025',   'time'=>'27 min'],
  ['init'=>'ZB', 'name'=>'Zakaria B.','product'=>'Grammarly Premium',      'time'=>'31 min'],
  ['init'=>'NM', 'name'=>'Nour M.',    'product'=>'ChatGPT Plus',           'time'=>'35 min'],
];

?>

<div class="home-flow">

  <!-- ── 0. HERO ─────────────────────────────────────────── -->
  <section class="hero hero--marketplace" aria-labelledby="hero-heading">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-grid" aria-hidden="true"></div>
    <div class="hero-orb hero-orb-1" aria-hidden="true"></div>
    <div class="hero-orb hero-orb-2" aria-hidden="true"></div>
    <div class="hero-noise" aria-hidden="true"></div>

    <div class="container">
      <div class="hero-content">
        <div class="hero-copy animate-fade-in-up">
          <h1 id="hero-heading" class="hero-title">
            <?= e(__('home.hero_title_1')) ?><br>
            <span class="gradient-text"><?= e(__('home.hero_title_2')) ?></span>
          </h1>

          <p class="hero-desc"><?= e(__('home.hero_desc')) ?></p>

          <div class="hero-actions">
            <a href="<?= url('products') ?>" class="btn btn-gradient btn-xl hero-cta-primary">
              <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
              <?= e(__('home.hero_cta_catalog')) ?>
            </a>
            <a href="<?= url('products?sort=newest') ?>" class="btn btn-outline btn-xl hero-cta-secondary">
              <i class="bi bi-stars" aria-hidden="true"></i>
              <?= e(__('home.hero_cta_new')) ?>
            </a>
          </div>
        </div>

        <div class="animate-fade-in-up animate-delay-2 hero-visual-wrap">
          <div class="hero-preview-card">
            <div class="hero-preview-list">
              <?php foreach (array_slice($featured, 0, 3) as $p): ?>
              <a href="<?= url('products/' . $p['slug']) ?>" class="hero-product-row">
                <div class="hero-product-thumb">
                  <img src="<?= productImageUrl($p['image'] ?? null) ?>" alt="<?= e($p['name']) ?>" loading="lazy" width="48" height="48">
                </div>
                <div class="hero-product-meta">
                  <div class="hero-product-name"><?= e($p['name']) ?></div>
                  <div class="hero-product-cat"><?= e($p['category_name'] ?? '') ?></div>
                </div>
                <div class="hero-product-price-col">
                  <?php if ($p['sale_price']): ?>
                    <div class="hero-price hero-price--sale"><?= formatPrice($p['sale_price']) ?></div>
                    <div class="hero-price hero-price--old"><?= formatPrice($p['price']) ?></div>
                  <?php else: ?>
                    <div class="hero-price"><?= formatPrice($p['price']) ?></div>
                  <?php endif; ?>
                </div>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($promoProducts)): ?>
  <!-- ── 1. PROMOTIONS (collapse 6) ───────────────────────── -->
  <section class="section section--promotions" x-data="{ expanded: false }" aria-labelledby="promotions-heading">
    <div class="container">
      <header class="section-header section-header--left section-header--tight">
        <p class="eyebrow"><?= e(__('home.promo_eyebrow')) ?></p>
        <h2 id="promotions-heading"><?= e(__('home.promo_title')) ?> <span class="gradient-text"><?= e(__('home.promo_title_grad')) ?></span></h2>
        <p>Decouvre les meilleures remises du moment.</p>
      </header>

      <div class="product-grid promo-grid">
        <?php foreach ($promoProducts as $idx => $p): ?>
        <article class="product-card promo-card" x-show="expanded || <?= (int)$idx ?> < 6" x-transition.opacity.duration.200ms>
          <div class="product-image">
            <img src="<?= productImageUrl($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <span class="product-badge sale">-<?= round((1 - $p['sale_price'] / $p['price']) * 100) ?>%</span>
          </div>
          <div class="product-body">
            <div class="product-cat"><?= e($p['category_name'] ?? '') ?></div>
            <h3 class="product-name"><a href="<?= url('products/' . $p['slug']) ?>"><?= e($p['name']) ?></a></h3>
            <p class="product-desc"><?= e(truncate($p['short_desc'] ?? '', 74)) ?></p>
            <div class="product-footer">
              <div class="product-price">
                <span class="price-current"><?= formatPrice($p['sale_price']) ?></span>
                <span class="price-original"><?= formatPrice($p['price']) ?></span>
              </div>
            </div>
            <div class="product-actions">
              <a href="<?= url('products/' . $p['slug']) ?>" class="btn btn-primary btn-sm w-full">
                <i class="bi bi-bag-check" aria-hidden="true"></i>
                <?= e(__('home.buy')) ?>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <?php if (count($promoProducts) > 6): ?>
      <div class="promo-actions">
        <button type="button" class="btn btn-outline btn-lg" @click="expanded = !expanded">
          <span x-show="!expanded"><?= e(__('home.promo_more')) ?></span>
          <span x-show="expanded"><?= e(__('home.promo_less')) ?></span>
          <i class="bi" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'" aria-hidden="true"></i>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── 2. STATS BAND ─────────────────────────────────── -->
  <section class="stats-band" aria-label="<?= e(__('home.stats_aria')) ?>">
    <div class="container">
      <div class="stats-band-grid">
        <div class="stats-band-item">
          <i class="bi bi-box-seam stats-band-icon" aria-hidden="true"></i>
          <span class="stats-band-value"><?= number_format($stats['products']) ?>+</span>
          <span class="stats-band-label">Produits disponibles</span>
        </div>
        <div class="stats-band-item">
          <i class="bi bi-people-fill stats-band-icon" aria-hidden="true"></i>
          <span class="stats-band-value"><?= number_format($stats['users']) ?>+</span>
          <span class="stats-band-label">Clients actifs</span>
        </div>
        <div class="stats-band-item">
          <i class="bi bi-patch-check-fill stats-band-icon" aria-hidden="true"></i>
          <span class="stats-band-value">98%</span>
          <span class="stats-band-label">Taux de satisfaction</span>
        </div>
        <div class="stats-band-item">
          <i class="bi bi-lightning-charge-fill stats-band-icon" aria-hidden="true"></i>
          <span class="stats-band-value">&lt;60s</span>
          <span class="stats-band-label">Livraison garantie</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ── 2b. COMMENT ÇA MARCHE ─────────────────────────── -->
  <section class="section section--how" aria-labelledby="how-heading">
    <div class="container">
      <header class="section-header">
        <p class="eyebrow"><?= e(__('how.eyebrow')) ?></p>
        <h2 id="how-heading">
          <?= e(__('how.title')) ?>
          <span class="gradient-text"><?= e(__('how.title_grad')) ?></span>
        </h2>
        <p><?= e(__('how.sub')) ?></p>
      </header>

      <ol class="how-steps">
        <li class="how-step animate-fade-in-up">
          <span class="how-step-num" aria-hidden="true">1</span>
          <div class="how-step-icon"><i class="bi bi-bag-check" aria-hidden="true"></i></div>
          <h3 class="how-step-title"><?= e(__('how.step1_title')) ?></h3>
          <p class="how-step-desc"><?= e(__('how.step1_desc')) ?></p>
        </li>
        <li class="how-step animate-fade-in-up animate-delay-1">
          <span class="how-step-num" aria-hidden="true">2</span>
          <div class="how-step-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></div>
          <h3 class="how-step-title"><?= e(__('how.step2_title')) ?></h3>
          <p class="how-step-desc"><?= e(__('how.step2_desc')) ?></p>
        </li>
        <li class="how-step animate-fade-in-up animate-delay-2">
          <span class="how-step-num" aria-hidden="true">3</span>
          <div class="how-step-icon"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></div>
          <h3 class="how-step-title"><?= e(__('how.step3_title')) ?></h3>
          <p class="how-step-desc"><?= e(__('how.step3_desc')) ?></p>
        </li>
      </ol>

      <div class="section-cta-row">
        <a href="<?= url('comment-ca-marche') ?>" class="btn btn-outline btn-lg"><?= e(__('how.cta_more')) ?></a>
        <a href="<?= url('products') ?>" class="btn btn-gradient btn-lg"><?= e(__('home.hero_cta_catalog')) ?></a>
      </div>
    </div>
  </section>

  <!-- ── 3. CATÉGORIES (cartes icônes) ─────────────────── -->
  <section class="section section--categories" aria-labelledby="categories-heading">
    <div class="container">
      <header class="section-intro">
        <p class="eyebrow"><?= e(__('home.categories_eyebrow')) ?></p>
        <h2 id="categories-heading" class="section-intro-title"><?= e(__('home.categories_title')) ?></h2>
        <p class="section-intro-desc"><?= e(__('home.categories_sub')) ?></p>
      </header>

      <div class="category-showcase">
        <a href="<?= url('products') ?>" class="category-card category-card--all">
          <span class="category-card-glow" aria-hidden="true"></span>
          <span class="category-card-icon"><i class="bi bi-grid-3x3-gap"></i></span>
          <span class="category-card-label"><?= e(__('home.all_products')) ?></span>
          <span class="category-card-meta">
            <span class="category-card-count"><?= array_sum(array_column($categories, 'product_count')) ?></span>
            <?= e(__('nav.products')) ?>
          </span>
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= url('categories/' . $cat['slug']) ?>" class="category-card">
          <span class="category-card-glow" aria-hidden="true"></span>
          <span class="category-card-icon"><i class="bi <?= e($cat['icon'] ?? 'bi-box-seam') ?>"></i></span>
          <span class="category-card-label"><?= e($cat['name']) ?></span>
          <span class="category-card-meta">
            <span class="category-card-count"><?= (int)$cat['product_count'] ?></span>
            <?= e(__('nav.products')) ?>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── 3b. GAMING & TOP-UP ───────────────────────────── -->
  <section class="section section--gaming" aria-labelledby="gaming-heading">
    <div class="container">
      <a href="<?= url('categories/gaming') ?>" class="gaming-banner">
        <div class="gaming-banner-copy">
          <p class="eyebrow"><?= e(__('home.gaming_eyebrow')) ?></p>
          <h2 id="gaming-heading">
            <?= e(__('home.gaming_title')) ?>
            <span class="gradient-text"><?= e(__('home.gaming_title_grad')) ?></span>
          </h2>
          <p><?= e(__('home.gaming_sub')) ?></p>
          <span class="btn btn-primary btn-sm gaming-banner-cta">
            <?= e(__('home.gaming_cta')) ?>
            <i class="bi bi-arrow-right" aria-hidden="true"></i>
          </span>
        </div>
        <div class="gaming-banner-visual" aria-hidden="true">
          <i class="bi bi-controller"></i>
        </div>
      </a>
    </div>
  </section>

  <!-- ── 4. PRODUITS POPULAIRES ────────────────────────── -->
  <section class="section section--featured" aria-labelledby="featured-heading">
    <div class="container">
      <header class="section-header section-header--left">
        <p class="eyebrow"><?= e(__('home.featured_eyebrow')) ?></p>
        <h2 id="featured-heading">
          <?= e(__('home.featured_title')) ?>
          <span class="gradient-text"><?= e(__('home.featured_title_grad')) ?></span>
        </h2>
        <p><?= e(__('home.featured_desc')) ?></p>
      </header>

      <div class="product-grid product-grid--featured">
        <?php foreach ($featured as $p): ?>
        <article class="product-card animate-fade-in-up">
          <div class="product-image">
            <img src="<?= productImageUrl($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <button type="button" class="product-wishlist-btn" aria-label="Ajouter aux favoris">
              <i class="bi bi-heart"></i>
            </button>
            <?php if ($p['sale_price']): ?>
              <span class="product-badge sale">-<?= round((1 - $p['sale_price'] / $p['price']) * 100) ?>%</span>
            <?php elseif ($p['featured']): ?>
              <span class="product-badge featured">Best Seller</span>
            <?php endif; ?>
            <div class="product-card-overlay">
              <a href="<?= url('products/' . $p['slug']) ?>" class="product-quickview-btn">
                <i class="bi bi-eye"></i> Apercu rapide
              </a>
            </div>
          </div>
          <div class="product-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem">
              <div class="product-cat"><?= e($p['category_name'] ?? '') ?></div>
              <?php if ($p['rating_avg'] > 0): ?>
              <div class="product-rating">
                <i class="bi bi-star-fill" aria-hidden="true"></i>
                <?= number_format($p['rating_avg'], 1) ?>
              </div>
              <?php endif; ?>
            </div>
            <h3 class="product-name">
              <a href="<?= url('products/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
            </h3>
            <p class="product-desc"><?= e(truncate($p['short_desc'] ?? '', 80)) ?></p>
            <div class="product-footer">
              <div class="product-price">
                <span class="price-current"><?= formatPrice($p['sale_price'] ?? $p['price']) ?></span>
                <?php if ($p['sale_price']): ?>
                  <span class="price-original"><?= formatPrice($p['price']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="product-actions product-actions--v2">
              <button type="button" class="btn btn-primary btn-sm add-to-cart" data-id="<?= $p['id'] ?>">
                <i class="bi bi-bag-plus" aria-hidden="true"></i>
                <?= e(__('home.add_cart')) ?>
              </button>
              <a href="<?= url('products/' . $p['slug']) ?>" class="product-details-link">
                <i class="bi bi-eye"></i> <?= e(__('home.view_more')) ?>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <div class="section-cta-row">
        <a href="<?= url('products') ?>" class="btn btn-outline btn-lg">
          <?= e(__('home.view_all_products')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
      </div>
    </div>
  </section>

  <?php if (!empty($productsByCategory)): ?>
  <!-- ── 5. PRODUITS PAR CATÉGORIE (onglets) ───────────── -->
  <section class="section section--segmented section--tint" x-data="{ tab: 0 }" aria-labelledby="bycat-heading">
    <div class="container">
      <header class="section-header section-header--left">
        <p class="eyebrow"><?= e(__('home.by_cat_eyebrow')) ?></p>
        <h2 id="bycat-heading">
          <?= e(__('home.by_cat_title')) ?>
          <span class="gradient-text"><?= e(__('home.by_cat_title_grad')) ?></span>
        </h2>
        <p><?= e(__('home.by_cat_desc')) ?></p>
      </header>

      <div class="segment-shell">
        <div class="segment-tabs" role="tablist">
          <?php foreach ($productsByCategory as $i => $block): ?>
          <button type="button"
                  class="segment-tab"
                  :class="{ 'is-active': tab === <?= (int)$i ?> }"
                  @click="tab = <?= (int)$i ?>"
                  role="tab"
                  :aria-selected="(tab === <?= (int)$i ?>).toString()">
            <?= e($block['category']['name']) ?>
            <span class="segment-tab-badge"><?= count($block['products']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>

        <?php foreach ($productsByCategory as $i => $block): ?>
        <div class="segment-panel" x-show="tab === <?= (int)$i ?>" x-cloak role="tabpanel">
          <div class="segment-panel-head">
            <a href="<?= url('categories/' . $block['category']['slug']) ?>" class="btn btn-ghost btn-sm segment-panel-link">
              <?= e(__('home.view_category')) ?> <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
          </div>
          <div class="product-grid product-grid--compact">
            <?php foreach ($block['products'] as $p): ?>
            <a href="<?= url('products/' . $p['slug']) ?>" class="product-card product-card--compact">
              <div class="product-image">
                <img src="<?= productImageUrl($p['image']) ?>" alt="" loading="lazy">
              </div>
              <div class="product-body">
                <div class="product-cat"><?= e($p['category_name'] ?? '') ?></div>
                <h3 class="product-name"><?= e($p['name']) ?></h3>
                <div class="product-footer product-footer--compact">
                  <span class="price-current"><?= formatPrice($p['sale_price'] ?? $p['price']) ?></span>
                  <?php if ($p['sale_price']): ?>
                    <span class="price-original"><?= formatPrice($p['price']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── 6. SERVICES (3 cartes) ────────────────────────── -->
  <section class="section section--services" aria-labelledby="services-heading">
    <div class="container">
      <header class="section-header">
        <p class="eyebrow"><?= e(__('home.services_eyebrow')) ?></p>
        <h2 id="services-heading">
          <?= e(__('home.services_title')) ?>
          <span class="gradient-text"><?= e(__('home.services_title_grad')) ?></span>
        </h2>
        <p><?= e(__('home.services_sub')) ?></p>
      </header>

      <div class="service-deck">
        <article class="service-card animate-fade-in-up">
          <div class="service-card-icon"><i class="bi bi-cloud-check" aria-hidden="true"></i></div>
          <h3 class="service-card-title"><?= e(__('home.svc_saas_title')) ?></h3>
          <p class="service-card-desc"><?= e(__('home.svc_saas_desc')) ?></p>
          <a href="<?= url('services') ?>" class="btn btn-primary btn-sm service-card-cta"><?= e(__('home.svc_cta')) ?></a>
        </article>
        <article class="service-card animate-fade-in-up animate-delay-1">
          <div class="service-card-icon"><i class="bi bi-braces" aria-hidden="true"></i></div>
          <h3 class="service-card-title"><?= e(__('home.svc_dev_title')) ?></h3>
          <p class="service-card-desc"><?= e(__('home.svc_dev_desc')) ?></p>
          <a href="<?= url('services') ?>" class="btn btn-primary btn-sm service-card-cta"><?= e(__('home.svc_cta')) ?></a>
        </article>
        <article class="service-card animate-fade-in-up animate-delay-2">
          <div class="service-card-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></div>
          <h3 class="service-card-title"><?= e(__('home.svc_consult_title')) ?></h3>
          <p class="service-card-desc"><?= e(__('home.svc_consult_desc')) ?></p>
          <a href="<?= url('services') ?>" class="btn btn-primary btn-sm service-card-cta"><?= e(__('home.svc_cta')) ?></a>
        </article>
      </div>
    </div>
  </section>

  <!-- ── 7. LIVE SALES FEED ─────────────────────────────── -->
  <div class="live-feed-section" aria-label="Ventes récentes">
    <div class="container">
      <div class="live-feed-header">
        <span class="live-feed-dot" aria-hidden="true"></span>
        <span class="live-feed-label">Ventes en direct</span>
      </div>
    </div>
    <div class="live-feed-viewport">
      <div class="live-feed-track">
        <?php
        /* Dupliquer les items pour une boucle seamless */
        $allItems = array_merge($feedItems, $feedItems);
        foreach ($allItems as $item):
        ?>
        <div class="live-feed-item">
          <div class="live-feed-avatar" aria-hidden="true"><?= e($item['init']) ?></div>
          <span class="live-feed-text">
            <strong><?= e($item['name']) ?></strong>
            vient d'acheter
            <span class="feed-product"><?= e($item['product']) ?></span>
          </span>
          <span class="live-feed-time">il y a <?= e($item['time']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ── 8. CONFIANCE + TÉMOIGNAGES ────────────────────── -->
  <section class="section section--trust section--tint" aria-labelledby="trust-heading">
    <div class="container">
      <header class="section-header">
        <p class="eyebrow"><?= e(__('home.trust_eyebrow')) ?></p>
        <h2 id="trust-heading">
          <?= e(__('home.trust_title')) ?>
          <span class="gradient-text"><?= e(__('home.trust_title_grad')) ?></span>
        </h2>
        <p><?= e(__('home.trust_sub')) ?></p>
      </header>

      <div class="trust-pillars">
        <div class="trust-pillar animate-fade-in-up">
          <div class="trust-pillar-icon"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></div>
          <h3 class="trust-pillar-title"><?= e(__('home.feat_instant_title')) ?></h3>
          <p class="trust-pillar-desc"><?= e(__('home.feat_instant_desc')) ?></p>
        </div>
        <div class="trust-pillar animate-fade-in-up animate-delay-1">
          <div class="trust-pillar-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></div>
          <h3 class="trust-pillar-title"><?= e(__('home.feat_secure_title')) ?></h3>
          <p class="trust-pillar-desc"><?= e(__('home.feat_secure_desc')) ?></p>
        </div>
        <div class="trust-pillar animate-fade-in-up animate-delay-2">
          <div class="trust-pillar-icon"><i class="bi bi-headset" aria-hidden="true"></i></div>
          <h3 class="trust-pillar-title"><?= e(__('home.feat_support_title')) ?></h3>
          <p class="trust-pillar-desc"><?= e(__('home.feat_support_desc')) ?></p>
        </div>
      </div>

      <p class="trust-more"><?= e(__('home.more_reasons')) ?></p>

      <header class="section-header section-header--tight">
        <p class="eyebrow"><?= e(__('home.testimonials_eyebrow')) ?></p>
        <h2>
          <?= e(__('home.testimonials_title')) ?>
          <span class="gradient-text"><?= e(__('home.testimonials_title_grad')) ?></span>
        </h2>
      </header>

      <div class="testimonial-deck">
        <?php
        $testimonials = [
          ['text' => __('home.testimonial_1'), 'name' => __('home.testimonial_1_name'), 'role' => __('home.testimonial_1_role'), 'rating' => 5],
          ['text' => __('home.testimonial_2'), 'name' => __('home.testimonial_2_name'), 'role' => __('home.testimonial_2_role'), 'rating' => 5],
          ['text' => __('home.testimonial_3'), 'name' => __('home.testimonial_3_name'), 'role' => __('home.testimonial_3_role'), 'rating' => 5],
        ];
        foreach ($testimonials as $t):
        ?>
        <blockquote class="testimonial-card animate-fade-in-up">
          <div class="testimonial-stars" aria-hidden="true">
            <?php for ($i = 0; $i < $t['rating']; $i++): ?>
            <i class="bi bi-star-fill"></i>
            <?php endfor; ?>
          </div>
          <p class="testimonial-text">"<?= e($t['text']) ?>"</p>
          <footer class="testimonial-author">
            <div class="author-avatar"><?= mb_strtoupper(mb_substr($t['name'], 0, 1)) ?></div>
            <div>
              <div class="author-name"><?= e($t['name']) ?></div>
              <div class="author-role"><?= e($t['role']) ?></div>
            </div>
          </footer>
        </blockquote>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── 9. BLOG (aperçu) ──────────────────────────────── -->
  <?php if (!empty($latestPosts)): ?>
  <section class="section section--blog" aria-labelledby="blog-heading">
    <div class="container">
      <header class="section-header section-header--left">
        <p class="eyebrow"><?= e(__('home.blog_eyebrow')) ?></p>
        <h2 id="blog-heading">
          <?= e(__('home.blog_title')) ?>
          <span class="gradient-text"><?= e(__('home.blog_title_grad')) ?></span>
        </h2>
      </header>
      <div class="blog-deck">
        <?php foreach ($latestPosts as $post): ?>
        <a href="<?= url('blog/' . $post['slug']) ?>" class="blog-card card animate-fade-in-up">
          <?php if ($post['image']): ?>
          <div class="blog-card-image">
            <img src="<?= UPLOAD_URL . $post['image'] ?>" alt="" loading="lazy">
          </div>
          <?php endif; ?>
          <div class="blog-card-body">
            <time class="blog-card-date"><?= formatDate($post['published_at'] ?? '') ?></time>
            <h3 class="blog-card-title"><?= e($post['title']) ?></h3>
            <p class="blog-card-excerpt"><?= e(truncate($post['excerpt'] ?? '', 100)) ?></p>
            <span class="blog-card-more"><?= e(__('home.blog_read_more')) ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="section-cta-row">
        <a href="<?= url('blog') ?>" class="btn btn-outline btn-lg"><?= e(__('home.blog_all')) ?></a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── 10. CTA FINAL ─────────────────────────────────── -->
  <section class="final-cta-band" aria-labelledby="final-cta-heading">
    <div class="final-cta-bg" aria-hidden="true"></div>
    <div class="container final-cta-inner">
      <h2 id="final-cta-heading" class="final-cta-title">
        <?= e(__('home.cta_title')) ?><br>
        <span class="gradient-text"><?= e(__('home.cta_title_grad')) ?></span>
      </h2>
      <p class="final-cta-desc"><?= e(__('home.cta_desc')) ?></p>
      <div class="final-cta-actions">
        <a href="<?= url('register') ?>" class="btn btn-gradient btn-xl"><?= e(__('home.cta_register')) ?></a>
        <a href="<?= url('products') ?>" class="btn btn-outline btn-xl final-cta-outline"><?= e(__('home.cta_explore')) ?></a>
      </div>
    </div>
  </section>

</div>
