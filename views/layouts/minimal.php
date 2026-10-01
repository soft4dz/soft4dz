<!DOCTYPE html>
<html lang="<?= e(html_lang()) ?>" dir="<?= e(html_dir()) ?>" data-theme="light">
<head>
  <meta charset="UTF-8">
  <script>
  (function(){try{var t=(localStorage.getItem('soft4dz_theme')||'').trim().toLowerCase();if(t!=='light'&&t!=='dark')t='light';document.documentElement.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){if(document.body)document.body.setAttribute('data-theme',t);});}catch(e){}})();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= e($pageTitle ?? __('auth.title_suffix')) ?> — <?= APP_NAME ?></title>
  <meta name="theme-color" id="metaThemeColor" content="#F8FAFC">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php if (locale() === 'ar'): ?>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php else: ?>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/nova.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/nova.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/responsive.css') ?>">
  <?php if (locale() === 'ar'): ?>
  <style>body{font-family:'IBM Plex Sans Arabic','Nunito Sans',system-ui,sans-serif}</style>
  <?php endif; ?>
</head>
<body class="site-redesign auth-body auth-body--centered">
  <div class="auth-center-page">
    <header class="auth-center-top">
      <?php $langSwitcherVariant = 'compact'; require VIEWS_PATH . '/partials/lang-switcher.php'; ?>
      <button type="button" class="btn btn-ghost btn-sm theme-toggle" id="themeToggle" title="<?= e(__('nav.theme_toggle')) ?>" aria-label="<?= e(__('nav.theme_toggle')) ?>">
        <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
        <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
      </button>
    </header>

    <main class="auth-center-main">
      <a href="<?= url() ?>" class="auth-center-brand" aria-label="<?= e(APP_NAME) ?>">
        <?php $brandLogoContext = 'auth-hero'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
      </a>

      <div class="auth-center-panel">
        <?php $error = flash('error'); $success = flash('success'); ?>
        <?php if ($error):   ?><div class="alert alert-error"  ><i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= e($success) ?></div><?php endif; ?>
        <?= $content ?>
      </div>
    </main>
  </div>
<script>
window.__THEME_COLORS = { dark: '#0B1220', light: '#F8FAFC' };
window.__I18N = window.__I18N || {};
window.__BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
