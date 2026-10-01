<?php $pageTitle = 'Mon Dashboard'; ?>

<!-- Welcome -->
<div style="margin-bottom:2rem">
  <?php $u = \App\Core\Auth::user(); ?>
  <h1 style="font-size:1.5rem;margin-bottom:0.25rem">Bonjour, <?= e($u['name'] ?? '') ?> 👋</h1>
  <p style="color:var(--text-secondary)">Voici un aperçu de votre activité.</p>
</div>

<!-- Stats -->
<div class="stats-row">
  <div class="stat-tile primary">
    <div class="stat-tile-header">
      <div class="stat-tile-icon primary"><i class="bi bi-receipt"></i></div>
    </div>
    <div class="stat-tile-value"><?= count($recent) ?></div>
    <div class="stat-tile-label">Commandes récentes</div>
  </div>
  <div class="stat-tile success">
    <div class="stat-tile-header">
      <div class="stat-tile-icon success"><i class="bi bi-cash"></i></div>
    </div>
    <div class="stat-tile-value"><?= formatPrice((float)$totalSpent) ?></div>
    <div class="stat-tile-label">Total dépensé</div>
  </div>
  <div class="stat-tile warning">
    <div class="stat-tile-header">
      <div class="stat-tile-icon warning"><i class="bi bi-calendar-check"></i></div>
    </div>
    <div class="stat-tile-value"><?= count($activeSubs) ?></div>
    <div class="stat-tile-label">Abonnements actifs</div>
  </div>
  <div class="stat-tile info">
    <div class="stat-tile-header">
      <div class="stat-tile-icon info"><i class="bi bi-headset"></i></div>
    </div>
    <div class="stat-tile-value"><?= $openTickets ?></div>
    <div class="stat-tile-label">Tickets ouverts</div>
  </div>
</div>

<!-- Recent Orders -->
<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Commandes récentes</div>
    <a href="<?= url('dashboard/orders') ?>" class="btn btn-outline btn-sm">Voir tout</a>
  </div>
  <?php if (empty($recent)): ?>
  <div style="text-align:center;padding:3rem;color:var(--text-muted)">
    <i class="bi bi-bag" style="font-size:2rem;display:block;margin-bottom:0.75rem"></i>
    Aucune commande pour le moment.
    <br><a href="<?= url('products') ?>" class="btn btn-primary btn-sm" style="margin-top:1rem">Explorer le catalogue</a>
  </div>
  <?php else: ?>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>N° Commande</th>
          <th>Articles</th>
          <th>Total</th>
          <th>Paiement</th>
          <th>Statut</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $order): ?>
        <tr>
          <td style="font-family:monospace;font-weight:600;color:var(--text-primary)"><?= e($order['order_number']) ?></td>
          <td><?= $order['item_count'] ?> article<?= $order['item_count'] > 1 ? 's' : '' ?></td>
          <td style="font-weight:700"><?= formatPrice($order['total']) ?></td>
          <td><?= statusBadge($order['payment_status']) ?></td>
          <td><?= statusBadge($order['status']) ?></td>
          <td><?= formatDate($order['created_at']) ?></td>
          <td>
            <a href="<?= url('dashboard/orders/' . $order['id']) ?>" class="btn btn-ghost btn-sm">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Active Subscriptions -->
<?php if (!empty($activeSubs)): ?>
<div class="data-table-wrap" style="margin-top:1.5rem">
  <div class="data-table-header">
    <div class="data-table-title">Abonnements actifs</div>
    <a href="<?= url('dashboard/subscriptions') ?>" class="btn btn-outline btn-sm">Gérer</a>
  </div>
  <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:0.75rem">
    <?php foreach ($activeSubs as $sub): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem;background:var(--bg-surface);border-radius:var(--radius);border:1px solid var(--border)">
      <div>
        <div style="font-weight:700"><?= e($sub['plan_name']) ?></div>
        <div style="font-size:0.78rem;color:var(--text-muted)"><?= ucfirst($sub['billing_cycle']) ?></div>
      </div>
      <div style="text-align:right">
        <div><?= statusBadge($sub['status']) ?></div>
        <?php if ($sub['ends_at']): ?>
        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem">Expire le <?= formatDate($sub['ends_at']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1.5rem">
  <a href="<?= url('products') ?>" class="card" style="text-align:center;padding:1.5rem;text-decoration:none;cursor:pointer">
    <i class="bi bi-bag" style="font-size:2rem;color:var(--primary-light);margin-bottom:0.75rem;display:block"></i>
    <div style="font-weight:700;font-size:0.9rem">Explorer le catalogue</div>
    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem">Découvrez nos produits</div>
  </a>
  <a href="<?= url('dashboard/tickets') ?>" class="card" style="text-align:center;padding:1.5rem;text-decoration:none;cursor:pointer">
    <i class="bi bi-headset" style="font-size:2rem;color:var(--info);margin-bottom:0.75rem;display:block"></i>
    <div style="font-weight:700;font-size:0.9rem">Contacter le support</div>
    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem">Ouvrir un ticket</div>
  </a>
  <a href="<?= url('services') ?>" class="card" style="text-align:center;padding:1.5rem;text-decoration:none;cursor:pointer">
    <i class="bi bi-code-square" style="font-size:2rem;color:var(--secondary);margin-bottom:0.75rem;display:block"></i>
    <div style="font-weight:700;font-size:0.9rem">Demande de projet</div>
    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem">Développement sur mesure</div>
  </a>
  <a href="<?= url('dashboard/profile') ?>" class="card" style="text-align:center;padding:1.5rem;text-decoration:none;cursor:pointer">
    <i class="bi bi-person-gear" style="font-size:2rem;color:var(--accent);margin-bottom:0.75rem;display:block"></i>
    <div style="font-weight:700;font-size:0.9rem">Mon profil</div>
    <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.25rem">Gérer mon compte</div>
  </a>
</div>
