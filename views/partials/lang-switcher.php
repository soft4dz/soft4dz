<?php
/**
 * Sélecteur de langue unifié FR / EN / AR
 * Variantes : navbar | mobile | compact
 */
$langSwitcherVariant = $langSwitcherVariant ?? 'navbar';
$currentLang = locale();
$langOptions = [
    'fr' => ['short' => 'FR', 'label' => __('lang.fr')],
    'en' => ['short' => 'EN', 'label' => __('lang.en')],
    'ar' => ['short' => 'ع',  'label' => __('lang.ar')],
];
$currentMeta = $langOptions[$currentLang] ?? $langOptions['fr'];
$switcherId = 'langSwitcher-' . $langSwitcherVariant . '-' . substr(md5(uniqid('', true)), 0, 6);
?>
<details class="lang-switcher lang-switcher--<?= e($langSwitcherVariant) ?>" id="<?= e($switcherId) ?>">
  <summary class="lang-switcher-trigger" aria-label="<?= e(__('nav.lang_aria')) ?>">
    <i class="bi bi-globe2" aria-hidden="true"></i>
    <span class="lang-switcher-current"><?= e($currentMeta['short']) ?></span>
    <span class="lang-switcher-current-full"><?= e($currentMeta['label']) ?></span>
    <i class="bi bi-chevron-down lang-switcher-caret" aria-hidden="true"></i>
  </summary>
  <div class="lang-switcher-menu" role="listbox" aria-label="<?= e(__('nav.lang_aria')) ?>">
    <?php foreach ($langOptions as $code => $meta): ?>
    <a href="<?= e(lang_switch_url($code)) ?>"
       class="lang-switcher-option <?= $code === $currentLang ? 'is-active' : '' ?>"
       role="option"
       aria-selected="<?= $code === $currentLang ? 'true' : 'false' ?>"
       lang="<?= e($code) ?>"
       hreflang="<?= e($code) ?>">
      <span class="lang-switcher-option-code"><?= e($meta['short']) ?></span>
      <span class="lang-switcher-option-label"><?= e($meta['label']) ?></span>
      <?php if ($code === $currentLang): ?>
      <i class="bi bi-check2" aria-hidden="true"></i>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</details>
<script>
(function () {
  var root = document.getElementById(<?= json_encode($switcherId) ?>);
  if (!root) return;
  document.addEventListener('click', function (e) {
    if (!root.open) return;
    if (!root.contains(e.target)) root.open = false;
  });
})();
</script>
