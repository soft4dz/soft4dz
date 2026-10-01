<?php

ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('mb_http_output')) {
    mb_http_output('UTF-8');
}

function env(string $key, $default = null): mixed {
    static $env = null;
    if ($env === null) {
        $env = [];
        $file = dirname(__DIR__) . '/.env';
        if (file_exists($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                $env[$k] = $v;
            }
        }
    }
    return $env[$key] ?? getenv($key) ?: $default;
}

define('APP_NAME', env('APP_NAME', 'Soft4dz'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_URL', env('APP_URL', 'http://localhost/soft4dz'));
/** Chemin URL de l’app (ex. /soft4dz) pour comparaisons REQUEST_URI — sans slash final */
define('BASE_URL', rtrim((string) (parse_url(APP_URL, PHP_URL_PATH) ?: ''), '/') ?: '');
define('APP_SECRET', (string) env('APP_SECRET', ''));
// APP_SECRET signe les liens de téléchargement des tickets (clés de licence) : une valeur connue permet de les forger.
if (APP_ENV === 'production' && PHP_SAPI !== 'cli'
    && (strlen(APP_SECRET) < 32 || in_array(APP_SECRET, ['changeme', 'change_this_secret_key_in_production_32chars'], true))) {
    error_log('Soft4dz: APP_SECRET absent ou faible — démarrage refusé.');
    http_response_code(500);
    exit('Configuration invalide : définissez APP_SECRET (32 caractères aléatoires minimum) dans le fichier .env.');
}
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views');
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/' . env('UPLOAD_PATH', 'uploads/'));
define('UPLOAD_URL', APP_URL . '/uploads/');
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 7200));

/** Chargily Pay — clé secrète (test_sk_… / live_sk_…) depuis le tableau de bord */
define('CHARGILY_SECRET_KEY', (string) env('CHARGILY_SECRET_KEY', ''));
/** test | live */
define('CHARGILY_MODE', strtolower((string) env('CHARGILY_MODE', 'test')) === 'live' ? 'live' : 'test');

/** Connexion sociale — Google OAuth2 (console.cloud.google.com → Identifiants) */
define('GOOGLE_CLIENT_ID', (string) env('GOOGLE_CLIENT_ID', ''));
define('GOOGLE_CLIENT_SECRET', (string) env('GOOGLE_CLIENT_SECRET', ''));
define('GOOGLE_REDIRECT_URI', APP_URL . '/auth/google/callback');

/** Connexion sociale — Facebook Login (developers.facebook.com → Mes apps) */
define('FACEBOOK_APP_ID', (string) env('FACEBOOK_APP_ID', ''));
define('FACEBOOK_APP_SECRET', (string) env('FACEBOOK_APP_SECRET', ''));
define('FACEBOOK_REDIRECT_URI', APP_URL . '/auth/facebook/callback');

/** Email SMTP */
define('MAIL_HOST', (string) env('MAIL_HOST', ''));
define('MAIL_PORT', (int) env('MAIL_PORT', 587));
define('MAIL_USERNAME', (string) env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', (string) env('MAIL_PASSWORD', ''));
define('MAIL_FROM_ADDRESS', (string) env('MAIL_FROM_ADDRESS', 'noreply@soft4dz.com'));
define('MAIL_FROM_NAME', (string) env('MAIL_FROM_NAME', env('APP_NAME', 'Soft4dz')));
define('MAIL_ENCRYPTION', strtolower((string) env('MAIL_ENCRYPTION', 'tls'))); // tls|ssl|

/** WhatsApp Cloud API (Meta) */
define('WHATSAPP_TOKEN', (string) env('WHATSAPP_TOKEN', ''));
define('WHATSAPP_PHONE_NUMBER_ID', (string) env('WHATSAPP_PHONE_NUMBER_ID', ''));
define('WHATSAPP_API_VERSION', (string) env('WHATSAPP_API_VERSION', 'v21.0'));

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
