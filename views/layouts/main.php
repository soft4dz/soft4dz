<!DOCTYPE html>
<html lang="<?= e(html_lang()) ?>" dir="<?= e(html_dir()) ?>" data-theme="light">
<head>
  <meta charset="UTF-8">
  <script>
  (function(){try{var t=(localStorage.getItem('soft4dz_theme')||'').trim().toLowerCase();if(t!=='light'&&t!=='dark')t='light';document.documentElement.setAttribute('data-theme',t);document.addEventListener('DOMContentLoaded',function(){if(document.body)document.body.setAttribute('data-theme',t);});}catch(e){}})();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
  <meta name="description" content="<?= e($pageDesc ?? __('meta.description_default')) ?>">
  <meta name="theme-color" id="metaThemeColor" content="#F8FAFC">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php if (locale() === 'ar'): ?>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php else: ?>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700&family=Rubik:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php endif; ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/nova.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/nova.css') ?>">
  <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/responsive.css') ?>">
  <?php if (locale() === 'ar'): ?>
  <style>body{font-family:'IBM Plex Sans Arabic','Nunito Sans',system-ui,sans-serif}</style>
  <?php endif; ?>
  <?= $extraHead ?? '' ?>
</head>
<body class="site-redesign">

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="container navbar-inner navbar-inner--marketplace">

    <!-- Logo -->
    <a href="<?= url() ?>" class="navbar-logo">
      <?php $brandLogoContext = 'navbar'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
    </a>

    <!-- Search bar desktop -->
    <form class="navbar-search" action="<?= url('products') ?>" method="get" role="search" aria-label="<?= e(__('nav.search_placeholder')) ?>">
      <i class="bi bi-search navbar-search-icon" aria-hidden="true"></i>
      <input type="search" name="q" class="navbar-search-input"
             placeholder="<?= e(__('nav.search_placeholder')) ?>"
             value="<?= e(trim($_GET['q'] ?? '')) ?>"
             autocomplete="off"
             id="navSearchInput">
      <button type="submit" class="navbar-search-submit" aria-label="<?= e(__('nav.search_placeholder')) ?>">
        <i class="bi bi-arrow-right"></i>
      </button>
    </form>

    <!-- Nav links desktop -->
    <ul class="nav-links nav-links--marketplace">
      <li class="nav-dropdown">
        <a href="<?= url('products') ?>" class="<?= isActive('products') ?>">
          <?= e(__('nav.products')) ?> <span class="nav-caret" aria-hidden="true"></span>
        </a>
        <?php require VIEWS_PATH . '/partials/nav-categories-mega.php'; ?>
      </li>
      <li><a href="<?= url('services') ?>" class="<?= isActive('services') ?>"><?= e(__('nav.services')) ?></a></li>
      <li><a href="<?= url('blog') ?>" class="<?= isActive('blog') ?>"><?= e(__('nav.blog')) ?></a></li>
    </ul>

    <!-- Actions desktop -->
    <div class="navbar-actions">
      <!-- Search trigger (mobile/tablet) -->
      <button type="button" class="search-trigger-btn" id="searchTriggerBtn" aria-label="Rechercher">
        <i class="bi bi-search" aria-hidden="true"></i>
      </button>

      <button type="button" class="btn btn-ghost btn-sm theme-toggle" id="themeToggle"
              title="<?= e(__('nav.theme_toggle')) ?>" aria-label="<?= e(__('nav.theme_toggle')) ?>">
        <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
        <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
      </button>

      <?php $langSwitcherVariant = 'navbar'; require VIEWS_PATH . '/partials/lang-switcher.php'; ?>

      <!-- Cart button — opens drawer (masqué pour les administrateurs, qui ne sont pas des clients) -->
      <?php if (!\App\Core\Auth::isAdmin()): ?>
      <button type="button" class="cart-btn" id="cartDrawerBtn" aria-label="<?= e(__('nav.cart')) ?>">
        <i class="bi bi-bag"></i>
        <span id="cartCount"><?= cartCount() ?></span>
        <?php $cnt = cartCount(); if ($cnt > 0): ?>
        <span class="cart-count" id="cartCountBadge"><?= $cnt ?></span>
        <?php else: ?>
        <span class="cart-count" id="cartCountBadge" style="display:none">0</span>
        <?php endif; ?>
      </button>
      <?php endif; ?>

      <?php if (\App\Core\Auth::check()): ?>
        <a href="<?= \App\Core\Auth::isAdmin() ? url('admin') : url('dashboard') ?>" class="btn btn-outline btn-sm">
          <i class="bi bi-grid"></i> <?= e(__('nav.dashboard')) ?>
        </a>
        <a href="<?= url('logout') ?>" class="btn btn-ghost btn-sm"><?= e(__('nav.logout')) ?></a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="btn btn-outline btn-sm"><?= e(__('nav.login')) ?></a>
        <a href="<?= url('register') ?>" class="btn btn-gradient btn-sm"><?= e(__('nav.register')) ?></a>
      <?php endif; ?>
    </div>

    <button class="hamburger" id="menuToggle" aria-label="<?= e(__('nav.menu_aria')) ?>">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <button class="btn btn-ghost mobile-menu-close" id="menuClose" type="button" aria-label="<?= e(__('nav.close')) ?>">
    <i class="bi bi-x-lg" aria-hidden="true"></i>
  </button>
  <form class="navbar-search navbar-search--mobile" action="<?= url('products') ?>" method="get" role="search">
    <i class="bi bi-search navbar-search-icon" aria-hidden="true"></i>
    <input type="search" name="q" class="navbar-search-input" placeholder="<?= e(__('nav.search_placeholder')) ?>" autocomplete="off">
    <button type="submit" class="navbar-search-submit"><i class="bi bi-search"></i></button>
  </form>
  <a href="<?= url() ?>"        class="btn btn-ghost mobile-menu-link"><?= e(__('nav.home')) ?></a>
  <a href="<?= url('products') ?>" class="btn btn-ghost mobile-menu-link"><?= e(__('nav.products')) ?></a>
  <a href="<?= url('services') ?>" class="btn btn-ghost mobile-menu-link"><?= e(__('nav.services')) ?></a>
  <a href="<?= url('blog') ?>"  class="btn btn-ghost mobile-menu-link"><?= e(__('nav.blog')) ?></a>
  <a href="<?= url('contact') ?>" class="btn btn-ghost mobile-menu-link"><?= e(__('nav.contact')) ?></a>
  <div class="mobile-menu-tools">
    <button type="button" class="btn btn-outline btn-sm theme-toggle" aria-label="<?= e(__('nav.theme_toggle')) ?>">
      <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
      <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
    </button>
    <?php $langSwitcherVariant = 'mobile'; require VIEWS_PATH . '/partials/lang-switcher.php'; ?>
  </div>
  <hr class="divider">
  <?php if (\App\Core\Auth::check()): ?>
    <a href="<?= url('dashboard') ?>" class="btn btn-outline"><?= e(__('nav.dashboard')) ?></a>
    <a href="<?= url('logout') ?>"    class="btn btn-ghost"><?= e(__('nav.logout')) ?></a>
  <?php else: ?>
    <a href="<?= url('login') ?>"    class="btn btn-outline"><?= e(__('nav.login')) ?></a>
    <a href="<?= url('register') ?>" class="btn btn-gradient"><?= e(__('nav.register')) ?></a>
  <?php endif; ?>
