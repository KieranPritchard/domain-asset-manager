<?php 
    namespace Core;

    class Router
    {
        protected array $routes = [];

        // Method to get the uri and action
        public function get(string $uri, string $controllerAction):void
        {
            $this->routes["GET"][$uri] = $controllerAction;
        }

        // Method to dispatch uri
        public function dispatch(string $uri): void
        {
            // Parses the url and forwards to the handler
            $uri = parse_url($uri, PHP_URL_PATH);
            $method = $_SERVER['REQUEST_METHOD'];
            $handler = $this->routes[$method][$uri] ?? null;

            // Checks if there is a handler
            if (!$handler) {
                http_response_code(404);
                exit('Not Found');
            }

            // Runs the controller
            [$controller, $action] = explode('@', $handler);
            $controller = "App\\Controllers\\$controller";
            (new $controller())->$action();
        }
    }
?>