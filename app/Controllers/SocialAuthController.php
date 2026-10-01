<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\SocialAuthService;

class SocialAuthController extends Controller {
    private const PROVIDERS = ['google', 'facebook'];

    public function redirectToProvider(string $provider): void {
        if (!in_array($provider, self::PROVIDERS, true) || !SocialAuthService::isConfigured($provider)) {
            setFlash('error', "La connexion via ce fournisseur n'est pas disponible pour le moment.");
            $this->redirect('/login');
        }

        $state = Auth::generateToken(32);
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $provider;

        $this->redirect(SocialAuthService::authUrl($provider, $state));
    }

    public function callback(string $provider): void {
        if (!in_array($provider, self::PROVIDERS, true)) {
            $this->abort(404);
        }

        if (!empty($this->input('error'))) {
            setFlash('info', "Connexion annulée.");
            $this->redirect('/login');
        }

        $state = $this->input('state', '');
        $code  = $this->input('code', '');
        $expectedState = $_SESSION['oauth_state'] ?? '';
        unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);

        if ($code === '' || $state === '' || !hash_equals($expectedState, $state)) {
            setFlash('error', "Requête de connexion invalide, veuillez réessayer.");
            $this->redirect('/login');
        }

        $profile = SocialAuthService::fetchProfile($provider, $code);
        if (!$profile || empty($profile['email'])) {
            setFlash('error', "Impossible de récupérer votre profil. Essayez avec votre email et mot de passe.");
            $this->redirect('/login');
        }

        $user = $this->findOrCreateUser($provider, $profile);
        if (!$user) {
            setFlash('error', "Ce compte est indisponible.");
            $this->redirect('/login');
        }

        Auth::login($user);
        $redirect = $_SESSION['intended'] ?? ($user['role'] === 'admin' ? '/admin' : '/dashboard');
        unset($_SESSION['intended']);
        $this->redirect($redirect);
    }

    /** @param array{id:string,email:?string,name:string} $profile */
    private function findOrCreateUser(string $provider, array $profile): array|false {
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT * FROM users WHERE oauth_provider = ? AND oauth_id = ? LIMIT 1",
            [$provider, $profile['id']]
        );
        if ($user) {
            if ($user['status'] !== 'active') return false;
            $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
            return $user;
        }

        $existing = $db->fetch("SELECT * FROM users WHERE email = ? LIMIT 1", [$profile['email']]);
        if ($existing) {
            if ($existing['status'] !== 'active') return false;
            $db->update('users', [
                'oauth_provider' => $provider,
                'oauth_id'       => $profile['id'],
                'last_login'     => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing['id']]);
            return $db->fetch("SELECT * FROM users WHERE id = ?", [$existing['id']]);
        }

        $userId = $db->insert('users', [
            'name'              => $profile['name'],
            'email'             => $profile['email'],
            'password_hash'     => null,
            'role'              => 'customer',
            'status'            => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'oauth_provider'    => $provider,
            'oauth_id'          => $profile['id'],
            'last_login'        => date('Y-m-d H:i:s'),
        ]);

        return $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    }
}
