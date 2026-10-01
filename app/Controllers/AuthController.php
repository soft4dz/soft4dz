<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class AuthController extends Controller {
    public function loginForm(): void {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
        }
        $this->view('auth.login', ['adminLogin' => false], 'minimal');
    }

    public function adminLoginForm(): void {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
        }
        $this->view('auth.login', ['adminLogin' => true], 'minimal');
    }

    public function login(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) {
            setFlash('error', 'Token de sécurité invalide.');
            $this->back();
        }

        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');

        $user = Auth::attempt($email, $password);
        if (!$user) {
            $_SESSION['old_input'] = ['email' => $email];
            setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
        }

        if (($user['role'] ?? '') === 'admin') {
            setFlash('error', "Utilisez l'espace administrateur pour vous connecter.");
            $this->redirect('/admin/login');
        }

        Auth::login($user);
        $redirect = $_SESSION['intended'] ?? ($user['role'] === 'admin' ? '/admin' : '/dashboard');
        unset($_SESSION['intended']);
        $this->redirect($redirect);
    }

    public function adminLogin(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) {
            setFlash('error', 'Token de sécurité invalide.');
            $this->back();
        }

        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');

        $user = Auth::attempt($email, $password);
        if (!$user) {
            $_SESSION['old_input'] = ['email' => $email];
            setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/admin/login');
        }

        if (($user['role'] ?? '') !== 'admin') {
            setFlash('error', "Accès réservé aux administrateurs.");
            $this->redirect('/login');
        }

        Auth::login($user);
        $redirect = $_SESSION['intended'] ?? '/admin';
        unset($_SESSION['intended']);
        $this->redirect($redirect);
    }

    public function registerForm(): void {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
        }
        $this->view('auth.register', [], 'minimal');
    }

    public function register(): void {
        if (!Auth::verifyCsrf($this->input('_csrf', ''))) {
            $this->back();
        }

        $name     = trim($this->input('name', ''));
        $email    = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirmation', '');

        $errors = [];
        if (strlen($name) < 2)       $errors['name']     = "Nom trop court";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email invalide";
        if (strlen($password) < 8)   $errors['password'] = "Mot de passe trop court (min. 8 chars)";
        if ($password !== $confirm)  $errors['password'] = "Les mots de passe ne correspondent pas";

        $db = Database::getInstance();
        if ($db->fetch("SELECT id FROM users WHERE email = ?", [$email])) {
            $errors['email'] = "Cet email est déjà utilisé";
        }

        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = compact('name', 'email');
            $this->redirect('/register');
        }

        $userId = $db->insert('users', [
            'name'               => $name,
            'email'              => $email,
            'password_hash'      => Auth::hashPassword($password),
            'role'               => 'customer',
            'status'             => 'active',
            'email_verified_at'  => date('Y-m-d H:i:s'),
        ]);

        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        Auth::login($user);
        setFlash('success', "Bienvenue sur Soft4dz, $name !");
        $this->redirect('/dashboard');
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/');
    }

    public function forgotForm(): void {
        $this->view('auth.forgot', [], 'minimal');
    }

    public function forgot(): void {
        $email = trim($this->input('email', ''));
        $db    = Database::getInstance();
        $user  = $db->fetch("SELECT id, name FROM users WHERE email = ?", [$email]);

        if ($user) {
            $token = Auth::generateToken();
            $db->query("DELETE FROM password_resets WHERE email = ?", [$email]);
            $db->insert('password_resets', [
                'email'      => $email,
                'token'      => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ]);
            // TODO: send email with reset link
        }

        setFlash('success', "Si l'email existe, un lien de réinitialisation a été envoyé.");
        $this->redirect('/login');
    }

    public function resetForm(string $token): void {
        $this->view('auth.reset', ['token' => $token], 'minimal');
    }

    public function reset(): void {
        $token    = $this->input('token', '');
        $password = $this->input('password', '');
        $db       = Database::getInstance();

        $reset = $db->fetch(
            "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()",
            [hash('sha256', $token)]
        );

        if (!$reset) {
            setFlash('error', "Lien invalide ou expiré.");
            $this->redirect('/forgot-password');
        }

        $db->update('users', ['password_hash' => Auth::hashPassword($password)], 'email = ?', ['email' => $reset['email']]);
        $db->query("UPDATE password_resets SET used = 1 WHERE token = ?", [hash('sha256', $token)]);

        setFlash('success', "Mot de passe réinitialisé avec succès.");
        $this->redirect('/login');
    }
}
