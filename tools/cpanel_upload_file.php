<?php
/** Remplace un fichier sur cPanel via save_file_content */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web

$host = 'soft4dz.com';
$port = 2083;
$user = getenv('CPANEL_USER') ?: 'softdzco';
$pass = getenv('CPANEL_PASS') ?: '';
$localFile = $argv[1] ?? '';
$remoteDir = $argv[2] ?? '/public_html';
$remoteName = $argv[3] ?? basename($localFile);

if ($pass === '' || $localFile === '' || !is_readable($localFile)) {
    fwrite(STDERR, "Usage: CPANEL_PASS=... php tools/cpanel_upload_file.php <local> [remoteDir] [remoteName]\n");
    exit(1);
}

$cookie = sys_get_temp_dir() . '/cpanel_up_' . md5($user) . '.txt';
@unlink($cookie);

$ch = curl_init("https://{$host}:{$port}/login/?login_only=1");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['user' => $user, 'pass' => $pass]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 60,
]);
$login = json_decode((string) curl_exec($ch), true);
curl_close($ch);

if (($login['status'] ?? 0) != 1) {
    fwrite(STDERR, "Login failed\n");
    exit(1);
}

$token = rtrim((string) ($login['security_token'] ?? ''), '/');
$content = file_get_contents($localFile);

$url = "https://{$host}:{$port}{$token}/execute/Fileman/save_file_content";
$post = [
    'dir'          => $remoteDir,
    'file'         => $remoteName,
    'content'      => $content,
    'from_charset' => 'utf-8',
    'to_charset'   => 'utf-8',
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 120,
]);
$resp = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
@unlink($cookie);

if ($resp === false) {
    fwrite(STDERR, "cURL: $err\n");
    exit(1);
}

echo $resp . "\n";
$json = json_decode((string) $resp, true);
exit(($json['status'] ?? 0) == 1 ? 0 : 1);
