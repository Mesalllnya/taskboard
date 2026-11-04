<?php
declare(strict_types=1);

/**
 * core/Router.php
 *
 * Minimal front-router.
 * Routes are registered as:   GET  /tasks         → TaskController@index
 * The URL is matched against  /controller/action/param
 */
class Router
{
    /** @var array<string,array{controller:string,action:string}> */
    private array $routes = [];

    // ── Registration ───────────────────────────────────────────

    public function get(string $pattern, string $controllerAction): void
    {
        $this->add('GET', $pattern, $controllerAction);
    }

    public function post(string $pattern, string $controllerAction): void
    {
        $this->add('POST', $pattern, $controllerAction);
    }

    public function put(string $pattern, string $controllerAction): void
    {
        $this->add('PUT', $pattern, $controllerAction);
    }

    public function delete(string $pattern, string $controllerAction): void
    {
        $this->add('DELETE', $pattern, $controllerAction);
    }

    // ── Dispatch ───────────────────────────────────────────────

    /**
     * Match the current request URI against registered routes and dispatch.
     * Params extracted from {placeholder} segments are passed as positional
     * arguments to the controller method.
     */
    public function dispatch(string $uri, string $method): void
    {
        // Strip query string
        $uri = strtok($uri, '?') ?: '/';
        // Normalise: remove trailing slash (except root)
        $uri = ($uri !== '/') ? rtrim($uri, '/') : $uri;

        // Remove BASE_PATH prefix so routes work in a sub-directory
        $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        foreach ($this->routes as $key => [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method && !($method === 'POST' && $routeMethod === 'PUT')
                                        && !($method === 'POST' && $routeMethod === 'DELETE')) {
                // Support _method override for HTML forms
                $override = strtoupper($_POST['_method'] ?? '');
                if ($routeMethod !== $override) {
                    continue;
                }
            }
            if ($routeMethod !== $method) {
                $override = strtoupper($_POST['_method'] ?? $_GET['_method'] ?? '');
                if ($routeMethod !== $override) continue;
            }

            $params = $this->match($pattern, $uri);
            if ($params !== null) {
                $this->invoke($handler, $params);
                return;
            }
        }

        // 404
        $this->invoke('ErrorController@notFound', []);
    }

    // ── Private helpers ────────────────────────────────────────

    private function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = [$method, $pattern, $handler];
    }

    /**
     * Match a URI against a pattern like /tasks/{id}/edit.
     * Returns an associative array of captured params, or null on no match.
     *
     * @return array<string,string>|null
     */
    private function match(string $pattern, string $uri): ?array
    {
        // Convert {param} → named capture group
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Return only named captures
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        return null;
    }

    /**
     * Instantiate controller and call the action method.
     * @param array<string,string> $params
     */
    private function invoke(string $handler, array $params): void
    {
        [$controllerName, $actionName] = explode('@', $handler, 2);

        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller not found: {$controllerName}";
            return;
        }
        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Class not found: {$controllerName}";
            return;
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $actionName)) {
            http_response_code(500);
            echo "Method not found: {$controllerName}::{$actionName}";
            return;
        }

        // Pass URL params as positional args
        call_user_func_array([$controller, $actionName], array_values($params));
    }
}
