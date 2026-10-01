<?php $pageTitle = "Vue d'ensemble"; ?>

<div class="stats-row">
  <a href="<?= url('admin/analytics') ?>" class="stat-tile primary">
    <div class="stat-tile-header">
      <div class="stat-tile-icon primary"><i class="bi bi-cash-stack"></i></div>
      <span class="stat-tile-change up">Aujourd'hui</span>
    </div>
    <div class="stat-tile-value"><?= formatPrice($stats['revenue_today']) ?></div>
    <div class="stat-tile-label">Revenu du jour</div>
  </a>
  <a href="<?= url('admin/analytics') ?>" class="stat-tile success">
    <div class="stat-tile-header">
      <div class="stat-tile-icon success"><i class="bi bi-graph-up"></i></div>
    </div>
    <div class="stat-tile-value"><?= formatPrice($stats['revenue_month']) ?></div>
    <div class="stat-tile-label">Revenu du mois</div>
  </a>
  <a href="<?= url('admin/orders') ?>" class="stat-tile warning">
    <div class="stat-tile-header">
      <div class="stat-tile-icon warning"><i class="bi bi-receipt"></i></div>
    </div>
    <div class="stat-tile-value"><?= (int)$stats['orders_today'] ?></div>
    <div class="stat-tile-label">Commandes aujourd'hui</div>
  </a>
  <a href="<?= url('admin/orders') ?>" class="stat-tile info">
    <div class="stat-tile-header">
      <div class="stat-tile-icon info"><i class="bi bi-calendar3"></i></div>
    </div>
    <div class="stat-tile-value"><?= (int)$stats['orders_month'] ?></div>
    <div class="stat-tile-label">Commandes du mois</div>
  </a>
  <a href="<?= url('admin/users') ?>" class="stat-tile info">
    <div class="stat-tile-header">
      <div class="stat-tile-icon info"><i class="bi bi-people"></i></div>
    </div>
    <div class="stat-tile-value"><?= number_format((int)$stats['total_users']) ?></div>
    <div class="stat-tile-label">Utilisateurs · +<?= (int)$stats['new_users_week'] ?> / 7j</div>
  </a>
  <a href="<?= url('admin/orders?status=pending') ?>" class="stat-tile danger">
    <div class="stat-tile-header">
      <div class="stat-tile-icon danger"><i class="bi bi-clock-history"></i></div>
    </div>
    <div class="stat-tile-value"><?= (int)$stats['pending_orders'] ?></div>
    <div class="stat-tile-label">Paiements en attente</div>
  </a>
  <a href="<?= url('admin/tickets?status=open') ?>" class="stat-tile warning">
    <div class="stat-tile-header">
      <div class="stat-tile-icon warning"><i class="bi bi-headset"></i></div>
    </div>
    <div class="stat-tile-value"><?= (int)$stats['open_tickets'] ?></div>
    <div class="stat-tile-label">Tickets ouverts</div>
  </a>
  <a href="<?= url('admin/products?status=active') ?>" class="stat-tile primary">
    <div class="stat-tile-header">
      <div class="stat-tile-icon primary"><i class="bi bi-box-seam"></i></div>
    </div>
    <div class="stat-tile-value"><?= (int)$stats['total_products'] ?></div>
    <div class="stat-tile-label">Produits actifs</div>
  </a>
</div>

