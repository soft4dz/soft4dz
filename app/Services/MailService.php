<?php

namespace App\Services;

/**
 * Lightweight SMTP mailer with attachment support (no Composer).
 */
class MailService {
    public static function isConfigured(): bool {
        return MAIL_HOST !== '' && MAIL_FROM_ADDRESS !== '';
    }

    /**
     * @param array<int, array{path:string,name?:string,mime?:string}> $attachments
     */
    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody = '',
        array $attachments = []
    ): bool {
        if ($to === '') {
            return false;
        }

        $fromEmail = MAIL_FROM_ADDRESS;
        $fromName  = MAIL_FROM_NAME;
        $boundary  = 'b_' . bin2hex(random_bytes(12));
        $mixed     = 'm_' . bin2hex(random_bytes(12));

        $headers = [];
        $headers[] = 'From: ' . self::encodeAddress($fromName, $fromEmail);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'X-Mailer: Soft4dz';

        if ($textBody === '') {
            $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        }

        if (empty($attachments)) {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
            $body = self::altPart($boundary, $textBody, $htmlBody);
        } else {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$mixed}\"";
            $body  = "--{$mixed}\r\n";
            $body .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";
            $body .= self::altPart($boundary, $textBody, $htmlBody) . "\r\n";
            foreach ($attachments as $att) {
                $path = $att['path'];
                if (!is_file($path)) {
                    continue;
                }
                $name = $att['name'] ?? basename($path);
                $mime = $att['mime'] ?? 'application/octet-stream';
                $data = chunk_split(base64_encode((string) file_get_contents($path)));
                $body .= "--{$mixed}\r\n";
                $body .= "Content-Type: {$mime}; name=\"{$name}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$name}\"\r\n\r\n";
                $body .= $data . "\r\n";
            }
            $body .= "--{$mixed}--\r\n";
        }

        if (MAIL_HOST !== '' && MAIL_USERNAME !== '') {
            return self::smtpSend($to, $subject, $body, $headers);
        }

        $headerStr = implode("\r\n", $headers);
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headerStr);
    }

    private static function altPart(string $boundary, string $text, string $html): string {
        $out  = "--{$boundary}\r\n";
        $out .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $out .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $out .= chunk_split(base64_encode($text)) . "\r\n";
        $out .= "--{$boundary}\r\n";
        $out .= "Content-Type: text/html; charset=UTF-8\r\n";
        $out .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $out .= chunk_split(base64_encode($html)) . "\r\n";
        $out .= "--{$boundary}--";
        return $out;
    }

    private static function encodeAddress(string $name, string $email): string {
        if ($name === '') {
            return $email;
        }
        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $email . '>';
    }

    private static function smtpSend(string $to, string $subject, string $body, array $headers): bool {
        $host = MAIL_HOST;
        $port = MAIL_PORT;
        $user = MAIL_USERNAME;
        $pass = MAIL_PASSWORD;
        $secure = MAIL_ENCRYPTION; // tls|ssl|''

        $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
        $fp = @fsockopen($remote, $port, $errno, $errstr, 20);
        if (!$fp) {
            error_log("MailService SMTP connect failed: {$errstr} ({$errno})");
            return false;
        }
        stream_set_timeout($fp, 20);

        try {
            self::smtpExpect($fp, 220);
            self::smtpCmd($fp, 'EHLO soft4dz.local', 250);

            if ($secure === 'tls') {
                self::smtpCmd($fp, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('STARTTLS failed');
                }
                self::smtpCmd($fp, 'EHLO soft4dz.local', 250);
            }

            if ($user !== '') {
                self::smtpCmd($fp, 'AUTH LOGIN', 334);
                self::smtpCmd($fp, base64_encode($user), 334);
                self::smtpCmd($fp, base64_encode($pass), 235);
            }

            self::smtpCmd($fp, 'MAIL FROM:<' . MAIL_FROM_ADDRESS . '>', 250);
            self::smtpCmd($fp, 'RCPT TO:<' . $to . '>', 250);
            self::smtpCmd($fp, 'DATA', 354);

            $msg  = 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
            $msg .= 'To: <' . $to . ">\r\n";
            $msg .= implode("\r\n", $headers) . "\r\n\r\n";
            $msg .= preg_replace('/^\./m', '..', $body);
            $msg .= "\r\n.\r\n";
            fwrite($fp, $msg);
            self::smtpExpect($fp, 250);
            self::smtpCmd($fp, 'QUIT', 221);
            fclose($fp);
            return true;
        } catch (\Throwable $e) {
            error_log('MailService SMTP error: ' . $e->getMessage());
            fclose($fp);
            return false;
        }
    }

    private static function smtpCmd($fp, string $cmd, int $expect): void {
        fwrite($fp, $cmd . "\r\n");
        self::smtpExpect($fp, $expect);
    }

    private static function smtpExpect($fp, int $code): void {
        $resp = '';
        while (($line = fgets($fp, 512)) !== false) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        if (!str_starts_with($resp, (string) $code)) {
            throw new \RuntimeException("SMTP expected {$code}, got: " . trim($resp));
        }
    }
}
