/* ============================================================
   Soft4dz — Admin Panel JavaScript
   ============================================================ */

'use strict';

// ── TABLE SEARCH LIVE FILTER ───────────────────────────────
(function () {
  document.querySelectorAll('.table-search').forEach(input => {
    if (input.closest('form')) return; // let form handle it
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      const tbody = input.closest('.data-table-wrap')?.querySelector('tbody');
      if (!tbody) return;
      tbody.querySelectorAll('tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
})();

// ── ROW CLICK TO NAVIGATE ─────────────────────────────────
(function () {
  document.querySelectorAll('tr[data-href]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => window.location.href = row.dataset.href);
  });
})();

// ── AUTO-RESIZE TEXTAREAS ─────────────────────────────────
document.querySelectorAll('textarea').forEach(ta => {
  const resize = () => { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; };
  ta.addEventListener('input', resize);
  resize();
});

// ── CONFIRM DESTRUCTIVE ACTIONS ────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Êtes-vous sûr de cette action ?')) e.preventDefault();
  });
});

// ── COPY TO CLIPBOARD ─────────────────────────────────────
document.querySelectorAll('[data-copy]').forEach(btn => {
  btn.addEventListener('click', () => {
    navigator.clipboard.writeText(btn.dataset.copy).then(() => {
      const orig = btn.textContent;
      btn.textContent = '✓ Copié !';
      setTimeout(() => btn.textContent = orig, 2000);
    });
  });
});

// ── STAT COUNTER ANIMATION ────────────────────────────────
(function () {
  const counters = document.querySelectorAll('.stat-tile-value[data-count]');
  if (!counters.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el  = entry.target;
      const end = parseFloat(el.dataset.count);
      const dur = 1500;
      const step = end / (dur / 16);
      let cur = 0;
      const timer = setInterval(() => {
        cur = Math.min(cur + step, end);
        el.textContent = Math.round(cur).toLocaleString('fr-DZ');
        if (cur >= end) clearInterval(timer);
      }, 16);
      observer.unobserve(el);
    });
  });

  counters.forEach(c => observer.observe(c));
})();