<div class="admin-grid-2 admin-grid-2--wide" style="margin-bottom:1.5rem">
  <div class="chart-card">
    <div class="chart-header">
      <div class="chart-title">Revenus — 30 derniers jours</div>
      <div class="badge badge-success">En direct</div>
    </div>
    <canvas id="revenueChart" height="100"></canvas>
  </div>

  <div class="chart-card">
    <div class="chart-header">
      <div class="chart-title">Top Produits</div>
    </div>
    <div class="admin-stack" style="gap:0.875rem">
      <?php if (empty($topProducts)): ?>
        <div class="admin-empty">Aucune vente pour le moment.</div>
      <?php else: ?>
        <?php foreach ($topProducts as $i => $p): ?>
        <div style="display:flex;align-items:center;gap:0.875rem">
          <div style="width:24px;height:24px;background:color-mix(in srgb,var(--primary) 15%,transparent);border-radius:6px;display:grid;place-items:center;font-size:0.72rem;font-weight:800;color:var(--primary-light);flex-shrink:0">
            <?= $i + 1 ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:0.875rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($p['name']) ?></div>
            <div style="font-size:0.72rem;color:var(--text-muted)"><?= (int)$p['sales_count'] ?> ventes</div>
          </div>
          <div style="font-size:0.875rem;font-weight:700;color:var(--primary-light)"><?= formatPrice($p['price']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="data-table-wrap">
  <div class="data-table-header">
    <div class="data-table-title">Commandes récentes</div>
    <div class="data-table-actions">
      <a href="<?= url('admin/orders?status=pending') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-clock"></i> En attente (<?= (int)$stats['pending_orders'] ?>)
      </a>
      <a href="<?= url('admin/orders') ?>" class="btn btn-outline btn-sm">Toutes</a>
    </div>
  </div>
  <div class="table-wrap" style="border:none;border-radius:0">
    <table>
      <thead>
        <tr>
          <th>N° Commande</th>
          <th>Client</th>
          <th>Total</th>
          <th>Méthode</th>
          <th>Paiement</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentOrders)): ?>
        <tr><td colspan="8" class="admin-empty">Aucune commande.</td></tr>
        <?php else: ?>
        <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td style="font-family:monospace;font-weight:600"><?= e($o['order_number']) ?></td>
          <td><?= e($o['customer']) ?></td>
          <td style="font-weight:700"><?= formatPrice($o['total']) ?></td>
          <td><span class="badge badge-secondary"><?= e($o['payment_method']) ?></span></td>
          <td><?= statusBadge($o['payment_status']) ?></td>
          <td><?= statusBadge($o['status']) ?></td>
          <td><?= formatDate($o['created_at']) ?></td>
          <td>
            <a href="<?= url('admin/orders/' . $o['id']) ?>" class="btn btn-outline btn-sm">
              <i class="bi bi-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function () {
  const canvas = document.getElementById('revenueChart');
  if (!canvas || typeof Chart === 'undefined') return;
  const isLight = document.documentElement.getAttribute('data-theme') === 'light';
  const tick = isLight ? '#64748b' : '#55557a';
  const grid = isLight ? 'rgba(15,23,42,0.06)' : 'rgba(255,255,255,0.04)';
  const tipBg = isLight ? '#ffffff' : '#10101e';
  const tipBorder = isLight ? 'rgba(37,99,235,0.25)' : 'rgba(124,58,237,0.3)';
  const line = isLight ? '#2563EB' : '#7C3AED';
  const fill = isLight ? 'rgba(37,99,235,0.08)' : 'rgba(124,58,237,0.08)';
  const chartData = <?= json_encode($chartData) ?>;

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: chartData.map(d => d.date),
      datasets: [{
        label: 'Revenus (DZD)',
        data: chartData.map(d => d.revenue),
        borderColor: line,
        backgroundColor: fill,
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: line,
        pointRadius: 3,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: tipBg,
          borderColor: tipBorder,
          borderWidth: 1,
          titleColor: isLight ? '#0f172a' : '#fff',
          bodyColor: isLight ? '#334155' : '#e2e8f0',
          callbacks: {
            label: ctx => ctx.parsed.y.toLocaleString('fr-DZ') + ' DZD'
          }
        }
      },
      scales: {
        x: { grid: { color: grid }, ticks: { color: tick, font: { size: 10 } } },
        y: { grid: { color: grid }, ticks: { color: tick, font: { size: 10 }, callback: v => v.toLocaleString('fr-DZ') + ' DZD' } }
      }
    }
  });
})();
</script>
