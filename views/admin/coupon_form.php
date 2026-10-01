<?php
$isEdit = isset($coupon);
$pageTitle = $isEdit ? 'Modifier coupon' : 'Nouveau coupon';
$action = $isEdit ? url('admin/coupons/' . $coupon['id'] . '/edit') : url('admin/coupons/create');
$expiresVal = '';
if (!empty($coupon['expires_at'])) {
    $expiresVal = date('Y-m-d\TH:i', strtotime($coupon['expires_at']));
}
?>

<div class="admin-page-header">
  <div class="admin-page-header-left">
    <a href="<?= url('admin/coupons') ?>" class="btn btn-ghost btn-sm">← Retour</a>
    <h1><?= e($pageTitle) ?></h1>
  </div>
</div>

<form action="<?= $action ?>" method="POST" style="max-width:560px">
  <?= csrf() ?>
  <div class="form-card">
    <div class="form-section-title">Détails du coupon</div>
    <div class="form-group">
      <label class="form-label">Code *</label>
      <input type="text" name="code" class="form-control" value="<?= e($coupon['code'] ?? '') ?>" required style="text-transform:uppercase" placeholder="PROMO10">
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Type *</label>
        <select name="type" class="form-control">
          <option value="percent" <?= ($coupon['type'] ?? '') === 'percent' ? 'selected' : '' ?>>Pourcentage</option>
          <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Valeur *</label>
        <input type="number" name="value" class="form-control" value="<?= e($coupon['value'] ?? '') ?>" step="0.01" min="0" required>
      </div>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Montant minimum</label>
        <input type="number" name="min_amount" class="form-control" value="<?= e($coupon['min_amount'] ?? '') ?>" step="0.01" min="0" placeholder="Optionnel">
      </div>
      <div class="form-group">
        <label class="form-label">Utilisations max</label>
        <input type="number" name="max_uses" class="form-control" value="<?= e($coupon['max_uses'] ?? '') ?>" min="1" placeholder="Illimité">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Expire le</label>
      <input type="datetime-local" name="expires_at" class="form-control" value="<?= e($expiresVal) ?>">
    </div>
    <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;margin-bottom:1.25rem">
      <input type="checkbox" name="is_active" value="1" <?= ($coupon['is_active'] ?? 1) ? 'checked' : '' ?> style="accent-color:var(--primary)">
      <span style="font-weight:600;font-size:0.875rem">Coupon actif</span>
    </label>
    <div class="flex gap-2">
      <button type="submit" class="btn btn-gradient"><?= $isEdit ? 'Mettre à jour' : 'Créer' ?></button>
      <a href="<?= url('admin/coupons') ?>" class="btn btn-outline">Annuler</a>
    </div>
  </div>
</form>
