<?php

namespace Core\Classes;

class View
{
    protected static array $shared = [];
    protected static array $namespaces = [];

    public static function addNamespace(string $namespace, string $path): void
    {
        self::$namespaces[$namespace] = rtrim($path, '/\\');
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = []): void
    {
        $viewPath = '';

        if (str_contains($view, '::')) {
            [$namespace, $viewName] = explode('::', $view, 2);
            if (!isset(self::$namespaces[$namespace])) {
                echo "View namespace '$namespace' not registered.";
                return;
            }
            $viewPath = self::$namespaces[$namespace] . '/' . str_replace('.', '/', $viewName) . '.php';
        } else {
            $viewPath = __DIR__ . '/../../app/Views/' . str_replace('.', '/', $view) . '.php';
        }

        if (!file_exists($viewPath)) {
            echo "View '$view' not found.";
            return;
        }

        // Extract shared and local data
        extract(self::$shared);
        extract($data);

        // Include the view file
        require $viewPath;
    }

    // Optional helper for one-time echo (auto-escaped)
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
