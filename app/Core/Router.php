<?php

namespace App\Core;

class Router {
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middleware): void {
        $pattern = preg_replace('/\{([a-z_]+)\}/', '([^/]+)', $path);
        $pattern = '@^' . $pattern . '$@i';
        $this->routes[] = compact('method', 'path', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(string $uri, string $method): void {
        // Strip base path
        $base = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        $uri  = '/' . ltrim(substr($uri, strlen($base)), '/');
        $uri  = strtok($uri, '?') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            array_shift($matches);

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                (new $mw())->handle();
            }

            // Dispatch handler
            if (is_callable($route['handler'])) {
                call_user_func_array($route['handler'], $matches);
            } else {
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...$matches);
            }
            return;
        }

        // 404
        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }
}
