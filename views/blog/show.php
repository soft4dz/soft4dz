<?php $pageTitle = e($post['title']); ?>
<div class="page-shell">
  <div class="container page-section" style="max-width:780px;padding-bottom:5rem">
    <a href="<?= url('blog') ?>" class="btn btn-ghost btn-sm back-link-inline" style="margin-bottom:1.5rem">
      <i class="bi bi-arrow-left"></i> Retour au blog
    </a>

    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem">
      <div class="author-avatar" style="width:40px;height:40px;font-size:0.9rem">
        <?= mb_strtoupper(mb_substr($post['author'], 0, 1)) ?>
      </div>
      <div>
        <div style="font-weight:700;font-size:0.875rem"><?= e($post['author']) ?></div>
        <div style="font-size:0.75rem;color:var(--text-muted)"><?= formatDate($post['published_at'] ?? $post['created_at']) ?> · <?= $post['views_count'] ?> vues</div>
      </div>
    </div>

    <h1 style="font-size:clamp(1.5rem,4vw,2.25rem);margin-bottom:1rem"><?= e($post['title']) ?></h1>

    <?php if ($post['image']): ?>
    <img src="<?= UPLOAD_URL . $post['image'] ?>" alt="<?= e($post['title']) ?>"
         style="width:100%;height:320px;object-fit:cover;border-radius:var(--radius-lg);margin-bottom:2rem">
    <?php endif; ?>

    <div style="color:var(--text-secondary);line-height:1.85;font-size:1rem">
      <?= $post['body'] ?>
    </div>

    <?php if (!empty($related)): ?>
    <hr class="divider" style="margin:3rem 0">
    <h3 style="margin-bottom:1.5rem">Articles similaires</h3>
    <div class="page-grid-3">
      <?php foreach ($related as $r): ?>
      <a href="<?= url('blog/' . $r['slug']) ?>" class="related-post-card panel" style="text-decoration:none;color:inherit;padding:1rem;display:block">
        <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.375rem"><?= formatDate($r['published_at']) ?></div>
        <div style="font-weight:700;font-size:0.875rem"><?= e(truncate($r['title'], 60)) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
