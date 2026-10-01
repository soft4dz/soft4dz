<?php

namespace App\Services;

/**
 * Chargily Pay v2 (API REST) — sans dépendance Composer.
 * @see https://dev.chargily.com/pay-v2/
 */
final class ChargilyPayService {
    public static function isConfigured(): bool {
        return CHARGILY_SECRET_KEY !== '';
    }

    public static function apiBase(): string {
        return CHARGILY_MODE === 'live'
            ? 'https://pay.chargily.net/api/v2'
            : 'https://pay.chargily.net/test/api/v2';
    }

    /**
     * @param array<string, string> $metadata
     * @return array<string, mixed>|null Réponse JSON décodée ou null en cas d’erreur.
     */
    public static function createCheckout(
        int $amountDzd,
        string $successUrl,
        string $failureUrl,
        string $webhookUrl,
        array $metadata,
        string $description = ''
    ): ?array {
        if (!self::isConfigured() || $amountDzd < 1) {
            return null;
        }

        $payload = [
            'amount'       => $amountDzd,
            'currency'     => 'dzd',
            'success_url'  => $successUrl,
            'failure_url'  => $failureUrl,
            'webhook_endpoint' => $webhookUrl,
            'locale'       => 'fr',
        ];
        if ($description !== '') {
            $payload['description'] = $description;
        }
        if ($metadata !== []) {
            $payload['metadata'] = $metadata;
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            return null;
        }

        $ch = curl_init(self::apiBase() . '/checkouts');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . CHARGILY_SECRET_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $code < 200 || $code >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    public static function verifyWebhookSignature(string $rawBody, ?string $signature): bool {
        if (!self::isConfigured() || $signature === null || $signature === '') {
            return false;
        }
        $expected = hash_hmac('sha256', $rawBody, CHARGILY_SECRET_KEY);
        return hash_equals($expected, $signature);
    }
}
