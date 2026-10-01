<?php $pageTitle = __('auth.forgot_title'); ?>
<div class="auth-card auth-card--bare">
  <h2 class="auth-title"><?= e(__('auth.forgot_title')) ?></h2>
  <p class="auth-subtitle"><?= e(__('auth.forgot_subtitle')) ?></p>
  <form action="<?= url('forgot-password') ?>" method="POST" class="auth-form">
    <?= csrf() ?>
    <div class="form-group">
      <label class="form-label" for="email"><?= e(__('auth.email')) ?></label>
      <input type="email" id="email" name="email" class="form-control" placeholder="vous@exemple.com" required autofocus>
    </div>
    <button type="submit" class="btn btn-gradient w-full btn-lg auth-submit">
      <i class="bi bi-envelope"></i> <?= e(__('auth.send_link')) ?>
    </button>
  </form>
  <p class="auth-meta">
    <a href="<?= url('login') ?>" class="auth-inline-link">
      <i class="bi bi-arrow-left"></i> <?= e(__('auth.back_login')) ?>
    </a>
  </p>
</div>
