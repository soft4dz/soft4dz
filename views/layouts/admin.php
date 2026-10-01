<!DOCTYPE html>
<html lang="<?= e(html_lang()) ?>" dir="<?= e(html_dir()) ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <script>
  (function(){try{var t=(localStorage.getItem('soft4dz_theme')||'').trim().toLowerCase();if(t!=='light'&&t!=='dark')t='light';document.documentElement.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){if(document.body)document.body.setAttribute('data-theme',t);});}catch(e){}})();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($pageTitle ?? 'Admin') ?> — <?= APP_NAME ?> Admin</title>
  <meta name="theme-color" id="metaThemeColor" content="#0B1220">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php if (locale() === 'ar'): ?>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php else: ?>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/admin.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/responsive.css') ?>">
  <?php if (locale() === 'ar'): ?>
  <style>body{font-family:'IBM Plex Sans Arabic','Inter',system-ui,sans-serif}</style>
  <?php endif; ?>
  <?= $extraHead ?? '' ?>
</head>
<body>

<?php
  $adminDb = \App\Core\Database::getInstance();
  $pendingCount = $adminDb->count("SELECT COUNT(*) FROM orders WHERE payment_status='pending'");
  $openTickets = $adminDb->count("SELECT COUNT(*) FROM tickets WHERE status='open'");
  $pendingReviews = $adminDb->count("SELECT COUNT(*) FROM product_reviews WHERE status='pending'");
?>

