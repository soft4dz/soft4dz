<!DOCTYPE html>
<html lang="<?= e(html_lang()) ?>" dir="<?= e(html_dir()) ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e(__('errors.404_title')) ?> — Soft4dz</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/nova.css') ?>">
  <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:2rem}</style>
</head>
<body class="site-redesign">
  <div style="max-width:500px">
    <div class="empty-state-icon" style="width:80px;height:80px;font-size:1.9rem;margin-bottom:1rem">
      <i class="bi bi-search" aria-hidden="true"></i>
    </div>
    <h1 class="gradient-text" style="font-size:5rem;font-weight:900;margin-bottom:0.5rem">404</h1>
    <h2 style="margin-bottom:1rem"><?= e(__('errors.404_title')) ?></h2>
    <p style="color:var(--text-secondary);margin-bottom:2rem"><?= e(__('errors.404_desc')) ?></p>
    <div class="flex gap-3 justify-center">
      <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-gradient"><i class="bi bi-arrow-left"></i> <?= e(__('nav.home')) ?></a>
      <a href="<?= defined('APP_URL') ? APP_URL . '/products' : '/products' ?>" class="btn btn-outline"><?= e(__('nav.catalog')) ?></a>
    </div>
  </div>
</body>
</html>
