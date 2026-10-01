<?php

namespace App\Services;

/**
 * WhatsApp Cloud API (Meta) — send text + document.
 * Configure WHATSAPP_TOKEN + WHATSAPP_PHONE_NUMBER_ID in .env
 */
class WhatsAppService {
    public static function isConfigured(): bool {
        return WHATSAPP_TOKEN !== '' && WHATSAPP_PHONE_NUMBER_ID !== '';
    }

    public static function normalizePhone(string $phone): string {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        // Algeria local 0XXXXXXXXX → 213XXXXXXXXX
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '213' . substr($digits, 1);
        }
        return $digits;
    }

    public static function sendText(string $phone, string $message): bool {
        if (!self::isConfigured()) {
            return false;
        }
        $to = self::normalizePhone($phone);
        if ($to === '') {
            return false;
        }

        return self::apiRequest([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => true, 'body' => $message],
        ]);
    }

    /**
     * Upload local PDF then send as document message.
     */
    public static function sendDocument(string $phone, string $filePath, string $filename, string $caption = ''): bool {
        if (!self::isConfigured() || !is_file($filePath)) {
            return false;
        }
        $to = self::normalizePhone($phone);
        if ($to === '') {
            return false;
        }

        $mediaId = self::uploadMedia($filePath, 'application/pdf');
        if (!$mediaId) {
            // Fallback: text with public URL if upload fails
            return false;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'document',
            'document' => [
                'id' => $mediaId,
                'filename' => $filename,
            ],
        ];
        if ($caption !== '') {
            $payload['document']['caption'] = mb_substr($caption, 0, 1024);
        }

        return self::apiRequest($payload);
    }

    private static function uploadMedia(string $filePath, string $mime): ?string {
        $url = 'https://graph.facebook.com/' . WHATSAPP_API_VERSION . '/' . WHATSAPP_PHONE_NUMBER_ID . '/media';
        $cfile = new \CURLFile($filePath, $mime, basename($filePath));
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . WHATSAPP_TOKEN],
            CURLOPT_POSTFIELDS => [
                'messaging_product' => 'whatsapp',
                'type' => $mime,
                'file' => $cfile,
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300 || !$raw) {
            error_log('WhatsApp media upload failed: ' . (string) $raw);
            return null;
        }
        $json = json_decode($raw, true);
        return $json['id'] ?? null;
    }

    private static function apiRequest(array $payload): bool {
        $url = 'https://graph.facebook.com/' . WHATSAPP_API_VERSION . '/' . WHATSAPP_PHONE_NUMBER_ID . '/messages';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . WHATSAPP_TOKEN,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300) {
            error_log('WhatsApp send failed (' . $code . '): ' . (string) $raw);
            return false;
        }
        return true;
    }

    /** Deep link for manual send (fallback when API not configured). */
    public static function waMeLink(string $phone, string $text): string {
        $to = self::normalizePhone($phone);
        return 'https://wa.me/' . $to . '?text=' . rawurlencode($text);
    }
}
