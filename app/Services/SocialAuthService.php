<?php

namespace App\Services;

/**
 * Connexion sociale Google + Facebook (OAuth2 "Authorization Code") — sans dépendance Composer.
 * @see https://developers.google.com/identity/protocols/oauth2/web-server
 * @see https://developers.facebook.com/docs/facebook-login/guides/access-tokens/get-a-user-access-token
 */
final class SocialAuthService {
    public static function isConfigured(string $provider): bool {
        return match ($provider) {
            'google'   => GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '',
            'facebook' => FACEBOOK_APP_ID !== '' && FACEBOOK_APP_SECRET !== '',
            default    => false,
        };
    }

    public static function authUrl(string $provider, string $state): ?string {
        return match ($provider) {
            'google' => 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id'     => GOOGLE_CLIENT_ID,
                'redirect_uri'  => GOOGLE_REDIRECT_URI,
                'response_type' => 'code',
                'scope'         => 'openid email profile',
                'state'         => $state,
                'prompt'        => 'select_account',
            ]),
            'facebook' => 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query([
                'client_id'    => FACEBOOK_APP_ID,
                'redirect_uri' => FACEBOOK_REDIRECT_URI,
                'response_type' => 'code',
                'scope'        => 'email,public_profile',
                'state'        => $state,
            ]),
            default => null,
        };
    }

    /**
     * Échange le "code" contre un profil utilisateur normalisé.
     * @return array{id:string,email:?string,name:string}|null
     */
    public static function fetchProfile(string $provider, string $code): ?array {
        return match ($provider) {
            'google'   => self::fetchGoogleProfile($code),
            'facebook' => self::fetchFacebookProfile($code),
            default    => null,
        };
    }

    private static function fetchGoogleProfile(string $code): ?array {
        $token = self::httpPostForm('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);
        if (!$token || empty($token['access_token'])) return null;

        $info = self::httpGet('https://www.googleapis.com/oauth2/v3/userinfo', [
            'Authorization: Bearer ' . $token['access_token'],
        ]);
        if (!$info || empty($info['sub'])) return null;

        return [
            'id'    => (string) $info['sub'],
            'email' => $info['email'] ?? null,
            'name'  => $info['name'] ?? trim(($info['given_name'] ?? '') . ' ' . ($info['family_name'] ?? '')) ?: 'Utilisateur Google',
        ];
    }

    private static function fetchFacebookProfile(string $code): ?array {
        $token = self::httpGet('https://graph.facebook.com/v19.0/oauth/access_token', [], [
            'code'          => $code,
            'client_id'     => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri'  => FACEBOOK_REDIRECT_URI,
        ]);
        if (!$token || empty($token['access_token'])) return null;

        $info = self::httpGet('https://graph.facebook.com/me', [], [
            'fields'       => 'id,name,email',
            'access_token' => $token['access_token'],
        ]);
        if (!$info || empty($info['id'])) return null;

        return [
            'id'    => (string) $info['id'],
            'email' => $info['email'] ?? null,
            'name'  => $info['name'] ?? 'Utilisateur Facebook',
        ];
    }

    /** @param array<string, string> $query */
    private static function httpGet(string $url, array $headers = [], array $query = []): ?array {
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json'], $headers),
        ]);
        return self::decode($ch);
    }

    /** @param array<string, string> $fields */
    private static function httpPostForm(string $url, array $fields): ?array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        return self::decode($ch);
    }

    private static function decode(\CurlHandle $ch): ?array {
        $response = curl_exec($ch);
        $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $code < 200 || $code >= 300) return null;

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}
