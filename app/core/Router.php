<?php
namespace App\Core;

class Router
{
    protected array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => '#^' . $pattern . '$#u',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri)
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                $response = call_user_func_array($route['handler'], $matches);

                return $this->sendResponse($response);
            }
        }

        http_response_code(404);
        return $this->sendResponse(view('errors/404'));
    }

    protected function sendResponse($response)
    {
        if ($response === null) {
            return null;
        }

        if (is_string($response)) {
            echo $response;
            return null;
        }

        if (is_array($response)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return null;
        }

        if (is_callable([$response, '__toString'])) {
            echo (string) $response;
            return null;
        }

        return $response;
    }
}
