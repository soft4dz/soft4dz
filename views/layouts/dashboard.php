<!DOCTYPE html>
<html lang="<?= e(html_lang()) ?>" dir="<?= e(html_dir()) ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <script>
  (function(){try{var t=(localStorage.getItem('soft4dz_theme')||'').trim().toLowerCase();if(t!=='light'&&t!=='dark')t='light';document.documentElement.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){if(document.body)document.body.setAttribute('data-theme',t);});}catch(e){}})();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle ?? __('admin.dashboard_default')) ?> — <?= APP_NAME ?></title>
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
</head>
<body>

<?php
  $notifDb = \App\Core\Database::getInstance();
  $notifUserId = \App\Core\Auth::id();
  $topNotifications = $notifDb->fetchAll(
    "SELECT id, title, body, url, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
    [$notifUserId]
  );
  $unreadNotifCount = (int)$notifDb->count(
    "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
    [$notifUserId]
  );
?>
<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <a href="<?= url() ?>" class="sidebar-logo">
      <?php $brandLogoContext = 'sidebar'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
    </a>

    <nav class="sidebar-nav">
      <p class="nav-section-label"><?= e(__('dash.nav.account')) ?></p>
      <a href="<?= url('dashboard') ?>" class="sidebar-link <?= isActive('dashboard') && !str_contains($_SERVER['REQUEST_URI'], '/') ? 'active' : '' ?>">
        <i class="bi bi-grid icon"></i> <?= e(__('dash.nav.overview')) ?>
      </a>
      <a href="<?= url('dashboard/orders') ?>" class="sidebar-link <?= isActive('dashboard/orders') ?>">
        <i class="bi bi-bag-check icon"></i> <?= e(__('dash.nav.orders')) ?>
      </a>
      <a href="<?= url('dashboard/subscriptions') ?>" class="sidebar-link <?= isActive('dashboard/subscriptions') ?>">
        <i class="bi bi-calendar-check icon"></i> <?= e(__('dash.nav.subscriptions')) ?>
      </a>
      <a href="<?= url('dashboard/tickets') ?>" class="sidebar-link <?= isActive('dashboard/tickets') ?>">
        <i class="bi bi-headset icon"></i> <?= e(__('dash.nav.support')) ?>
      </a>
      <a href="<?= url('dashboard/notifications') ?>" class="sidebar-link <?= isActive('dashboard/notifications') ?>">
        <i class="bi bi-bell icon"></i> Notifications
        <?php if ($unreadNotifCount > 0): ?><span class="badge-count"><?= $unreadNotifCount > 99 ? '99+' : $unreadNotifCount ?></span><?php endif; ?>
      </a>
      <a href="<?= url('dashboard/profile') ?>" class="sidebar-link <?= isActive('dashboard/profile') ?>">
        <i class="bi bi-person icon"></i> <?= e(__('dash.nav.profile')) ?>
      </a>

      <p class="nav-section-label" style="margin-top:1rem"><?= e(__('dash.nav.shop')) ?></p>
      <a href="<?= url('products') ?>" class="sidebar-link">
        <i class="bi bi-shop icon"></i> <?= e(__('dash.nav.catalog')) ?>
      </a>
      <a href="<?= url('cart') ?>" class="sidebar-link">
        <i class="bi bi-bag icon"></i> <?= e(__('dash.nav.cart')) ?>
        <?php $cnt = cartCount(); if ($cnt > 0): ?><span class="badge-count"><?= $cnt ?></span><?php endif; ?>
      </a>
      <a href="<?= url('services') ?>" class="sidebar-link">
        <i class="bi bi-code-square icon"></i> <?= e(__('dash.nav.services')) ?>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div style="display:flex;gap:0.25rem;justify-content:center;padding:0.35rem 0;flex-wrap:wrap">
        <a href="<?= e(lang_switch_url('fr')) ?>" class="btn btn-ghost btn-sm" style="padding:0.15rem 0.4rem;font-size:0.65rem">FR</a>
        <a href="<?= e(lang_switch_url('en')) ?>" class="btn btn-ghost btn-sm" style="padding:0.15rem 0.4rem;font-size:0.65rem">EN</a>
        <a href="<?= e(lang_switch_url('ar')) ?>" class="btn btn-ghost btn-sm" style="padding:0.15rem 0.4rem;font-size:0.65rem">ع</a>
      </div>
      <a href="<?= url() ?>" class="sidebar-link">
        <i class="bi bi-house icon"></i> <?= e(__('dash.nav.home')) ?>
      </a>
      <a href="<?= url('logout') ?>" class="sidebar-link">
        <i class="bi bi-box-arrow-right icon"></i> <?= e(__('dash.nav.logout')) ?>
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="topbar-btn" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <span class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
      </div>
      <div class="topbar-right">
        <button type="button" class="topbar-btn theme-toggle" title="<?= e(__('nav.theme_toggle')) ?>" aria-label="<?= e(__('nav.theme_toggle')) ?>">
          <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
          <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
        </button>
        <div class="notif-wrap">
          <button type="button" class="topbar-btn notif-toggle" id="notifToggle" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <?php if ($unreadNotifCount > 0): ?>
              <span class="notif-badge"><?= $unreadNotifCount > 99 ? '99+' : $unreadNotifCount ?></span>
            <?php endif; ?>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-dropdown-head">
              <span>Notifications</span>
              <a href="<?= url('dashboard/notifications') ?>" class="notif-view-all">Tout voir</a>
            </div>
            <?php if (empty($topNotifications)): ?>
              <div class="notif-empty">Aucune notification pour le moment.</div>
            <?php else: ?>
              <div class="notif-list">
                <?php foreach ($topNotifications as $n): ?>
                  <form action="<?= url('dashboard/notifications/' . $n['id'] . '/read') ?>" method="POST" class="notif-item-form">
                    <?= csrf() ?>
                    <input type="hidden" name="next" value="<?= e($n['url'] ?: '/dashboard/notifications') ?>">
                    <button type="submit" class="notif-item <?= (int)$n['is_read'] === 0 ? 'is-unread' : '' ?>">
                      <div class="notif-item-title"><?= e($n['title']) ?></div>
                      <?php if (!empty($n['body'])): ?><div class="notif-item-body"><?= e(truncate($n['body'], 90)) ?></div><?php endif; ?>
                      <div class="notif-item-time"><?= e(timeAgo($n['created_at'])) ?></div>
                    </button>
                  </form>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <a href="<?= url('cart') ?>" class="topbar-btn" title="Panier">
          <i class="bi bi-bag"></i>
          <?php if (cartCount() > 0): ?><span class="cart-count"><?= cartCount() ?></span><?php endif; ?>
        </a>
        <?php $u = \App\Core\Auth::user(); ?>
        <div class="topbar-avatar" title="<?= e($u['name'] ?? '') ?>">
          <?= e(mb_strtoupper(mb_substr($u['name'] ?? 'U', 0, 1))) ?>
        </div>
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
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
