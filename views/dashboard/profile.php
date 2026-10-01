<?php $pageTitle = 'Mon Profil'; ?>

<div style="max-width:560px">
  <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:2rem">
    <div class="author-avatar" style="width:72px;height:72px;font-size:1.75rem"><?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?></div>
    <div>
      <h2><?= e($user['name']) ?></h2>
      <div style="color:var(--text-muted);font-size:0.875rem"><?= e($user['email']) ?></div>
      <div style="margin-top:0.375rem"><?= statusBadge($user['role']) ?></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-bottom:1.5rem">Modifier le profil</h3>
    <form action="<?= url('dashboard/profile') ?>" method="POST">
      <?= csrf() ?>
      <div class="form-group">
        <label class="form-label">Nom complet</label>
        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="opacity:0.6">
      </div>
      <div class="form-group">
        <label class="form-label">Téléphone</label>
        <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+213 XX XX XX XX">
      </div>
      <hr class="divider">
      <h4 style="margin-bottom:1rem;font-size:0.9rem">Changer le mot de passe</h4>
      <div class="form-group">
        <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
        <input type="password" name="new_password" class="form-control" placeholder="Min. 8 caractères" autocomplete="new-password">
      </div>
      <button type="submit" class="btn btn-gradient">Sauvegarder les modifications</button>
    </form>
  </div>
</div>
