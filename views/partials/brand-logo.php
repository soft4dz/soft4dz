<?php
/**
 * Contenu visuel marque (image si présente, sinon fallback lettre S + texte).
 * Contextes : navbar | footer | sidebar | sidebar-admin | auth-hero | auth-bar | page-hero
 * (auth-hero = panneau gauche login ; auth-bar = bandeau compact mobile au-dessus du formulaire)
 */
$ctx = $brandLogoContext ?? 'navbar';
$u = brand_logo_url();
$dims = match ($ctx) {
    'footer' => ['w' => 220, 'h' => 56],
    'sidebar' => ['w' => 128, 'h' => 28],
    'sidebar-admin' => ['w' => 100, 'h' => 22],
    'auth-hero' => ['w' => 200, 'h' => 200],
    'auth-bar' => ['w' => 132, 'h' => 30],
    'page-hero' => ['w' => 200, 'h' => 52],
    'navbar' => ['w' => 176, 'h' => 36],
    default => ['w' => 176, 'h' => 36],
};
if ($u !== null): ?>
<img src="<?= e($u) ?>" alt="<?= e(APP_NAME) ?>" class="brand-logo-img brand-logo-img--<?= e($ctx) ?>" width="<?= (int) $dims['w'] ?>" height="<?= (int) $dims['h'] ?>" loading="eager" decoding="async" />
<?php else: ?>
<div class="logo-icon" aria-hidden="true">S</div>
<?php
    if ($ctx === 'navbar'): ?>
<span class="navbar-logo-text">Soft4dz</span>
<?php elseif ($ctx === 'auth-hero' || $ctx === 'auth-bar' || $ctx === 'page-hero'): ?>Soft4dz
<?php elseif ($ctx === 'sidebar' || $ctx === 'sidebar-admin' || $ctx === 'footer'): ?>Soft4dz
<?php endif;
endif;