</div>

<!-- FLASH MESSAGES -->
<?php $success = flash('success'); $error = flash('error'); $info = flash('info'); ?>
<?php if ($success): ?>
  <div class="alert alert-success" id="flashMsg" style="position:fixed;top:5rem;inset-inline-end:1.5rem;z-index:800;max-width:380px;animation:slide-in .3s ease">
    <i class="bi bi-check-circle-fill"></i> <?= e($success) ?>
  </div>
<?php elseif ($error): ?>
  <div class="alert alert-error" id="flashMsg" style="position:fixed;top:5rem;inset-inline-end:1.5rem;z-index:800;max-width:380px;animation:slide-in .3s ease">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
  </div>
<?php elseif ($info): ?>
  <div class="alert alert-info" id="flashMsg" style="position:fixed;top:5rem;inset-inline-end:1.5rem;z-index:800;max-width:380px;animation:slide-in .3s ease">
    <i class="bi bi-info-circle-fill"></i> <?= e($info) ?>
  </div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<main>
  <?= $content ?>
</main>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid footer-grid--marketplace">
      <div class="footer-brand footer-brand--split">
        <div class="footer-brand-logo">
          <a href="<?= url() ?>" class="navbar-logo navbar-logo--footer" aria-label="<?= e(APP_NAME) ?>">
            <?php $brandLogoContext = 'footer'; require VIEWS_PATH . '/partials/brand-logo.php'; ?>
          </a>
        </div>
        <div class="footer-brand-body">
          <p class="footer-brand-tagline"><?= e(__('footer.tagline')) ?></p>
          <div class="social-links footer-brand-social">
            <a href="#" class="social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-link" aria-label="Twitter/X"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          </div>
          <div class="footer-newsletter">
            <p class="footer-newsletter-label"><?= e(__('footer.newsletter')) ?></p>
            <form class="footer-newsletter-form" onsubmit="return false;">
              <input type="email" class="footer-newsletter-input" placeholder="<?= e(__('footer.newsletter_placeholder')) ?>" autocomplete="email">
              <button type="submit" class="btn btn-primary btn-sm footer-newsletter-submit" aria-label="<?= e(__('footer.newsletter')) ?>">
                <i class="bi bi-send"></i>
              </button>
            </form>
          </div>
        </div>
      </div>

      <div>
        <h6 class="footer-title"><?= e(__('footer.catalog')) ?></h6>
        <ul class="footer-links">
          <li><a href="<?= url('products?type=subscription') ?>"><?= e(__('footer.subscriptions')) ?></a></li>
          <li><a href="<?= url('products?type=software') ?>"><?= e(__('footer.software')) ?></a></li>
          <li><a href="<?= url('products?type=gift_card') ?>"><?= e(__('footer.gift_cards')) ?></a></li>
          <li><a href="<?= url('products?type=top_up') ?>"><?= e(__('footer.top_up')) ?></a></li>
          <li><a href="<?= url('products?type=account') ?>"><?= e(__('footer.accounts')) ?></a></li>
          <li><a href="<?= url('products?type=saas') ?>"><?= e(__('footer.saas')) ?></a></li>
          <li><a href="<?= url('categories/gaming') ?>"><?= e(__('footer.gaming')) ?></a></li>
        </ul>
      </div>

      <div>
        <h6 class="footer-title"><?= e(__('footer.company')) ?></h6>
        <ul class="footer-links">
          <li><a href="<?= url('about') ?>"><?= e(__('footer.about')) ?></a></li>
          <li><a href="<?= url('comment-ca-marche') ?>"><?= e(__('footer.how')) ?></a></li>
          <li><a href="<?= url('services') ?>"><?= e(__('footer.our_services')) ?></a></li>
          <li><a href="<?= url('blog') ?>"><?= e(__('nav.blog')) ?></a></li>
          <li><a href="<?= url('contact') ?>"><?= e(__('nav.contact')) ?></a></li>
        </ul>
      </div>

      <div>
        <h6 class="footer-title"><?= e(__('footer.support')) ?></h6>
        <ul class="footer-links">
          <li><a href="<?= url('dashboard/tickets') ?>"><?= e(__('footer.open_ticket')) ?></a></li>
          <li><a href="<?= url('dashboard/orders') ?>"><?= e(__('footer.order_tracking')) ?></a></li>
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Remboursements</a></li>
          <li><a href="#">CGU</a></li>
        </ul>
      </div>

      <div class="footer-contact">
        <h6 class="footer-title"><?= e(__('nav.contact')) ?></h6>
        <ul class="footer-links">
          <li><a href="<?= url('contact') ?>"><?= e(__('nav.contact')) ?></a></li>
        </ul>
        <a href="<?= url('contact') ?>" class="btn btn-primary btn-sm footer-contact-cta"><?= e(__('home.dev_contact')) ?></a>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© <?= date('Y') ?> Soft4dz. <?= e(__('footer.rights')) ?></p>
      <div class="flex gap-3" style="align-items:center">
        <img src="<?= asset('images/payment-cib.svg') ?>" alt="CIB" style="height:24px;opacity:0.6" onerror="this.style.display='none'">
        <img src="<?= asset('images/payment-edahabia.svg') ?>" alt="Edahabia" style="height:24px;opacity:0.6" onerror="this.style.display='none'">
        <span style="font-size:.78rem;color:var(--text-muted)"><?= e(__('footer.payment_methods')) ?></span>
      </div>
    </div>
  </div>
