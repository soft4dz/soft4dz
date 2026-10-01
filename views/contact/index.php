<?php
$pageTitle = 'Contact';
$siteEmail = setting('site_email', 'contact@soft4dz.com');
$sitePhone = setting('site_phone', '');
$waUrl = whatsappUrl($sitePhone);
?>
<div class="page-shell">
  <div class="container page-section" style="max-width:700px">
    <div style="text-align:center;margin-bottom:3rem">
      <div class="eyebrow" style="display:inline-block;margin-bottom:1rem">Nous Contacter</div>
      <h1 class="page-title">Une question ? <span class="gradient-text">Parlons-en</span></h1>
      <p class="page-subtitle" style="margin-top:1rem">Notre équipe répond en moyenne dans les 2 heures.</p>
    </div>

    <div class="page-grid-3" style="margin-bottom:2.5rem">
      <?php
      $contacts = [
        ['icon'=>'bi-envelope','label'=>'Email','val'=>$siteEmail, 'href' => 'mailto:' . $siteEmail],
        ['icon'=>'bi-whatsapp','label'=>'WhatsApp','val'=>$sitePhone ?: '+213 …', 'href' => $waUrl],
        ['icon'=>'bi-clock','label'=>'Horaires','val'=>'Lun-Sam 8h-20h', 'href' => null],
      ];
      foreach ($contacts as $c):
      ?>
      <div style="text-align:center;padding:1.5rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
        <i class="bi <?= $c['icon'] ?>" style="font-size:1.5rem;color:var(--primary-light);display:block;margin-bottom:0.75rem"></i>
        <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.25rem"><?= e($c['label']) ?></div>
        <?php if (!empty($c['href'])): ?>
        <a href="<?= e($c['href']) ?>" style="font-weight:700;font-size:0.875rem;color:inherit;text-decoration:none" <?= str_starts_with((string)$c['href'], 'http') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($c['val']) ?></a>
        <?php else: ?>
        <div style="font-weight:700;font-size:0.875rem"><?= e($c['val']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="card panel">
      <?php $success = flash('success'); if ($success): ?><div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?= e($success) ?></div><?php endif; ?>
      <form action="<?= url('contact') ?>" method="POST">
        <?= csrf() ?>
        <div class="grid-2">
          <div class="form-group"><label class="form-label">Nom *</label><input type="text" name="name" class="form-control" required placeholder="Votre nom"></div>
          <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required placeholder="vous@exemple.com"></div>
        </div>
        <div class="form-group"><label class="form-label">Sujet *</label><input type="text" name="subject" class="form-control" required placeholder="Objet de votre message"></div>
        <div class="form-group"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="5" required placeholder="Votre message..."></textarea></div>
        <button type="submit" class="btn btn-gradient btn-lg w-full"><i class="bi bi-send"></i> Envoyer le message</button>
      </form>
    </div>
  </div>
</div>
