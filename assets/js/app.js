/* ============================================================
   Soft4dz — Main Application JavaScript
   ============================================================ */

'use strict';

const I18N = typeof window.__I18N === 'object' && window.__I18N !== null ? window.__I18N : {};
const BASE_URL = (window.__BASE_URL || '').replace(/\/+$/, '');
const CSRF_TOKEN_GLOBAL = window.__CSRF || document.querySelector('input[name="_csrf"]')?.value || '';

function toUrl(path) {
  const cleanPath = String(path || '').replace(/^\/+/, '');
  return `${BASE_URL}/${cleanPath}`.replace(/([^:]\/)\/+/g, '$1');
}

// ── THÈME (clair / sombre) + meta theme-color ─────────────
(function () {
  const KEY = 'soft4dz_theme';
  const root = document.documentElement;
  const meta = document.getElementById('metaThemeColor');
  const colors = typeof window.__THEME_COLORS === 'object' && window.__THEME_COLORS !== null
    ? window.__THEME_COLORS
    : { dark: '#080510', light: '#f5f3ff' };

  function normalizeTheme(value) {
    const v = String(value ?? '').trim().toLowerCase();
    return v === 'light' ? 'light' : 'dark';
  }

  function currentTheme() {
    return normalizeTheme(root.getAttribute('data-theme'));
  }

  function applyMeta(theme) {
    if (!meta) return;
    meta.setAttribute('content', theme === 'light' ? colors.light : colors.dark);
  }

  function setTheme(theme) {
    const t = normalizeTheme(theme);
    root.setAttribute('data-theme', t);
    if (document.body) {
      document.body.setAttribute('data-theme', t);
    }
    try {
      localStorage.setItem(KEY, t);
    } catch (e) { /* ignore */ }
    applyMeta(t);
  }

  /** Synchronise l’attribut (casse / valeur vide) pour que les sélecteurs CSS [data-theme] matchent toujours. */
  function syncThemeAttribute() {
    const t = currentTheme();
    if (root.getAttribute('data-theme') !== t) {
      root.setAttribute('data-theme', t);
    }
    if (document.body && document.body.getAttribute('data-theme') !== t) {
      document.body.setAttribute('data-theme', t);
    }
  }

  syncThemeAttribute();
  applyMeta(currentTheme());

  document.addEventListener('click', (e) => {
    const btn = e.target && typeof e.target.closest === 'function' ? e.target.closest('.theme-toggle') : null;
    if (!btn) return;
    setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
  });

  window.addEventListener('pageshow', () => {
    syncThemeAttribute();
    applyMeta(currentTheme());
  });
})();

