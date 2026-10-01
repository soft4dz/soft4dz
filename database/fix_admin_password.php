<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Script CLI uniquement : jamais exécutable via le web
/**
 * Définit le mot de passe du compte admin.
 * Usage : php database/fix_admin_password.php [email] [mot_de_passe]
 *   - email par défaut : admin@soft4dz.com
 *   - sans mot de passe, un mot de passe aléatoire est généré et affiché une seule fois.
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';
$cfg = require 'config/database.php';

$email = $argv[1] ?? 'admin@soft4dz.com';
$plain = $argv[2] ?? rtrim(strtr(base64_encode(random_bytes(15)), '+/', '-_'), '=');
if (strlen($plain) < 12) {
    echo "Mot de passe trop court (12 caractères minimum).\n";
    exit(1);
}
$hash = password_hash($plain, PASSWORD_ARGON2ID);

try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}",
        $cfg['username'],
        $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $st = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ? AND role = ?');
    $st->execute([$hash, $email, 'admin']);
    if ($st->rowCount() === 0) {
        echo "Aucune ligne mise à jour : vérifiez que l'email admin est bien {$email}.\n";
        exit(1);
    }
    echo "Mot de passe admin défini. Connexion : {$email} / {$plain}\n";
    echo "Conservez-le dans un gestionnaire de mots de passe : il ne sera plus affiché.\n";
} catch (PDOException $e) {
    echo 'ERREUR : ' . $e->getMessage() . "\n";
    exit(1);
}
