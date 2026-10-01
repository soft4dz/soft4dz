<?php
require_once __DIR__ . '/config/config.php';

/*
 * Installateur web — désactivé par défaut.
 * Pour l'utiliser : définir SETUP_TOKEN (valeur aléatoire) dans .env puis ouvrir setup.php?token=VALEUR.
 * Il se verrouille dès que la table `users` existe. Préférez la CLI : php database/install.php
 */
function setupIsLocked(): bool {
    try {
        $cfg = require __DIR__ . '/config/database.php';
        $pdo = new PDO(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
            $cfg['username'], $cfg['password'], $cfg['options']
        );
        return $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn() !== false;
    } catch (PDOException) {
        return false; // base absente : installation possible
    }
}

$setupToken = (string) env('SETUP_TOKEN', '');
$givenToken = (string) ($_GET['token'] ?? '');
if ($setupToken === '' || strlen($setupToken) < 16 || !hash_equals($setupToken, $givenToken) || setupIsLocked()) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Soft4dz — Installation</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Inter,sans-serif;background:#080811;color:#f0f0ff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
  .card{background:#10101e;border:1px solid rgba(255,255,255,0.07);border-radius:20px;padding:2.5rem;max-width:600px;width:100%}
  h1{font-size:2rem;font-weight:800;margin-bottom:0.5rem;background:linear-gradient(135deg,#7C3AED,#06B6D4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  .step{padding:1rem;border-radius:12px;margin-bottom:0.75rem;font-size:0.875rem;display:flex;align-items:center;gap:0.75rem}
  .step.ok{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);color:#34d399}
  .step.err{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#f87171}
  .step.info{background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);color:#60a5fa}
  pre{background:#1c1c32;border:1px solid rgba(255,255,255,0.07);border-radius:8px;padding:1rem;font-size:0.78rem;overflow:auto;margin:1rem 0;color:#9090b0}
  a.btn{display:inline-block;background:linear-gradient(135deg,#7C3AED,#06B6D4);color:white;padding:0.75rem 1.5rem;border-radius:12px;text-decoration:none;font-weight:700;margin-top:1.5rem}
</style>
</head>
<body>
<div class="card">
  <h1>⚡ Soft4dz Setup</h1>
  <p style="color:#9090b0;margin-bottom:2rem">Installation de la base de données et vérification de l'environnement.</p>

<?php
$steps = [];
$adminPassword = null;
$hasErrors = false;

// PHP version check
if (PHP_VERSION_ID >= 80200) {
    $steps[] = ['ok', '✓ PHP ' . PHP_VERSION . ' détecté'];
} else {
    $steps[] = ['err', '✗ PHP 8.2+ requis. Version actuelle : ' . PHP_VERSION];
    $hasErrors = true;
}

// Extensions
foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'] as $ext) {
    if (extension_loaded($ext)) {
        $steps[] = ['ok', "✓ Extension $ext disponible"];
    } else {
        $steps[] = ['err', "✗ Extension $ext manquante"];
        $hasErrors = true;
    }
}

// Database connection
try {
    $cfg = require __DIR__ . '/config/database.php';
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};charset={$cfg['charset']}",
        $cfg['username'], $cfg['password'], $cfg['options']
    );

    // Create DB if needed
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$cfg['dbname']}`");
    $steps[] = ['ok', "✓ Base de données '{$cfg['dbname']}' connectée"];

    // Run schema
    if (isset($_GET['install'])) {
        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        // Remove CREATE DATABASE / USE statements (already done)
        $sql = preg_replace('/^CREATE DATABASE.*?;/im', '', $sql);
        $sql = preg_replace('/^USE.*?;/im', '', $sql);

        // Split and execute
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $ok = 0; $fail = 0;
        foreach ($statements as $stmt) {
            if (!$stmt) continue;
            try {
                $pdo->exec($stmt);
                $ok++;
            } catch (PDOException $e) {
                if (!str_contains($e->getMessage(), 'already exists') && !str_contains($e->getMessage(), 'Duplicate')) {
                    $fail++;
                }
            }
        }
        $steps[] = ['ok', "✓ Schéma exécuté : $ok requêtes OK" . ($fail > 0 ? ", $fail avertissements" : '')];

        // Admin : mot de passe aléatoire, affiché une seule fois
        $admin = $pdo->query("SELECT id, password_hash FROM users WHERE role='admin' LIMIT 1")->fetch();
        if ($admin && empty($admin['password_hash'])) {
            $adminPassword = rtrim(strtr(base64_encode(random_bytes(15)), '+/', '-_'), '=');
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($adminPassword, PASSWORD_ARGON2ID), $admin['id']]);
            $steps[] = ['ok', '✓ Compte admin créé (admin@soft4dz.com)'];
        } elseif (!$admin) {
            $steps[] = ['info', 'ℹ Créez un compte admin : php database/fix_admin_password.php'];
        }
    }

} catch (PDOException $e) {
    $steps[] = ['err', '✗ Erreur de connexion : ' . $e->getMessage()];
    $hasErrors = true;
}

// Directories
foreach (['uploads', 'uploads/products', 'uploads/proofs'] as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) mkdir($path, 0755, true);
    $steps[] = is_writable($path)
        ? ['ok', "✓ Dossier /$dir accessible en écriture"]
        : ['err', "✗ Dossier /$dir non accessible"];
}

foreach ($steps as $step):
?>
<div class="step <?= $step[0] ?>"><?= $step[1] ?></div>
<?php endforeach; ?>

<?php if (!isset($_GET['install']) && !$hasErrors): ?>
<a href="?token=<?= htmlspecialchars(rawurlencode($givenToken)) ?>&amp;install=1" class="btn">Lancer l'installation de la base de données →</a>
<?php elseif (isset($_GET['install'])): ?>
<div style="margin-top:1.5rem;padding:1.25rem;background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.2);border-radius:12px">
  <div style="font-weight:700;margin-bottom:0.75rem;color:#9f67f5">🎉 Installation terminée !</div>
  <?php if ($adminPassword !== null): ?>
  <div style="font-size:0.875rem;color:#9090b0;margin-bottom:0.5rem">Identifiants admin :</div>
  <code style="display:block;padding:0.75rem;background:#1c1c32;border-radius:8px;font-size:0.85rem">
    Email : admin@soft4dz.com<br>
    Mot de passe : <?= htmlspecialchars($adminPassword) ?>
  </code>
  <div style="font-size:0.8rem;color:#f59e0b;margin-top:0.75rem">⚠ Notez ce mot de passe maintenant : il ne sera plus jamais affiché.</div>
  <?php endif; ?>
  <div style="display:flex;gap:0.75rem;margin-top:1rem">
    <a href="<?= APP_URL ?>" class="btn" style="font-size:0.875rem">Voir le site</a>
    <a href="<?= APP_URL ?>/admin/login" class="btn" style="font-size:0.875rem">Connexion admin</a>
  </div>
</div>
<div style="margin-top:1rem;padding:0.875rem;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:8px;font-size:0.78rem;color:#f87171">
  ⚠ Supprimez le fichier <strong>setup.php</strong> et la variable <strong>SETUP_TOKEN</strong> du serveur.
</div>
<?php endif; ?>

</div>
</body>
</html>
