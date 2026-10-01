<?php $pageTitle = 'Analytics'; ?>

<div class="stats-row" style="margin-bottom:1.5rem">
  <div class="stat-tile primary">
    <div class="stat-tile-header"><div class="stat-tile-icon primary"><i class="bi bi-currency-dollar"></i></div></div>
    <div class="stat-tile-value"><?= formatPrice(array_sum(array_column($monthly, 'revenue'))) ?></div>
    <div class="stat-tile-label">Revenu total (12 mois)</div>
  </div>
  <div class="stat-tile success">
    <div class="stat-tile-header"><div class="stat-tile-icon success"><i class="bi bi-receipt"></i></div></div>
    <div class="stat-tile-value"><?= number_format(array_sum(array_column($monthly, 'orders'))) ?></div>
    <div class="stat-tile-label">Commandes totales</div>
  </div>
  <div class="stat-tile warning">
    <div class="stat-tile-header"><div class="stat-tile-icon warning"><i class="bi bi-bar-chart"></i></div></div>
    <div class="stat-tile-value">
      <?php
      $totalOrders = array_sum(array_column($monthly, 'orders'));
      $totalRevenue = array_sum(array_column($monthly, 'revenue'));
      echo formatPrice($totalOrders > 0 ? $totalRevenue / $totalOrders : 0);
      ?>
    </div>
    <div class="stat-tile-label">Panier moyen</div>
  </div>
</div>

<div class="admin-grid-2 admin-grid-2--wide">
  <div class="chart-card">
    <div class="chart-header">
      <div class="chart-title">Revenus mensuels (12 mois)</div>
    </div>
    <canvas id="monthlyChart" height="100"></canvas>
  </div>

  <div class="chart-card">
    <div class="chart-header"><div class="chart-title">Revenus par catégorie</div></div>
    <canvas id="categoryChart" height="200"></canvas>
    <div class="admin-stack" style="gap:0.625rem;margin-top:1rem">
      <?php foreach (array_slice($byCategory, 0, 5) as $cat): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.8rem">
        <span style="color:var(--text-secondary)"><?= e($cat['name']) ?></span>
        <span style="font-weight:700"><?= formatPrice($cat['revenue']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
(function () {
  if (typeof Chart === 'undefined') return;
  const isLight = document.documentElement.getAttribute('data-theme') === 'light';
  const tick = isLight ? '#64748b' : '#55557a';
  const grid = isLight ? 'rgba(15,23,42,0.06)' : 'rgba(255,255,255,0.04)';
  const tipBg = isLight ? '#ffffff' : '#10101e';
  const tipBorder = isLight ? 'rgba(37,99,235,0.25)' : 'rgba(124,58,237,0.3)';
  const bar = isLight ? 'rgba(37,99,235,0.65)' : 'rgba(124,58,237,0.6)';
  const barBorder = isLight ? '#2563EB' : '#7C3AED';
  const monthly = <?= json_encode($monthly) ?>;
  const byCategory = <?= json_encode($byCategory) ?>;

  const chartDefaults = {
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: tipBg,
        borderColor: tipBorder,
        borderWidth: 1,
        titleColor: isLight ? '#0f172a' : '#fff',
        bodyColor: isLight ? '#334155' : '#e2e8f0'
      }
    },
    scales: {
      x: { grid: { color: grid }, ticks: { color: tick, font: { size: 10 } } },
      y: { grid: { color: grid }, ticks: { color: tick, font: { size: 10 } } }
    }
  };

  new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
      labels: monthly.map(d => d.month),
      datasets: [{
        label: 'Revenus',
        data: monthly.map(d => d.revenue),
        backgroundColor: bar,
        borderColor: barBorder,
        borderWidth: 1,
        borderRadius: 6,
      }]
    },
    options: chartDefaults
  });

  new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
      labels: byCategory.map(d => d.name),
      datasets: [{
        data: byCategory.map(d => d.revenue),
        backgroundColor: ['#7C3AED','#06B6D4','#F59E0B','#10B981','#EF4444','#8B5CF6'],
        borderWidth: 0,
      }]
    },
    options: {
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: tipBg,
          borderColor: tipBorder,
          borderWidth: 1,
          titleColor: isLight ? '#0f172a' : '#fff',
          bodyColor: isLight ? '#334155' : '#e2e8f0'
        }
      },
      cutout: '65%',
    }
  });
})();
</script>
