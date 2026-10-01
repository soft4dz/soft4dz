<?php $pageTitle = __('auth.register_title'); ?>
<div class="auth-card auth-card--bare">
  <h2 class="auth-title"><?= e(__('auth.register_title')) ?></h2>
  <p class="auth-subtitle"><?= e(__('auth.register_subtitle')) ?></p>

  <?php $errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']); ?>

  <form action="<?= url('register') ?>" method="POST" class="auth-form">
    <?= csrf() ?>

    <div class="form-group">
      <label class="form-label" for="name"><?= e(__('auth.name')) ?></label>
      <input type="text" id="name" name="name" class="form-control" value="<?= old('name') ?>"
             placeholder="<?= e(__('auth.name')) ?>" required autocomplete="name" autofocus>
      <?php if (!empty($errors['name'])): ?><div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['name']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="email"><?= e(__('auth.email')) ?></label>
      <input type="email" id="email" name="email" class="form-control" value="<?= old('email') ?>"
             placeholder="vous@exemple.com" required autocomplete="email">
      <?php if (!empty($errors['email'])): ?><div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['email']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="password"><?= e(__('auth.password')) ?></label>
      <div class="auth-password-wrap">
        <input type="password" id="password" name="password" class="form-control"
               placeholder="<?= e(__('auth.password_min')) ?>" required autocomplete="new-password">
        <button type="button" onclick="togglePass(this)" class="auth-password-toggle" aria-label="<?= e(__('auth.password')) ?>">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      <div class="auth-strength" id="strengthBar" aria-hidden="true">
        <div class="auth-strength-bar s1"></div>
        <div class="auth-strength-bar s2"></div>
        <div class="auth-strength-bar s3"></div>
        <div class="auth-strength-bar s4"></div>
      </div>
      <?php if (!empty($errors['password'])): ?><div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['password']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label class="form-label" for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label>
      <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
             placeholder="<?= e(__('auth.password_repeat')) ?>" required autocomplete="new-password">
    </div>

    <div class="auth-terms-row">
      <input type="checkbox" id="terms" name="terms" required class="auth-checkbox">
      <label for="terms" class="auth-checkbox-label">
        <?= e(__('auth.terms_before')) ?> <a href="#" class="auth-inline-link"><?= e(__('auth.terms_cgu')) ?></a> <?= e(__('auth.terms_and')) ?>
        <a href="#" class="auth-inline-link"><?= e(__('auth.terms_privacy')) ?></a>
      </label>
    </div>

    <button type="submit" class="btn btn-gradient w-full btn-lg auth-submit">
      <i class="bi bi-person-plus"></i> <?= e(__('auth.create_submit')) ?>
    </button>
  </form>

  <div class="auth-divider"><?= e(__('auth.or')) ?></div>

  <div class="social-auth-row">
    <a href="<?= url('auth/google/redirect') ?>" class="btn-social btn-social--google">
      <i class="bi bi-google" aria-hidden="true"></i> <?= e(__('auth.google')) ?>
    </a>
    <a href="<?= url('auth/facebook/redirect') ?>" class="btn-social btn-social--facebook">
      <i class="bi bi-facebook" aria-hidden="true"></i> <?= e(__('auth.facebook')) ?>
    </a>
  </div>

  <p class="auth-meta">
    <?= e(__('auth.have_account')) ?>
    <a href="<?= url('login') ?>" class="auth-inline-link"><?= e(__('auth.login_link')) ?></a>
  </p>
</div>

<script>
function togglePass(btn) {
  const inp = btn.previousElementSibling;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.querySelector('i').className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('password')?.addEventListener('input', function() {
  const v = this.value, bars = document.querySelectorAll('#strengthBar .auth-strength-bar');
  const strength = (v.length >= 8) + /[A-Z]/.test(v) + /[0-9]/.test(v) + /[^A-Za-z0-9]/.test(v);
  const colors = ['#ef4444','#f59e0b','#0ea5e9','#10b981'];
  bars.forEach((b, i) => b.style.background = i < strength ? colors[strength - 1] : 'var(--bg-surface)');
});
</script>
