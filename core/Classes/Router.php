<?php

namespace Core\Classes;

class Router
{
    protected array $routes = [];
    protected Request $request;
    protected Response $response;
    protected string $baseMiddleware = '';
    private ?string $currentGroupPrefix = '';
    private array $currentGroupMiddleware = [];
    protected Container $container;


    public function __construct(Request $request, Response $response, Container $container)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->container = $container;
    }

    // Load route with optional middleware prefix
    public function group(string $prefix, array $middlewares, \Closure $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix ?? '';
        $previousMiddleware = $this->currentGroupMiddleware ?? [];

        $this->currentGroupPrefix = $previousPrefix . rtrim($prefix, '/');
        $this->currentGroupMiddleware = array_merge($previousMiddleware, $middlewares);

        $callback($this); // register all routes inside the group

        // Restore previous state
        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;
    }


    private function add(string $method, string $path, $callback, array $middleware = []): void
    {
        $prefix = $this->currentGroupPrefix ?? '';
        $path = $prefix . $path;

        // Normalize slashes
        $path = trim($path, '/');
        $path = $path === '' ? '/' : $path;

        $middleware = array_merge($this->currentGroupMiddleware ?? [], $middleware);

        $this->routes[strtoupper($method)][$path] = [
            'callback' => $callback,
            'middleware' => $middleware
        ];
    }


    public function get(string $path, callable|array $callback, array $middleware = []): void
    {
        $this->add('GET', $path, $callback, $middleware);
    }

    public function post(string $path, callable|array $callback, array $middleware = []): void
    {
        $this->add('POST', $path, $callback, $middleware);
    }
    
    public function delete(string $path, callable|array $callback, array $middleware = []): void
    {
        $this->add('DELETE', $path, $callback, $middleware);
    }

    public function resolve(): mixed
    {
        $method = $this->request->method();
        $uri = trim($this->request->uri(), '/');
        $uri = $uri === '' ? '/' : $uri;

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            // 1. Extract parameter names (e.g., gets 'id' from '{id}')
            preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $route, $paramNames);
            $paramNames = $paramNames[1];

            $routePattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^\/]+)', $route);
            $routeRegex   = '#^' . $routePattern . '$#';

            // echo "<pre>"; print_r($this->routes);

            if (preg_match($routeRegex, $uri, $matches)) {
                array_shift($matches); // Remove full match

                // 2. Combine parameter names with the matched values
                $routeParams = [];
                foreach ($paramNames as $index => $name) {
                    $routeParams[$name] = $matches[$index] ?? null;
                }

                // 3. Inject them into the Request object BEFORE middlewares run
                $this->request->setRouteParams($routeParams);

                // Combine global and route middlewares
                $globalMiddlewares = $this->container->get(MiddlewareHandler::class)->getGlobalMiddleware();
                $routeMiddlewares = array_map(function($mw) {
                    return "App\\Middlewares\\$mw";
                }, $handler['middleware']);
                
                $allMiddlewares = array_merge($globalMiddlewares, $routeMiddlewares);

                // Run Pipeline
                $pipeline = new Pipeline($this->container);
                
                $destination = function ($req) use ($handler, $routeParams) {
                    $callback = $handler['callback'];
                    if (is_array($callback)) {
                        [$class, $method] = $callback;
                        $class = str_replace('/', '\\', $class);
                        $class = "App\\Controllers\\$class";
                        if (class_exists($class)) {
                            $controller = $this->container->get($class);
                            return $this->container->call([$controller, $method], array_merge(['request' => $req, 'response' => $this->response], $routeParams));
                        }
                    }

                    if (is_callable($callback)) {
                        return $this->container->call($callback, array_merge(['request' => $req, 'response' => $this->response], $routeParams));
                    }
                    
                    return $this->response->status(500)->send("Invalid route handler.");
                };

                return $pipeline->send($this->request)
                                ->through($allMiddlewares)
                                ->then($destination);
            }
        }

        return $this->response->status(404)->send("Route not found.");
    }
}
