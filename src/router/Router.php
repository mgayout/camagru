<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = [
			'handler' => $handler,
			'auth' => null,
		];
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = [
			'handler' => $handler,
			'auth' => null,
		];
    }

	public function auth(string $path, callable $handler): void
	{
		$this->routes['GET'][$path] = [
			'handler' => $handler,
			'auth' => true,
		];
	}

	public function guest(string $path, callable $handler): void
	{
		$this->routes['GET'][$path] = [
			'handler' => $handler,
			'auth' => false,
		];
	}

    public function dispatch(string $method, string $path): void
    {
		if (!isset($this->routes[$method][$path])) {
			http_response_code(404);
			echo '404 - Page non trouvée';
			return;
		}

		$route = $this->routes[$method][$path];

		$isAuthenticated = isset($_SESSION['user_id']);

		if ($route['auth'] === true && !$isAuthenticated) {
			header('Location: /');
			exit;
		}

        if ($route['auth'] === false && $isAuthenticated) {
			header('Location: /camagru');
			exit;
		}

		call_user_func($route['handler']);
    }
}