<?php
$pageTitle = 'Paramètres';

$settingLabels = [
    'site_name' => 'Nom du site',
    'site_tagline' => 'Slogan',
    'site_email' => 'Email de contact',
    'site_phone' => 'Téléphone',
    'currency' => 'Devise',
    'currency_symbol' => 'Symbole devise',
    'tax_rate' => 'Taux de TVA (%)',
    'maintenance_mode' => 'Mode maintenance',
    'allow_registration' => 'Inscriptions ouvertes',
    'bank_name' => 'Nom de la banque',
    'bank_account' => 'N° de compte',
    'bank_rib' => 'RIB',
    'bank_iban' => 'IBAN',
    'chargily_enabled' => 'Chargily activé',
    'chargily_mode' => 'Mode Chargily',
    'chargily_api_key' => 'Clé API Chargily',
    'home_promo_product_ids' => 'Promos homepage',
];

$groupLabels = [
    'general' => 'Général',
    'payments' => 'Paiements',
    'homepage' => 'Page d\'accueil',
    'email' => 'Email',
    'social' => 'Réseaux sociaux',
    'seo' => 'SEO',
];
?>

<form action="<?= url('admin/settings') ?>" method="POST">
  <?= csrf() ?>

  <div class="form-card" style="margin-bottom:1.5rem">
    <div class="form-section-title">Promotions homepage (6 max)</div>
    <p style="margin-bottom:1rem;color:var(--text-secondary);font-size:0.875rem">
      Sélectionnez jusqu'à 6 produits en promotion affichés en premier sur la page d'accueil.
    </p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:0.75rem">
      <?php foreach (($promoProducts ?? []) as $prod): ?>
      <?php
        $pid = (int)($prod['id'] ?? 0);
        $checked = in_array($pid, $promoIds ?? [], true);
      ?>
      <label style="display:flex;align-items:flex-start;gap:0.625rem;padding:0.75rem;border:1px solid var(--border);border-radius:10px;background:var(--bg-card)">
        <input
          type="checkbox"
          name="home_promo_product_ids[]"
          value="<?= $pid ?>"
          <?= $checked ? 'checked' : '' ?>
          style="accent-color:var(--primary);margin-top:0.2rem"
        >
        <span style="display:flex;flex-direction:column;gap:0.2rem;min-width:0">
          <span style="font-weight:600;color:var(--text-primary);font-size:0.86rem;line-height:1.35"><?= e($prod['name'] ?? '') ?></span>
          <span style="font-size:0.75rem;color:var(--text-muted)">
            Prix: <?= formatPrice((float)($prod['price'] ?? 0)) ?>
            <?php if (!empty($prod['sale_price'])): ?>
              · Promo: <?= formatPrice((float)$prod['sale_price']) ?>
            <?php endif; ?>
          </span>
        </span>
      </label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="admin-grid-2" style="grid-template-columns:1fr 1fr">
    <?php foreach ($grouped as $group => $settings): ?>
    <div class="form-card">
      <div class="form-section-title"><?= e($groupLabels[$group] ?? ucfirst((string)$group)) ?></div>
      <?php foreach ($settings as $key => $s): ?>
        <?php if ($key === 'home_promo_product_ids') continue; ?>
      <div class="form-group">
        <label class="form-label admin-setting-label"><?= e($settingLabels[$key] ?? $key) ?></label>
        <?php if (($settingLabels[$key] ?? null) === null): ?>
        <div class="admin-setting-hint"><?= e($key) ?></div>
        <?php endif; ?>
        <?php if ($s['type'] === 'boolean'): ?>
        <input type="hidden" name="<?= e($key) ?>" value="0">
        <div style="display:flex;align-items:center;gap:0.625rem">
          <input type="checkbox" name="<?= e($key) ?>" value="1" <?= !empty($s['value']) && $s['value'] !== '0' ? 'checked' : '' ?> style="accent-color:var(--primary);width:16px;height:16px">
          <span style="font-size:0.875rem;color:var(--text-secondary)">Activer</span>
        </div>
        <?php elseif ($s['type'] === 'json'): ?>
        <textarea name="<?= e($key) ?>" class="form-control" rows="4" style="font-family:monospace;font-size:0.78rem"><?= e($s['value']) ?></textarea>
        <?php else: ?>
        <input type="<?= $s['type'] === 'number' ? 'number' : 'text' ?>" name="<?= e($key) ?>" class="form-control" value="<?= e($s['value']) ?>">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:1.5rem">
    <button type="submit" class="btn btn-gradient btn-lg">
      <i class="bi bi-check"></i> Sauvegarder les paramètres
    </button>
  </div>
</form>

<script>
document.addEventListener('change', function (event) {
  if (!event.target.matches('input[name="home_promo_product_ids[]"]')) return;
  const boxes = Array.from(document.querySelectorAll('input[name="home_promo_product_ids[]"]'));
  const checked = boxes.filter((b) => b.checked);
  if (checked.length > 6) {
    event.target.checked = false;
    alert('Vous pouvez sélectionner au maximum 6 promotions.');
  }
});
</script>