</footer>

<!-- BACK TO TOP -->
<button type="button" class="back-to-top" id="backToTop" title="Retour en haut" aria-label="Retour en haut">
  <i class="bi bi-chevron-up" aria-hidden="true"></i>
</button>

<?php
$waUrl = whatsappUrl(null, __('whatsapp.prefill'));
if ($waUrl):
?>
<a href="<?= e($waUrl) ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer"
   title="<?= e(__('whatsapp.title')) ?>" aria-label="<?= e(__('whatsapp.title')) ?>">
  <i class="bi bi-whatsapp" aria-hidden="true"></i>
</a>
<?php endif; ?>

<!-- AI CHATBOT -->
<button class="chatbot-toggle<?= $waUrl ? ' chatbot-toggle--with-wa' : '' ?>" id="chatbotToggle" title="<?= e(__('chat.toggle')) ?>" aria-label="Chatbot">
  <i class="bi bi-chat-dots-fill"></i>
</button>
<div class="chatbot-window hidden<?= $waUrl ? ' chatbot-window--with-wa' : '' ?>" id="chatbotWindow">
  <div class="chatbot-header">
    <div class="flex items-center gap-3">
      <div class="chatbot-avatar"><i class="bi bi-robot" aria-hidden="true"></i></div>
      <div>
        <div style="font-weight:700;font-size:.9rem"><?= e(__('chat.title')) ?></div>
        <div style="font-size:.72rem;opacity:.8"><?= e(__('chat.subtitle')) ?></div>
      </div>
    </div>
    <button class="chatbot-close" type="button" aria-label="Fermer" onclick="document.getElementById('chatbotWindow').classList.add('hidden')">
      <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
  </div>
  <div class="chatbot-messages" id="chatMessages">
    <div class="chat-msg bot"><?= e(__('chat.welcome1')) ?></div>
    <div class="chat-msg bot"><?= e(__('chat.welcome2')) ?></div>
  </div>
  <div class="chatbot-input">
    <input type="text" id="chatInput" placeholder="<?= e(__('chat.placeholder')) ?>" autocomplete="off">
    <button class="btn btn-primary btn-sm" id="chatSend"><i class="bi bi-send"></i></button>
  </div>
