<?php

namespace App\Core;

/**
 * Langues : français (défaut), anglais, arabe (RTL).
 */
final class Locale {
    public const SUPPORTED = ['fr', 'en', 'ar'];
    public const COOKIE = 'soft4dz_locale';

    public static function init(): void {
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::SUPPORTED, true)) {
            self::set($_GET['lang']);
            return;
        }

        if (!empty($_SESSION['locale']) && in_array($_SESSION['locale'], self::SUPPORTED, true)) {
            return;
        }

        $cookie = $_COOKIE[self::COOKIE] ?? '';
        if (is_string($cookie) && in_array($cookie, self::SUPPORTED, true)) {
            $_SESSION['locale'] = $cookie;
            return;
        }

        $_SESSION['locale'] = self::detectFromBrowser();
    }

    public static function set(string $locale): void {
        if (!in_array($locale, self::SUPPORTED, true)) {
            return;
        }
        $_SESSION['locale'] = $locale;
        if (PHP_SAPI === 'cli') {
            return;
        }
        setcookie(self::COOKIE, $locale, [
            'expires'  => time() + 365 * 24 * 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function current(): string {
        $loc = $_SESSION['locale'] ?? 'fr';
        return in_array($loc, self::SUPPORTED, true) ? $loc : 'fr';
    }

    public static function isRtl(): bool {
        return self::current() === 'ar';
    }

    /** @return list<string> */
    public static function all(): array {
        return self::SUPPORTED;
    }

    public static function label(string $code): string {
        return match ($code) {
            'en' => 'English',
            'ar' => 'العربية',
            default => 'Français',
        };
    }

    public static function short(string $code): string {
        return match ($code) {
            'en' => 'EN',
            'ar' => 'ع',
            default => 'FR',
        };
    }

    private static function detectFromBrowser(): string {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if ($header === '') {
            return 'fr';
        }
        foreach (explode(',', $header) as $part) {
            $tag = strtolower(trim(explode(';', $part)[0]));
            $primary = substr($tag, 0, 2);
            if (in_array($primary, self::SUPPORTED, true)) {
                return $primary;
            }
        }
        return 'fr';
    }

    /**
     * @param array<string, string> $replace remplacements pour :key dans la chaîne
     */
    public static function translate(string $key, array $replace = []): string {
        static $cache = [];
        $loc = self::current();
        foreach ([$loc, 'fr'] as $lang) {
            if (!isset($cache[$lang])) {
                $path = ROOT_PATH . '/lang/' . $lang . '.php';
                $cache[$lang] = is_file($path) ? require $path : [];
            }
        }
        $str = $cache[$loc][$key] ?? $cache['fr'][$key] ?? $key;
        foreach ($replace as $k => $v) {
            $str = str_replace(':' . $k, (string) $v, $str);
        }
        return $str;
    }
}
