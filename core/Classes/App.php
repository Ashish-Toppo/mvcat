<?php

namespace Core\Classes;

use Core\Classes\Router;
use Core\Classes\Request;
use Core\Classes\Response;
use Core\Classes\MiddlewareHandler;
use Core\Classes\Container;

class App
{
    protected Router $router;
    protected Request $request;
    protected Response $response;
    protected MiddlewareHandler $middleware;
    public Container $container;

    public function __construct()
    {
        $this->container = new Container();

        // Bind core instances to container
        $this->container->instance(self::class, $this);
        $this->container->instance(App::class, $this);

        $this->request = new Request();
        $this->container->instance(Request::class, $this->request);

        $this->response = new Response();
        $this->container->instance(Response::class, $this->response);

        $this->router = new Router($this->request, $this->response, $this->container);
        $this->container->instance(Router::class, $this->router);

        $this->middleware = new MiddlewareHandler($this->request, $this->response);
        $this->container->instance(MiddlewareHandler::class, $this->middleware);
    }

    public function registerProviders(array $providers): void
    {
        $instances = [];
        
        // 1. Instantiate and Register
        foreach ($providers as $providerClass) {
            $provider = new $providerClass($this);
            $provider->register();
            $instances[] = $provider;
        }

        // 2. Boot
        foreach ($instances as $provider) {
            $provider->boot();
        }
    }

    public function loadRoutes(string $routeDirectory): void
    {
        $routeFiles = glob($routeDirectory . '/*.php');
        foreach ($routeFiles as $file) {
            $app = $this; // Make $app available inside route file
            require $file;
        }
    }


    public function run(): void
    {
        $result = $this->router->resolve();

        if ($result instanceof \Closure) {
            echo $result(); // closure returning a string
        } elseif (is_string($result)) {
            echo $result;
        } else {
            echo $result;
        }
    }


    // Dependency injection helpers
    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getMiddlewareHandler(): MiddlewareHandler
    {
        return $this->middleware;
    }
}
