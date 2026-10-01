<?php

namespace App\Core;

class Auth {
    /** Connecté ET compte toujours actif (un compte banni ou désactivé perd sa session). */
    public static function check(): bool {
        return self::user() !== null;
    }

    public static function user(): ?array {
        if (empty($_SESSION['user_id'])) return null;
        static $cache = [];
        $id = (int) $_SESSION['user_id'];
        if (!array_key_exists($id, $cache)) {
            $cache[$id] = Database::getInstance()->fetch(
                "SELECT id, name, email, role, avatar FROM users WHERE id = ? AND status = 'active'",
                [$id]
            ) ?: null;
        }
        if ($cache[$id] === null) {
            unset($_SESSION['user_id'], $_SESSION['user_role']);
        }
        return $cache[$id];
    }

    public static function id(): ?int {
        return self::check() ? (int) $_SESSION['user_id'] : null;
    }

    public static function role(): ?string {
        return self::user()['role'] ?? null;
    }

    public static function isAdmin(): bool {
        return self::role() === 'admin';
    }

    public static function isVendor(): bool {
        return in_array(self::role(), ['vendor', 'admin']);
    }

    public static function login(array $user): void {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['login_time'] = time();
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function attempt(string $email, string $password): array|false {
        $db   = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [$email]);
        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) return false;
        if ($user['status'] !== 'active') return false;
        $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
        return $user;
    }

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function generateToken(int $length = 64): string {
        return bin2hex(random_bytes($length / 2));
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken(32);
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