</div>

<!-- ═══════════════ CART DRAWER ═══════════════ -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay" aria-hidden="true"></div>
<aside class="cart-drawer" id="cartDrawer" role="dialog" aria-label="Panier" aria-modal="true">
  <div class="cart-drawer-header">
    <div class="cart-drawer-title">
      <i class="bi bi-bag" aria-hidden="true"></i>
      Mon Panier
      <span class="cart-drawer-count" id="drawerCartCount">0</span>
    </div>
    <button class="cart-drawer-close" id="cartDrawerClose" aria-label="Fermer le panier">
      <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
  </div>
  <div class="cart-drawer-body" id="cartDrawerBody">
    <div class="cart-drawer-loading" id="cartDrawerLoading">
      <div class="cart-drawer-skel"></div>
      <div class="cart-drawer-skel"></div>
      <div class="cart-drawer-skel"></div>
    </div>
  </div>
  <div class="cart-drawer-footer" id="cartDrawerFooter" style="display:none">
    <div class="cart-drawer-total">
      <span class="cart-drawer-total-label">Sous-total</span>
      <span class="cart-drawer-total-value" id="drawerCartTotal">0 DZD</span>
    </div>
    <a href="<?= url('checkout') ?>" class="btn btn-cta w-full btn-lg">
      <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
      Valider la commande
    </a>
    <a href="<?= url('cart') ?>" class="btn btn-ghost w-full" style="margin-top:.5rem;text-align:center">
      Voir le panier complet
    </a>
  </div>
