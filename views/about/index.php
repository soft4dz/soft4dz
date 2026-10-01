<?php
$pageTitle = __('about.page_title');
$pageDesc  = __('about.page_desc');
?>

<div class="page-shell">
  <div class="page-hero">
    <div class="page-hero-glow" aria-hidden="true"></div>
    <div class="container page-hero-inner">
      <div class="page-hero-brand">
        <?php $brandLogoContext = 'page-hero'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
      </div>
      <h1 class="page-title">
        <?= e(__('about.title')) ?>
        <span class="gradient-text"><?= e(__('about.title_grad')) ?></span>
      </h1>
      <p class="page-subtitle"><?= e(__('about.subtitle')) ?></p>
    </div>
  </div>

  <div class="container page-section">
    <header class="section-header" style="margin-bottom:2.5rem">
      <p class="eyebrow"><?= e(__('how.eyebrow')) ?></p>
      <h2><?= e(__('how.title')) ?> <span class="gradient-text"><?= e(__('how.title_grad')) ?></span></h2>
      <p><?= e(__('how.sub')) ?></p>
    </header>

    <ol class="how-steps how-steps--page">
      <li class="how-step">
        <span class="how-step-num" aria-hidden="true">1</span>
        <div class="how-step-icon"><i class="bi bi-bag-check" aria-hidden="true"></i></div>
        <h3 class="how-step-title"><?= e(__('how.step1_title')) ?></h3>
        <p class="how-step-desc"><?= e(__('how.step1_desc')) ?></p>
      </li>
      <li class="how-step">
        <span class="how-step-num" aria-hidden="true">2</span>
        <div class="how-step-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></div>
        <h3 class="how-step-title"><?= e(__('how.step2_title')) ?></h3>
        <p class="how-step-desc"><?= e(__('how.step2_desc')) ?></p>
      </li>
      <li class="how-step">
        <span class="how-step-num" aria-hidden="true">3</span>
        <div class="how-step-icon"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i></div>
        <h3 class="how-step-title"><?= e(__('how.step3_title')) ?></h3>
        <p class="how-step-desc"><?= e(__('how.step3_desc')) ?></p>
      </li>
    </ol>

    <div class="about-trust-grid">
      <article class="about-trust-card">
        <div class="trust-pillar-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></div>
        <h3><?= e(__('home.feat_secure_title')) ?></h3>
        <p><?= e(__('home.feat_secure_desc')) ?></p>
      </article>
      <article class="about-trust-card">
        <div class="trust-pillar-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></div>
        <h3><?= e(__('home.feat_refund_title')) ?></h3>
        <p><?= e(__('home.feat_refund_desc')) ?></p>
      </article>
      <article class="about-trust-card">
        <div class="trust-pillar-icon"><i class="bi bi-headset" aria-hidden="true"></i></div>
        <h3><?= e(__('home.feat_support_title')) ?></h3>
        <p><?= e(__('home.feat_support_desc')) ?></p>
      </article>
    </div>

    <div class="about-cta-row">
      <a href="<?= url('products') ?>" class="btn btn-gradient btn-lg">
        <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
        <?= e(__('home.hero_cta_catalog')) ?>
      </a>
      <a href="<?= url('contact') ?>" class="btn btn-outline btn-lg"><?= e(__('nav.contact')) ?></a>
    </div>
  </div>
</div>
