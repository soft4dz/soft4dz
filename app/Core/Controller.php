<?php

namespace App\Core;

class Controller {
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile   = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $code = 200): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path, int $code = 302): never {
        $url = str_starts_with($path, 'http') ? $path : APP_URL . '/' . ltrim($path, '/');
        header("Location: $url", true, $code);
        exit;
    }

    protected function back(): never {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '');
    }

    protected function input(string $key, $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function validate(array $rules): array {
        $errors = [];
        foreach ($rules as $field => $ruleStr) {
            $value = $this->input($field);
            foreach (explode('|', $ruleStr) as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule), 2, null);
                match ($ruleName) {
                    'required' => (!$value && $value !== '0') && ($errors[$field][] = "Le champ $field est requis"),
                    'email'    => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) && ($errors[$field][] = "Email invalide"),
                    'min'      => (strlen($value ?? '') < (int)$param) && ($errors[$field][] = "Minimum $param caractères"),
                    'max'      => (strlen($value ?? '') > (int)$param) && ($errors[$field][] = "Maximum $param caractères"),
                    'numeric'  => ($value && !is_numeric($value)) && ($errors[$field][] = "Doit être un nombre"),
                    default    => null,
                };
            }
        }
        if ($errors) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->back();
        }
        return array_map(fn($k) => $this->input($k), array_keys($rules));
    }

    protected function abort(int $code = 404): never {
        http_response_code($code);
        $file = VIEWS_PATH . "/errors/$code.php";
        if (file_exists($file)) require $file;
        else echo "<h1>Error $code</h1>";
        exit;
    }

    protected function requireAuth(): void {
        if (!\App\Core\Auth::check()) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void {
        if (!\App\Core\Auth::check()) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/admin';
            $this->redirect('/admin/login');
        }
        if (!\App\Core\Auth::isAdmin()) {
            $this->abort(403);
        }
    }
}