</aside>

<!-- ═══════════════ SEARCH MODAL ═══════════════ -->
<div class="search-modal-overlay" id="searchModalOverlay" role="dialog" aria-label="Recherche" aria-modal="true">
  <div class="search-modal" id="searchModal">
    <div class="search-modal-input-wrap">
      <i class="bi bi-search search-modal-icon" aria-hidden="true"></i>
      <input type="text" class="search-modal-input" id="searchModalInput"
             placeholder="Rechercher un produit…" autocomplete="off" autofocus>
      <span class="search-modal-kbd">Esc</span>
    </div>
    <div class="search-modal-results" id="searchModalResults">
      <div class="search-modal-empty">Commencez à taper pour rechercher…</div>
    </div>
    <div class="search-modal-footer">
      <span><kbd>↑↓</kbd> naviguer</span>
      <span><kbd>↵</kbd> sélectionner</span>
      <span><kbd>Esc</kbd> fermer</span>
    </div>
  </div>
</div>

<!-- ═══════════════ BOTTOM NAV (mobile) ═══════════════ -->
<nav class="bottom-nav" aria-label="Navigation principale">
  <div class="bottom-nav-inner">
    <a href="<?= url() ?>" class="bottom-nav-item <?= (trim($_SERVER['REQUEST_URI'] ?? '', '/') === trim(BASE_URL, '/') || $_SERVER['REQUEST_URI'] === BASE_URL . '/') ? 'active' : '' ?>" aria-label="Accueil">
      <i class="bi bi-house-fill" aria-hidden="true"></i>
      <span>Accueil</span>
    </a>
    <a href="<?= url('products') ?>" class="bottom-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/products') !== false ? 'active' : '' ?>" aria-label="Catalogue">
      <i class="bi bi-grid-fill" aria-hidden="true"></i>
      <span>Catalogue</span>
    </a>
    <?php if (!\App\Core\Auth::isAdmin()): ?>
    <button type="button" class="bottom-nav-item" id="cartDrawerBtnMobile" aria-label="Panier">
      <i class="bi bi-bag-fill" aria-hidden="true"></i>
      <span>Panier</span>
      <?php if (cartCount() > 0): ?>
      <span class="bottom-nav-badge" id="bottomNavCartBadge"><?= cartCount() ?></span>
      <?php else: ?>
      <span class="bottom-nav-badge" id="bottomNavCartBadge" style="display:none"><?= cartCount() ?></span>
      <?php endif; ?>
    </button>
    <?php endif; ?>
    <?php if (\App\Core\Auth::check()): ?>
    <a href="<?= \App\Core\Auth::isAdmin() ? url('admin') : url('dashboard') ?>" class="bottom-nav-item <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') !== false ? 'active' : '' ?>" aria-label="Mon compte">
      <i class="bi bi-person-fill" aria-hidden="true"></i>
      <span>Compte</span>
    </a>
    <?php else: ?>
    <a href="<?= url('login') ?>" class="bottom-nav-item" aria-label="Connexion">
      <i class="bi bi-person-fill" aria-hidden="true"></i>
      <span>Connexion</span>
    </a>
    <?php endif; ?>
  </div>
</nav>

<script>
window.__THEME_COLORS = { dark: '#0B1220', light: '#F8FAFC' };
window.__I18N = <?= json_encode([
    'cart_added'            => __('js.cart_added'),
    'cart_added_btn'        => __('js.cart_added_btn'),
    'error'                 => __('js.error'),
    'network_error'         => __('js.network_error'),
    'chat_fallback'         => __('js.chat_fallback'),
    'chat_connection_error' => __('js.chat_connection_error'),
    'confirm_default'       => __('js.confirm_default'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
window.__BASE_URL = '<?= BASE_URL ?>';
window.__CSRF    = '<?= $_SESSION['_csrf_token'] ?? '' ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
