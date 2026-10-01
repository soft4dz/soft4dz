<?php
/**
 * Réinitialise le mot de passe admin au mot de passe par défaut du schema.sql.
 * Changez-le immédiatement après connexion.
 * Usage : php database/fix_admin_password.php
 */
chdir(dirname(__DIR__));
require_once 'config/config.php';
$cfg = require 'config/database.php';

$email = 'admin@soft4dz.com';
$plain = 'ChangeMe@2026';
$hash  = password_hash($plain, PASSWORD_ARGON2ID);

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
    echo "Mot de passe admin réinitialisé. Connexion : {$email} / {$plain}\n";
} catch (PDOException $e) {
    echo 'ERREUR : ' . $e->getMessage() . "\n";
    exit(1);
}