// ── RETOUR EN HAUT ─────────────────────────────────────────
(function () {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  const threshold = 320;
  const onScroll = () => {
    btn.classList.toggle('is-visible', window.scrollY > threshold);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

// ── NAVBAR SCROLL ──────────────────────────────────────────
(function () {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;
  const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 20);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// ── MOBILE MENU ────────────────────────────────────────────
(function () {
  const toggle = document.getElementById('menuToggle');
  const close  = document.getElementById('menuClose');
  const menu   = document.getElementById('mobileMenu');
  if (!toggle || !menu) return;

  toggle.addEventListener('click', () => menu.classList.add('open'));
  close?.addEventListener('click', () => menu.classList.remove('open'));
  menu.addEventListener('click', e => { if (e.target === menu) menu.classList.remove('open'); });
})();

// ── SIDEBAR TOGGLE (ADMIN/DASHBOARD) ──────────────────────
(function () {
  const btn     = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (!btn || !sidebar) return;
  btn.addEventListener('click', () => sidebar.classList.toggle('open'));
})();

// ── DASHBOARD NOTIFICATIONS DROPDOWN ───────────────────────
(function () {
  const toggle = document.getElementById('notifToggle');
  const dropdown = document.getElementById('notifDropdown');
  if (!toggle || !dropdown) return;
  toggle.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
      dropdown.classList.remove('open');
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') dropdown.classList.remove('open');
  });
})();

// ── FLASH MESSAGE AUTO-DISMISS ─────────────────────────────
(function () {
  const flash = document.getElementById('flashMsg');
  if (!flash) return;
  setTimeout(() => {
    flash.style.transition = 'opacity 0.5s, transform 0.5s';
    flash.style.opacity = '0';
    flash.style.transform = 'translateX(20px)';
    setTimeout(() => flash.remove(), 500);
  }, 4000);
})();

// ── ADD TO CART ────────────────────────────────────────────
(function () {
  const CSRF_TOKEN = document.querySelector('input[name="_csrf"]')?.value || '';

  function showToast(message, type = 'success') {
    const old = document.getElementById('cartToast');
    if (old) old.remove();

    const toast = document.createElement('div');
    toast.id = 'cartToast';
    toast.style.cssText = `
      position:fixed;bottom:2rem;left:50%;transform:translateX(-50%) translateY(100px);
      background:${type === 'success' ? '#34d399' : '#f87171'};
      color:#0b0f14;padding:0.75rem 1.5rem;border-radius:14px;font-weight:700;
      font-size:0.875rem;z-index:9999;transition:transform 0.3s ease;
      display:flex;align-items:center;gap:0.625rem;box-shadow:0 8px 32px rgba(0,0,0,0.45),0 0 24px rgba(0,245,212,0.15);
      font-family:var(--font-body, Inter),system-ui,sans-serif;
    `;
    toast.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span> ${message}`;
    document.body.appendChild(toast);

    setTimeout(() => (toast.style.transform = 'translateX(-50%) translateY(0)'), 10);
    setTimeout(() => {
      toast.style.transform = 'translateX(-50%) translateY(100px)';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  function updateCartCount(count) {
    document.querySelectorAll('#cartCount, .cart-count').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? '' : 'none';
    });
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.add-to-cart');
    if (!btn) return;
    e.preventDefault();

    const productId = btn.dataset.id;
    const qty = document.getElementById('qty')?.value || 1;

    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span>';

    try {
      const res = await fetch(toUrl('cart/add'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ product_id: productId, qty, _csrf: CSRF_TOKEN }),
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || I18N.cart_added || 'OK');
        updateCartCount(data.cart_count);
        btn.innerHTML = '<i class="bi bi-check"></i> ' + (I18N.cart_added_btn || 'OK');
        setTimeout(() => { btn.innerHTML = originalHtml; btn.disabled = false; }, 2000);
      } else {
        showToast(data.message || I18N.error || 'Error', 'error');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    } catch {
      showToast(I18N.network_error || 'Network error', 'error');
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    }
  });
})();

// ── LIVE PRODUCT SEARCH ────────────────────────────────────
(function () {
  const searchInputs = document.querySelectorAll('.search-bar input[type="text"]');
  searchInputs.forEach(input => {
    let timeout;
    const dropdown = input.parentElement.querySelector('.search-dropdown');
    if (!dropdown) return;

    input.addEventListener('input', () => {
      clearTimeout(timeout);
      const q = input.value.trim();
      if (q.length < 2) { dropdown.innerHTML = ''; dropdown.style.display = 'none'; return; }

      timeout = setTimeout(async () => {
        try {
          const res  = await fetch(toUrl(`api/products/search?q=${encodeURIComponent(q)}`));
          const data = await res.json();
          if (!data.results?.length) { dropdown.style.display = 'none'; return; }

          dropdown.innerHTML = data.results.map(p => `
            <a href="${toUrl(`products/${p.slug}`)}" class="search-result-item">
              <img src="${toUrl('assets/images/product-placeholder.svg')}" alt="" onerror="this.style.display='none'">
              <div>
                <div class="search-result-name">${escHtml(p.name)}</div>
                <div class="search-result-price">${Number(p.sale_price || p.price).toLocaleString('fr-DZ')} DZD</div>
              </div>
            </a>
          `).join('');
          dropdown.style.display = 'block';
        } catch {}
      }, 300);
    });

    document.addEventListener('click', e => {
      if (!input.parentElement.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
  });

  function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();

// ── AI CHATBOT ─────────────────────────────────────────────
(function () {
  const toggle  = document.getElementById('chatbotToggle');
  const window_ = document.getElementById('chatbotWindow');
  const input   = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');
  const messages= document.getElementById('chatMessages');
  if (!toggle) return;

  toggle.addEventListener('click', () => window_.classList.toggle('hidden'));

  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    appendMsg(text, 'user');
    input.value = '';

    const typing = appendMsg('...', 'bot');
    typing.style.opacity = '0.5';

    try {
      const res  = await fetch(toUrl('api/ai/chat'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ message: text }),
      });
      const data = await res.json();
      typing.textContent = data.reply || I18N.chat_fallback || '';
      typing.style.opacity = '1';
    } catch {
      typing.textContent = I18N.chat_connection_error || '';
    }

    messages.scrollTop = messages.scrollHeight;
  }

  function appendMsg(text, role) {
    const div = document.createElement('div');
    div.className = `chat-msg ${role}`;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
    return div;
  }

  sendBtn?.addEventListener('click', sendMessage);
  input?.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });
})();

// ── SMOOTH SCROLL ──────────────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
  });
});

// ── INTERSECTION OBSERVER (animations) ────────────────────
(function () {
  if (!('IntersectionObserver' in window)) return;
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.animate-fade-in-up').forEach(el => {
    el.style.animationPlayState = 'paused';
    observer.observe(el);
  });
})();

// ── CONFIRM DELETE ─────────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || I18N.confirm_default || 'OK')) e.preventDefault();
  });
});

// ── CART DRAWER ─────────────────────────────────────────────
(function () {
  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartDrawerOverlay');
  const openBtns = [document.getElementById('cartDrawerBtn'), document.getElementById('cartDrawerBtnMobile')].filter(Boolean);
  const closeBtn = document.getElementById('cartDrawerClose');
  const body = document.getElementById('cartDrawerBody');
  const footer = document.getElementById('cartDrawerFooter');
  const count = document.getElementById('drawerCartCount');
  const total = document.getElementById('drawerCartTotal');
  if (!drawer || !overlay || !body) return;

  const close = () => {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  };
  const open = async () => {
    drawer.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    await load();
  };
  async function load() {
    try {
      const res = await fetch(toUrl('api/cart/count'));
      const data = await res.json();
      const cartCount = Number(data.count || 0);
      if (count) count.textContent = String(cartCount);
      if (!cartCount) {
        body.innerHTML = '<div class="cart-drawer-empty"><i class="bi bi-bag-x"></i><p>Votre panier est vide.</p></div>';
        if (footer) footer.style.display = 'none';
        return;
      }
      const cartPage = await fetch(toUrl('cart'));
      const html = await cartPage.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const items = [...doc.querySelectorAll('.remove-item')].slice(0, 6).map((btn) => {
        const row = btn.closest('div[style*="display:flex"]');
        const img = row?.querySelector('img')?.getAttribute('src') || '';
        const name = row?.querySelector('a')?.textContent?.trim() || 'Produit';
        const qty = row?.querySelector('span[style*="min-width:24px"]')?.textContent?.trim() || '1';
        const price = row?.querySelector('div[style*="font-weight:800"]')?.textContent?.trim() || '';
        return { id: btn.dataset.id, img, name, qty, price };
      });
      body.innerHTML = items.map((item) => `
        <div class="cart-drawer-item">
          <img class="cart-drawer-item-img" src="${item.img}" alt="">
          <div class="cart-drawer-item-info">
            <div class="cart-drawer-item-name">${item.name}</div>
            <div class="cart-drawer-item-qty">Qté: ${item.qty}</div>
            <div class="cart-drawer-item-price">${item.price}</div>
          </div>
          <button class="cart-drawer-item-remove" data-id="${item.id}" aria-label="Retirer">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      `).join('');
      const totalEl = doc.querySelector('div[style*="font-weight:800;font-size:1.2rem"] span:last-child');
      if (total && totalEl) total.textContent = totalEl.textContent.trim();
      if (footer) footer.style.display = 'block';
    } catch {
      body.innerHTML = '<div class="cart-drawer-empty"><i class="bi bi-exclamation-circle"></i><p>Impossible de charger le panier.</p></div>';
      if (footer) footer.style.display = 'none';
    }
  }
  body.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-drawer-item-remove');
    if (!btn) return;
    await fetch(toUrl('cart/remove'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ product_id: btn.dataset.id || '', _csrf: CSRF_TOKEN_GLOBAL }),
    });
    await load();
    location.reload();
  });
  openBtns.forEach((b) => b.addEventListener('click', open));
  closeBtn?.addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();

// ── SEARCH MODAL (Spotlight) ───────────────────────────────
(function () {
  const overlay = document.getElementById('searchModalOverlay');
  const input = document.getElementById('searchModalInput');
  const results = document.getElementById('searchModalResults');
  const trigger = document.getElementById('searchTriggerBtn');
  if (!overlay || !input || !results) return;
  let focusIndex = -1;
  function open() { overlay.classList.add('open'); input.focus(); }
  function close() { overlay.classList.remove('open'); focusIndex = -1; }
  async function search(q) {
    if (q.length < 2) { results.innerHTML = '<div class="search-modal-empty">Commencez à taper pour rechercher…</div>'; return; }
    results.innerHTML = '<div class="search-modal-loading"><div class="search-modal-skel"></div><div class="search-modal-skel"></div></div>';
    try {
      const res = await fetch(toUrl(`api/products/search?q=${encodeURIComponent(q)}`));
      const data = await res.json();
      const rows = data.results || [];
      if (!rows.length) { results.innerHTML = '<div class="search-modal-empty">Aucun résultat.</div>'; return; }
      results.innerHTML = rows.map((p) => `
        <a href="${toUrl(`products/${p.slug}`)}" class="search-modal-result-item" data-result>
          <img class="search-modal-result-img" src="${toUrl('assets/images/product-placeholder.svg')}" alt="">
          <div>
            <div class="search-modal-result-name">${String(p.name || '')}</div>
            <div class="search-modal-result-price">${Number(p.sale_price || p.price || 0).toLocaleString('fr-DZ')} DZD</div>
          </div>
        </a>
      `).join('');
    } catch {
      results.innerHTML = '<div class="search-modal-empty">Erreur de recherche.</div>';
    }
  }
  input.addEventListener('input', () => search(input.value.trim()));
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); open(); return; }
    if (e.key === 'Escape') close();
    if (!overlay.classList.contains('open')) return;
    const items = [...results.querySelectorAll('[data-result]')];
    if (!items.length) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); focusIndex = (focusIndex + 1) % items.length; }
    if (e.key === 'ArrowUp') { e.preventDefault(); focusIndex = (focusIndex - 1 + items.length) % items.length; }
    items.forEach((el, i) => el.classList.toggle('focused', i === focusIndex));
    if (e.key === 'Enter' && focusIndex >= 0) items[focusIndex].click();
  });
  trigger?.addEventListener('click', open);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
})();

// ── PRODUCT WISHLIST + SPRING HOVER ────────────────────────
(function () {
  const KEY = 'soft4dz_wishlist';
  const saved = new Set(JSON.parse(localStorage.getItem(KEY) || '[]'));
  const cards = document.querySelectorAll('.product-card');
  cards.forEach((card) => {
    const id = card.querySelector('.add-to-cart')?.dataset.id;
    const btn = card.querySelector('.product-wishlist-btn');
    if (btn && id && saved.has(String(id))) btn.classList.add('active');
    btn?.addEventListener('click', () => {
      if (!id) return;
      const sid = String(id);
      if (saved.has(sid)) saved.delete(sid); else saved.add(sid);
      btn.classList.toggle('active', saved.has(sid));
      localStorage.setItem(KEY, JSON.stringify([...saved]));
    });
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      card.style.transform = `perspective(700px) rotateX(${(-y * 4).toFixed(2)}deg) rotateY(${(x * 5).toFixed(2)}deg) translateY(-3px)`;
      card.style.transition = 'transform 120ms cubic-bezier(0.2,0.8,0.2,1)';
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
      card.style.transition = 'transform 260ms cubic-bezier(0.18,0.85,0.2,1.1)';
    });
  });
})();

// ── PRODUCT GALLERY ─────────────────────────────────────────
(function () {
  const main = document.getElementById('productGalleryMain');
  const thumbs = document.querySelectorAll('[data-gallery-thumb]');
  if (!main || !thumbs.length) return;
  thumbs.forEach((thumb) => {
    thumb.addEventListener('click', () => {
      const src = thumb.getAttribute('data-gallery-src');
      if (!src) return;
      main.setAttribute('src', src);
      thumbs.forEach((t) => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });
})();
