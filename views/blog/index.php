<?php $pageTitle = 'Blog'; ?>
<div class="page-shell">
  <div class="page-hero">
    <div class="container page-hero-inner">
      <div class="eyebrow" style="display:inline-block;margin-bottom:1rem">Blog & Actualités</div>
      <h1 class="page-title">Restez <span class="gradient-text">informé</span></h1>
      <p class="page-subtitle">Conseils, guides et actualités du monde digital en Algérie.</p>
    </div>
  </div>
  <div class="container page-section" style="padding-bottom:5rem">
    <?php if (empty($data)): ?>
    <div class="panel empty-state">
      <div class="empty-state-icon"><i class="bi bi-journal-text"></i></div>
      <h3>Aucun article publié pour le moment.</h3>
      <p style="color:var(--text-secondary);margin-top:0.5rem">Revenez bientôt !</p>
    </div>
    <?php else: ?>
    <div class="grid-3">
      <?php foreach ($data as $post): ?>
      <a href="<?= url('blog/' . $post['slug']) ?>" style="text-decoration:none;color:inherit" class="card">
        <?php if ($post['image']): ?>
        <img src="<?= UPLOAD_URL . $post['image'] ?>" alt="<?= e($post['title']) ?>"
             style="width:100%;height:180px;object-fit:cover;border-radius:var(--radius);margin-bottom:1rem">
        <?php endif; ?>
        <div style="font-size:0.72rem;color:var(--text-muted);margin-bottom:0.5rem">
          <?= formatDate($post['published_at'] ?? $post['created_at']) ?> · par <?= e($post['author']) ?>
        </div>
        <h3 style="font-size:1.1rem;margin-bottom:0.5rem"><?= e($post['title']) ?></h3>
        <p style="color:var(--text-secondary);font-size:0.875rem"><?= e(truncate($post['excerpt'] ?? '', 100)) ?></p>
        <div style="color:var(--primary-light);font-size:0.8rem;font-weight:600;margin-top:1rem;display:inline-flex;align-items:center;gap:0.35rem">
          Lire la suite <i class="bi bi-arrow-right"></i>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($last_page > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $last_page; $i++): ?>
      <a href="?page=<?= $i ?>" class="page-btn <?= $i === $current_page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
