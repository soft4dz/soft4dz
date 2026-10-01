<?php

use App\Core\Locale;

function locale(): string {
    return Locale::current();
}

/** Traduction : clés dans lang/{locale}.php ; remplacements :n dans la chaîne */
function __(string $key, array $replace = []): string {
    return Locale::translate($key, $replace);
}

/** URL de changement de langue en conservant la page actuelle */
function html_lang(): string {
    return match (locale()) {
        'ar' => 'ar',
        'en' => 'en',
        default => 'fr',
    };
}

function html_dir(): string {
    return Locale::isRtl() ? 'rtl' : 'ltr';
}

function lang_switch_url(string $code): string {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base    = parse_url(APP_URL, PHP_URL_PATH) ?: '';
    if ($base !== '' && str_starts_with($uriPath, $base)) {
        $uriPath = substr($uriPath, strlen($base)) ?: '/';
    }
    if ($uriPath === '' || $uriPath[0] !== '/') {
        $uriPath = '/' . ltrim($uriPath, '/');
    }
    $query    = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    $redirect = $uriPath . ($query ? '?' . $query : '');
    return url('lang/' . $code . '?redirect=' . rawurlencode($redirect));
}

function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * URL du logo marque (cache-bust). Fichiers recherchés sous /assets/images/.
 * Renommer votre export en logo-soft4dz.png est recommandé (pas d’espaces dans l’URL).
 */
function brand_logo_url(): ?string {
    $candidates = [
        'images/logo-soft4dz.png',
        'images/Logo-SOFT4DZ.png',
        'images/Logo - SOFT4DZ.png',
    ];
    foreach ($candidates as $rel) {
        $full = ROOT_PATH . '/assets/' . $rel;
        if (is_readable($full)) {
            return asset($rel) . '?v=' . filemtime($full);
        }
    }
    return null;
}

