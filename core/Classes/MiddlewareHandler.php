<?php

namespace Core\Classes;

class MiddlewareHandler
{
    protected Request $request;
    protected Response $response;
    protected array $globalMiddleware = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // Preload global middleware if needed
        $this->globalMiddleware = [
            // 'SecurityHeaders', 'VerifyCsrfToken', etc.
        ];
    }

    public function getGlobalMiddleware(): array
    {
        $middlewares = [];
        foreach ($this->globalMiddleware as $middleware) {
            $middlewares[] = "App\\Middlewares\\$middleware";
        }
        return $middlewares;
    }

    public function addGlobal(string $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }
}
