<?php
/**
 * Crée la base MySQL Soft4dz sur cPanel (UAPI) et importe schema.sql.
 * Usage: php tools/cpanel_setup_db.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web

$cpanelHost = 'soft4dz.com';
$cpanelPort = 2083;
$cpanelUser = getenv('CPANEL_USER') ?: 'softdzco';
$cpanelPass = getenv('CPANEL_PASS') ?: '';
$dbShortName = 'soft4dz';
$dbUserShort = 'soft4dz';

if ($cpanelPass === '') {
    fwrite(STDERR, "CPANEL_PASS manquant.\n");
    exit(1);
}

$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cpanel_cookies_' . md5($cpanelUser) . '.txt';
@unlink($cookieFile);

function cpanelRequest(string $url, string $cookieFile, array $post = null): array
{
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FOLLOWLOCATION => true,
    ];
    if ($post !== null) {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($post);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException("cURL: $err");
    }

    $json = json_decode($body, true);
    return ['code' => $code, 'body' => $body, 'json' => is_array($json) ? $json : null];
}

function uapi(string $host, int $port, string $token, string $cookieFile, string $module, string $func, array $params = []): array
{
    $qs = $params ? '?' . http_build_query($params) : '';
    $url = "https://{$host}:{$port}{$token}/execute/{$module}/{$func}{$qs}";
    return cpanelRequest($url, $cookieFile);
}

try {
    echo "Connexion cPanel...\n";
    $login = cpanelRequest(
        "https://{$cpanelHost}:{$cpanelPort}/login/?login_only=1",
        $cookieFile,
        ['user' => $cpanelUser, 'pass' => $cpanelPass]
    );

    $loginJson = $login['json'];
    if (!$loginJson || ($loginJson['status'] ?? 0) != 1) {
        throw new RuntimeException('Échec login cPanel: ' . ($login['body'] ?: 'réponse vide'));
    }

    $token = rtrim((string) ($loginJson['security_token'] ?? ''), '/');
    if ($token === '') {
        throw new RuntimeException('Token de sécurité cPanel absent.');
    }
    echo "Login OK.\n";

    // Base existante ?
    $listDb = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'list_databases');
    $existingDbs = [];
    if (($listDb['json']['status'] ?? 0) == 1) {
        foreach ($listDb['json']['data'] ?? [] as $row) {
            $existingDbs[] = $row['database'] ?? $row['name'] ?? '';
        }
    }
    $fullDbName = $cpanelUser . '_' . $dbShortName;

    if (!in_array($fullDbName, $existingDbs, true)) {
        echo "Création base {$fullDbName}...\n";
        $createDb = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'create_database', ['name' => $dbShortName]);
        if (($createDb['json']['status'] ?? 0) != 1) {
            $err = $createDb['json']['errors'][0] ?? $createDb['body'];
            throw new RuntimeException("create_database: $err");
        }
        echo "Base créée.\n";
    } else {
        echo "Base {$fullDbName} existe déjà.\n";
    }

    $dbPassword = bin2hex(random_bytes(12)) . 'A1!';

    $listUsers = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'list_users');
    $existingUsers = [];
    if (($listUsers['json']['status'] ?? 0) == 1) {
        foreach ($listUsers['json']['data'] ?? [] as $row) {
            $existingUsers[] = $row['user'] ?? $row['username'] ?? '';
        }
    }
    $fullUserName = $cpanelUser . '_' . $dbUserShort;

    if (!in_array($fullUserName, $existingUsers, true)) {
        echo "Création utilisateur {$fullUserName}...\n";
        $createUser = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'create_user', [
            'name'     => $dbUserShort,
            'password' => $dbPassword,
        ]);
        if (($createUser['json']['status'] ?? 0) != 1) {
            $err = $createUser['json']['errors'][0] ?? $createUser['body'];
            throw new RuntimeException("create_user: $err");
        }
        echo "Utilisateur créé.\n";
    } else {
        echo "Utilisateur {$fullUserName} existe — mise à jour du mot de passe...\n";
        $setPass = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'set_password', [
            'user'     => $dbUserShort,
            'password' => $dbPassword,
        ]);
        if (($setPass['json']['status'] ?? 0) != 1) {
            $err = $setPass['json']['errors'][0] ?? $setPass['body'];
            throw new RuntimeException("set_password: $err");
        }
    }

    echo "Attribution des privilèges...\n";
    $priv = uapi($cpanelHost, $cpanelPort, $token, $cookieFile, 'Mysql', 'set_privileges_on_database', [
        'user'       => $dbUserShort,
        'database'   => $dbShortName,
        'privileges' => 'ALL PRIVILEGES',
    ]);
    if (($priv['json']['status'] ?? 0) != 1) {
        $err = $priv['json']['errors'][0] ?? $priv['body'];
        throw new RuntimeException("set_privileges: $err");
    }
    echo "Privilèges OK.\n";

    // Import schema via MySQL distant (si autorisé)
    echo "Import schema.sql...\n";
    $schemaPath = dirname(__DIR__) . '/database/schema.sql';
    $sql = file_get_contents($schemaPath);
    $sql = preg_replace('/^CREATE DATABASE.*?;/im', '', $sql);
    $sql = preg_replace('/^USE.*?;/im', '', $sql);

    $imported = false;
    $remoteHosts = ['soft4dz.com', 'localhost'];
    foreach ($remoteHosts as $mysqlHost) {
        try {
            $pdo = new PDO(
                "mysql:host={$mysqlHost};port=3306;dbname={$fullDbName};charset=utf8mb4",
                $fullUserName,
                $dbPassword,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 15]
            );
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $ok = 0;
            foreach ($statements as $stmt) {
                if ($stmt === '' || str_starts_with($stmt, '--')) {
                    continue;
                }
                try {
                    $pdo->exec($stmt);
                    $ok++;
                } catch (PDOException $e) {
                    if (!str_contains($e->getMessage(), 'already exists') && !str_contains($e->getMessage(), 'Duplicate')) {
                        throw $e;
                    }
                }
            }
            echo "Import OK via {$mysqlHost} ({$ok} requêtes).\n";
            $imported = true;
            break;
        } catch (PDOException $e) {
            echo "MySQL {$mysqlHost}: " . $e->getMessage() . "\n";
        }
    }

    // Sauvegarde config prod locale
    $envProd = dirname(__DIR__) . '/.env.production';
    $envContent = <<<ENV
APP_NAME=Soft4dz
APP_ENV=production
APP_URL=https://soft4dz.com
APP_SECRET=CHANGE_ME_RANDOM_SECRET

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE={$fullDbName}
DB_USERNAME={$fullUserName}
DB_PASSWORD={$dbPassword}

SESSION_LIFETIME=7200
UPLOAD_PATH=uploads/
ENV;
    file_put_contents($envProd, $envContent);
    echo "Config sauvegardée dans .env.production\n";

    if (!$imported) {
        echo "\n⚠ Import distant impossible — importez database/schema.sql via phpMyAdmin.\n";
    }

    $admin = null;
    if ($imported) {
        try {
            $pdo = new PDO(
                "mysql:host=soft4dz.com;port=3306;dbname={$fullDbName};charset=utf8mb4",
                $fullUserName,
                $dbPassword,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $admin = $pdo->query("SELECT email FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
        } catch (Throwable) {
        }
    }

    echo "\n=== RÉSUMÉ ===\n";
    echo "Base     : {$fullDbName}\n";
    echo "User     : {$fullUserName}\n";
    echo "Password : {$dbPassword}\n";
    echo "Host     : localhost (sur le serveur)\n";
    if ($admin) {
        echo "Admin    : {$admin} — mot de passe par défaut du schema.sql, à changer immédiatement\n";
    }
    echo "Import   : " . ($imported ? 'OK' : 'MANUEL requis (phpMyAdmin)') . "\n";

} catch (Throwable $e) {
    fwrite(STDERR, 'ERREUR: ' . $e->getMessage() . "\n");
    exit(1);
} finally {
    @unlink($cookieFile);
}
