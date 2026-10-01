<?php $pageTitle = 'Notifications'; ?>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Mes notifications</div>
    <form action="<?= url('dashboard/notifications/read-all') ?>" method="POST">
      <?= csrf() ?>
      <button type="submit" class="btn btn-outline btn-sm" <?= ($unreadCount ?? 0) < 1 ? 'disabled' : '' ?>>
        Tout marquer comme lu
      </button>
    </form>
  </div>

  <?php if (empty($data ?? [])): ?>
    <div class="notif-empty">Aucune notification.</div>
  <?php else: ?>
    <div class="notif-list">
      <?php foreach (($data ?? []) as $n): ?>
        <form action="<?= url('dashboard/notifications/' . $n['id'] . '/read') ?>" method="POST" class="notif-item-form">
          <?= csrf() ?>
          <input type="hidden" name="next" value="<?= e($n['url'] ?: '/dashboard/notifications') ?>">
          <button type="submit" class="notif-item <?= (int)$n['is_read'] === 0 ? 'is-unread' : '' ?>">
            <div class="notif-item-title"><?= e($n['title']) ?></div>
            <?php if (!empty($n['body'])): ?><div class="notif-item-body"><?= e($n['body']) ?></div><?php endif; ?>
            <div class="notif-item-time"><?= e(timeAgo($n['created_at'])) ?></div>
          </button>
        </form>
      <?php endforeach; ?>
    </div>

    <?php if (($last_page ?? 1) > 1): ?>
      <div class="pagination" style="padding:1rem 1.25rem">
        <?php if (($current_page ?? 1) > 1): ?>
          <a href="?page=<?= (int)$current_page - 1 ?>" class="page-btn"><i class="bi bi-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = max(1, (int)$current_page - 2); $i <= min((int)$last_page, (int)$current_page + 2); $i++): ?>
          <a href="?page=<?= $i ?>" class="page-btn <?= $i === (int)$current_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if (($current_page ?? 1) < ($last_page ?? 1)): ?>
          <a href="?page=<?= (int)$current_page + 1 ?>" class="page-btn"><i class="bi bi-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