<div class="admin-layout">
  <aside class="sidebar" id="sidebar">
    <a href="<?= url('admin') ?>" class="sidebar-logo">
      <?php $brandLogoContext = 'sidebar-admin'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
      <span class="admin-badge">ADMIN</span>
    </a>

    <nav class="sidebar-nav">
      <p class="nav-section-label"><?= e(__('admin.nav.dashboard_section')) ?></p>
      <a href="<?= url('admin') ?>" class="sidebar-link <?= isActive('admin') && !preg_match('#/admin/.+#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '') ? 'active' : '' ?>">
        <i class="bi bi-speedometer2 icon"></i> <?= e(__('admin.nav.overview')) ?>
      </a>
      <a href="<?= url('admin/analytics') ?>" class="sidebar-link <?= isActive('admin/analytics') ?>">
        <i class="bi bi-graph-up icon"></i> <?= e(__('admin.nav.analytics')) ?>
      </a>

      <p class="nav-section-label"><?= e(__('admin.nav.shop')) ?></p>
      <a href="<?= url('admin/products') ?>" class="sidebar-link <?= isActive('admin/products') ?>">
        <i class="bi bi-box-seam icon"></i> <?= e(__('admin.nav.products')) ?>
      </a>
      <a href="<?= url('admin/categories') ?>" class="sidebar-link <?= isActive('admin/categories') ?>">
        <i class="bi bi-tags icon"></i> <?= e(__('admin.nav.categories')) ?>
      </a>
      <a href="<?= url('admin/licenses') ?>" class="sidebar-link <?= isActive('admin/licenses') ?>">
        <i class="bi bi-key icon"></i> <?= e(__('admin.nav.licenses')) ?>
      </a>
      <a href="<?= url('admin/coupons') ?>" class="sidebar-link <?= isActive('admin/coupons') ?>">
        <i class="bi bi-ticket-perforated icon"></i> <?= e(__('admin.nav.coupons')) ?>
      </a>
      <a href="<?= url('admin/reviews') ?>" class="sidebar-link <?= isActive('admin/reviews') ?>">
        <i class="bi bi-star icon"></i> <?= e(__('admin.nav.reviews')) ?>
        <?php if ($pendingReviews > 0): ?><span class="badge-count"><?= $pendingReviews ?></span><?php endif; ?>
      </a>
      <a href="<?= url('admin/orders') ?>" class="sidebar-link <?= isActive('admin/orders') ?>">
        <i class="bi bi-receipt icon"></i> <?= e(__('admin.nav.orders')) ?>
        <?php if ($pendingCount > 0): ?><span class="badge-count"><?= $pendingCount ?></span><?php endif; ?>
      </a>

      <p class="nav-section-label"><?= e(__('admin.nav.ops_section')) ?></p>
      <a href="<?= url('admin/users') ?>" class="sidebar-link <?= isActive('admin/users') ?>">
        <i class="bi bi-people icon"></i> <?= e(__('admin.nav.users')) ?>
      </a>
      <a href="<?= url('admin/tickets') ?>" class="sidebar-link <?= isActive('admin/tickets') ?>">
        <i class="bi bi-headset icon"></i> <?= e(__('admin.nav.support')) ?>
        <?php if ($openTickets > 0): ?><span class="badge-count"><?= $openTickets ?></span><?php endif; ?>
      </a>
      <a href="<?= url('admin/services') ?>" class="sidebar-link <?= isActive('admin/services') ?>">
        <i class="bi bi-code-square icon"></i> <?= e(__('admin.nav.service_requests')) ?>
      </a>

      <p class="nav-section-label"><?= e(__('admin.nav.content')) ?></p>
      <a href="<?= url('admin/blog') ?>" class="sidebar-link <?= isActive('admin/blog') ?>">
        <i class="bi bi-file-text icon"></i> <?= e(__('admin.nav.blog')) ?>
      </a>
      <a href="<?= url('admin/settings') ?>" class="sidebar-link <?= isActive('admin/settings') ?>">
        <i class="bi bi-gear icon"></i> <?= e(__('admin.nav.settings')) ?>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-lang-row">
        <a href="<?= e(lang_switch_url('fr')) ?>" class="btn btn-ghost btn-sm admin-lang-btn">FR</a>
        <a href="<?= e(lang_switch_url('en')) ?>" class="btn btn-ghost btn-sm admin-lang-btn">EN</a>
        <a href="<?= e(lang_switch_url('ar')) ?>" class="btn btn-ghost btn-sm admin-lang-btn">ع</a>
      </div>
      <a href="<?= url() ?>" class="sidebar-link" target="_blank">
        <i class="bi bi-box-arrow-up-right icon"></i> <?= e(__('admin.nav.view_site')) ?>
      </a>
      <a href="<?= url('logout') ?>" class="sidebar-link">
        <i class="bi bi-box-arrow-right icon"></i> <?= e(__('nav.logout')) ?>
      </a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="topbar-btn" id="sidebarToggle" type="button" aria-label="Menu"><i class="bi bi-list"></i></button>
        <nav class="admin-breadcrumb">
          <a href="<?= url('admin') ?>"><?= e(__('admin.breadcrumb')) ?></a>
          <i class="bi bi-chevron-right" aria-hidden="true"></i>
          <span class="page-title"><?= e($pageTitle ?? __('admin.dashboard_default')) ?></span>
        </nav>
      </div>
      <div class="topbar-right">
        <button type="button" class="topbar-btn theme-toggle" title="<?= e(__('nav.theme_toggle')) ?>" aria-label="<?= e(__('nav.theme_toggle')) ?>">
          <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
          <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
        </button>
        <a href="<?= url('admin/orders?status=pending') ?>" class="topbar-btn" title="<?= e(__('admin.nav.pending_payments')) ?>">
          <i class="bi bi-bell"></i>
          <?php if ($pendingCount > 0): ?><span class="cart-count"><?= $pendingCount ?></span><?php endif; ?>
        </a>
        <?php $u = \App\Core\Auth::user(); ?>
        <a href="<?= url('admin/settings') ?>" class="topbar-avatar" title="<?= e($u['name'] ?? '') ?>">
          <?= e(mb_strtoupper(mb_substr($u['name'] ?? 'A', 0, 1))) ?>
        </a>
      </div>
    </header>

    <main class="admin-content">
      <?php $success = flash('success'); $error = flash('error'); ?>
      <?php if ($success): ?><div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?= e($success) ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?></div><?php endif; ?>
      <?= $content ?>
    </main>
  </div>
</div>

<script>window.__THEME_COLORS = { dark: '#0B1220', light: '#F8FAFC' };</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/admin.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
