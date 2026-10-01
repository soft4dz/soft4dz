<?php
$adminLogin = $adminLogin ?? false;
$pageTitle = $adminLogin ? __('auth.admin_title') : __('auth.login_title');
$formAction = $adminLogin ? url('admin/login') : url('login');
?>
<div class="auth-card auth-card--login auth-card--bare">
  <h2 class="auth-title"><?= e($adminLogin ? __('auth.admin_title') : __('auth.login_title')) ?></h2>
  <p class="auth-subtitle">
    <?= e($adminLogin ? __('auth.admin_subtitle') : __('auth.login_subtitle')) ?>
  </p>

  <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

  <form action="<?= $formAction ?>" method="POST" class="auth-form">
    <?= csrf() ?>

    <div class="form-group">
      <label class="form-label" for="email"><?= e(__('auth.email')) ?></label>
      <input type="email" id="email" name="email" class="form-control" value="<?= old('email') ?>"
             placeholder="vous@exemple.com" required autocomplete="email" autofocus>
      <?php if (!empty($errors['email'])): ?><div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['email']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label auth-form-label" for="password">
        <?= e(__('auth.password')) ?>
        <?php if (!$adminLogin): ?>
        <a href="<?= url('forgot-password') ?>" class="auth-inline-link auth-inline-link--right"><?= e(__('auth.forgot')) ?></a>
        <?php endif; ?>
      </label>
      <div class="auth-password-wrap">
        <input type="password" id="password" name="password" class="form-control"
               placeholder="<?= e(__('auth.password')) ?>" required autocomplete="current-password">
        <button type="button" onclick="togglePass(this)" class="auth-password-toggle" aria-label="<?= e(__('auth.password')) ?>">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      <?php if (!empty($errors['password'])): ?><div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['password']) ?></div><?php endif; ?>
    </div>

    <div class="auth-remember-row">
      <input type="checkbox" id="remember" name="remember" class="auth-checkbox">
      <label for="remember" class="auth-checkbox-label"><?= e(__('auth.remember')) ?></label>
    </div>

    <button type="submit" class="btn btn-gradient w-full btn-lg auth-submit">
      <i class="bi bi-box-arrow-in-right"></i> <?= e(__('auth.submit')) ?>
    </button>
  </form>

  <?php if (!$adminLogin): ?>
  <div class="auth-divider"><?= e(__('auth.or')) ?></div>

  <div class="social-auth-row">
    <a href="<?= url('auth/google/redirect') ?>" class="btn-social btn-social--google">
      <i class="bi bi-google" aria-hidden="true"></i> <?= e(__('auth.google')) ?>
    </a>
    <a href="<?= url('auth/facebook/redirect') ?>" class="btn-social btn-social--facebook">
      <i class="bi bi-facebook" aria-hidden="true"></i> <?= e(__('auth.facebook')) ?>
    </a>
  </div>
  <?php endif; ?>

  <p class="auth-meta">
    <?php if ($adminLogin): ?>
      <?= e(__('auth.are_client')) ?>
      <a href="<?= url('login') ?>" class="auth-inline-link"><?= e(__('auth.client_space')) ?></a>
    <?php else: ?>
      <?= e(__('auth.no_account')) ?>
      <a href="<?= url('register') ?>" class="auth-inline-link"><?= e(__('auth.create_account')) ?></a>
      <br>
      <span class="auth-meta-admin">
        <?= e(__('auth.are_admin')) ?>
        <a href="<?= url('admin/login') ?>" class="auth-inline-link"><?= e(__('auth.admin_login')) ?></a>
      </span>
    <?php endif; ?>
  </p>
</div>
<script>
function togglePass(btn) {
  const inp = btn.previousElementSibling;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.querySelector('i').className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