function e(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function old(string $key, string $default = ''): string {
    $val = $_SESSION['old_input'][$key] ?? $default;
    unset($_SESSION['old_input'][$key]);
    return e($val);
}

function flash(string $key): string {
    $val = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $val;
}

function setFlash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function csrf(): string {
    $token = \App\Core\Auth::csrfToken();
    return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
}

function csrf_token(): string {
    return \App\Core\Auth::csrfToken();
}

function formatPrice(float $amount, string $currency = 'DZD'): string {
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

function formatDate(string $date, ?string $format = null): string {
    if ($format === null) {
        $format = locale() === 'en' ? 'M j, Y' : (locale() === 'ar' ? 'Y/m/d' : 'd/m/Y');
    }
    return date($format, strtotime($date));
}

function truncate(string $str, int $len = 100, string $suffix = '…'): string {
    return mb_strlen($str) > $len ? mb_substr($str, 0, $len) . $suffix : $str;
}

function slug(string $text): string {
    $text = mb_strtolower($text);
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-');
}

function avatarUrl(?string $avatar): string {
    if ($avatar && file_exists(UPLOAD_PATH . $avatar)) {
        return UPLOAD_URL . $avatar;
    }
    return ASSETS_URL . '/images/avatar-default.svg';
}

function productImageUrl(?string $img): string {
    if ($img && file_exists(UPLOAD_PATH . 'products/' . $img)) {
        return UPLOAD_URL . 'products/' . $img;
    }
    // Visuels du catalogue générés par tools/product-images (versionnés avec le code)
    if ($img && is_file(ROOT_PATH . '/assets/images/products/' . basename($img))) {
        return ASSETS_URL . '/images/products/' . basename($img);
    }
    return ASSETS_URL . '/images/product-placeholder.svg';
}

/** Types de sortie produit (admin choisit pour chaque produit). */
function productTypes(): array {
    return [
        'top_up'       => 'Top-up / Recharge',
        'account'      => 'Compte',
        'gift_card'    => 'Carte cadeau',
        'subscription' => 'Abonnement',
        'software'     => 'Logiciel',
        'saas'         => 'SaaS',
        'service'      => 'Service',
    ];
}

function productTypeLabel(?string $type): string {
    $types = productTypes();
    if ($type && isset($types[$type])) {
        return $types[$type];
    }
    return $type ? ucfirst(str_replace('_', ' ', $type)) : '—';
}

function productTypeIcons(): array {
    return [
        'top_up'       => 'bi-phone-vibrate',
        'account'      => 'bi-person-badge',
        'gift_card'    => 'bi-gift',
        'subscription' => 'bi-arrow-repeat',
        'software'     => 'bi-window',
        'saas'         => 'bi-cloud-check',
        'service'      => 'bi-tools',
    ];
}

/** Valeur d'un paramètre site (table settings). */
function setting(string $key, string $default = ''): string {
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    try {
        $row = \App\Core\Database::getInstance()->fetch(
            'SELECT value FROM settings WHERE `key` = ? LIMIT 1',
            [$key]
        );
        $cache[$key] = (string) ($row['value'] ?? $default);
    } catch (\Throwable) {
        $cache[$key] = $default;
    }
    return $cache[$key];
}

/**
 * Lien wa.me à partir d'un numéro (site_phone par défaut).
 * Retourne null si le numéro est invalide / placeholder.
 */
function whatsappUrl(?string $phone = null, string $text = ''): ?string {
    $raw = $phone ?? setting('site_phone', '');
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if (strlen($digits) < 10) {
        return null;
    }
    $url = 'https://wa.me/' . $digits;
    if ($text !== '') {
        $url .= '?text=' . rawurlencode($text);
    }
    return $url;
}

function statusBadge(string $status): string {
    $map = [
        'active'     => 'badge-success',
        'inactive'   => 'badge-secondary',
        'pending'    => 'badge-warning',
        'completed'  => 'badge-success',
        'cancelled'  => 'badge-danger',
        'paid'       => 'badge-success',
        'failed'     => 'badge-danger',
        'open'       => 'badge-info',
        'closed'     => 'badge-secondary',
        'processing' => 'badge-warning',
        'refunded'   => 'badge-secondary',
        'unpaid'     => 'badge-warning',
        'draft'      => 'badge-secondary',
        'approved'   => 'badge-success',
        'rejected'   => 'badge-danger',
        'published'  => 'badge-success',
        'replied'    => 'badge-info',
        'resolved'   => 'badge-success',
        'banned'     => 'badge-danger',
        'customer'   => 'badge-info',
        'vendor'     => 'badge-warning',
        'admin'      => 'badge-danger',
    ];
    $class   = $map[$status] ?? 'badge-secondary';
    $labelKey = 'status.' . $status;
    $label   = __($labelKey);
    if ($label === $labelKey) {
        $label = ucfirst(str_replace('_', ' ', $status));
    }
    return '<span class="badge ' . $class . '">' . e($label) . '</span>';
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match (true) {
        $diff < 60     => __('time.ago_seconds'),
        $diff < 3600   => __('time.ago_minutes', ['n' => (string) floor($diff / 60)]),
        $diff < 86400  => __('time.ago_hours', ['n' => (string) floor($diff / 3600)]),
        $diff < 604800 => __('time.ago_days', ['n' => (string) floor($diff / 86400)]),
        default        => formatDate($datetime),
    };
}

function isActive(string $path): string {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base    = parse_url(APP_URL, PHP_URL_PATH) . '/' . ltrim($path, '/');
    return str_starts_with($current, $base) ? 'active' : '';
}

function cartCount(): int {
    if (!\App\Core\Auth::check()) {
        return array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
    }
    $db = \App\Core\Database::getInstance();
    return (int) $db->count(
        "SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?",
        [\App\Core\Auth::id()]
    );
}

function dump(mixed ...$vars): void {
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;overflow:auto">';
    foreach ($vars as $v) var_dump($v);
    echo '</pre>';
}

/** Rubriques actives (parent_id NULL) avec leurs sous-rubriques. */
function categoryTree(bool $activeOnly = true): array {
    static $cache = [];
    $key = $activeOnly ? 'active' : 'all';
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $db = \App\Core\Database::getInstance();
    $where = $activeOnly ? 'WHERE is_active = 1' : '';
    $rows  = $db->fetchAll("SELECT * FROM categories $where ORDER BY sort_order ASC, name ASC");

    $byParent = [];
    foreach ($rows as $row) {
        $pid = $row['parent_id'] ? (int) $row['parent_id'] : 0;
        $byParent[$pid][] = $row;
    }

    $tree = [];
    foreach ($byParent[0] ?? [] as $parent) {
        $parent['children'] = $byParent[(int) $parent['id']] ?? [];
        $tree[] = $parent;
    }

    return $cache[$key] = $tree;
}

/** Liste à plat pour les &lt;select&gt; admin (rubrique → sous-rubrique indentée). */
function categoriesForSelect(bool $activeOnly = false): array {
    $db = \App\Core\Database::getInstance();
    $where = $activeOnly ? 'WHERE is_active = 1' : '';
    $rows  = $db->fetchAll("SELECT id, name, parent_id FROM categories $where ORDER BY COALESCE(parent_id, id), parent_id IS NOT NULL, sort_order, name");

    $parents = [];
    $children = [];
    foreach ($rows as $row) {
        if ($row['parent_id']) {
            $children[(int) $row['parent_id']][] = $row;
        } else {
            $parents[] = $row;
        }
    }

    $flat = [];
    foreach ($parents as $p) {
        $flat[] = ['id' => (int) $p['id'], 'name' => $p['name'], 'depth' => 0];
        foreach ($children[(int) $p['id']] ?? [] as $c) {
            $flat[] = ['id' => (int) $c['id'], 'name' => $c['name'], 'depth' => 1];
        }
    }
    return $flat;
}

/** IDs d'une rubrique + ses sous-rubriques (filtre catalogue). */
function categoryFilterIds(?string $slug): array {
    if ($slug === null || $slug === '') {
        return [];
    }

    $db  = \App\Core\Database::getInstance();
    $cat = $db->fetch('SELECT id, parent_id FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    if (!$cat) {
        return [];
    }

    $id = (int) $cat['id'];
    if (!$cat['parent_id']) {
        $childIds = $db->fetchAll('SELECT id FROM categories WHERE parent_id = ? AND is_active = 1', [$id]);
        return array_merge([$id], array_map(fn($r) => (int) $r['id'], $childIds));
    }

    return [$id];
}

/** Slug unique pour une catégorie. */
function uniqueCategorySlug(string $name, ?int $excludeId = null): string {
    $db   = \App\Core\Database::getInstance();
    $base = slug($name) ?: 'rubrique';
    $slug = $base;
    $i    = 1;

    while (true) {
        $params = [$slug];
        $sql    = 'SELECT id FROM categories WHERE slug = ?';
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $exists = $db->fetch($sql, $params);
        if (!$exists) {
            return $slug;
        }
        $slug = $base . '-' . (++$i);
    }
}
