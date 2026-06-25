<?php

namespace Core\Classes;

class Request
{
    protected array $routeParams = [];

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $uri = strtok($uri, '?');

        // Normalize both URI and APP_SUBDIR to prevent mismatch
        $uri = '/' . ltrim($uri, '/');
        $basePath = rtrim($_ENV['APP_BASE'] ?? '', '/');

        // If APP_SUBDIR is set and matches the beginning of the URI, strip it
        if (!empty($basePath) && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Ensure it always starts with a slash
        return '/' . trim($uri, '/');
    }

    public function json(): array
    {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true) ?? [];
        return $this->sanitizeArray($data);
    }

    public function setRouteParams(array $params): void
    {
        // Using your existing sanitizer for safety!
        $this->routeParams = $this->sanitizeArray($params);
    }

    // Add this method to retrieve a specific route parameter
    public function route(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public function header(string $key): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return isset($_SERVER[$headerKey]) ? $this->sanitizeString($_SERVER[$headerKey]) : null;
    }

    public function all(): array
    {
        return $this->sanitizeArray(array_merge($_GET, $_POST));
    }

    public function body(): array
    {
        if ($this->isJsonRequest()) {
            return $this->json();
        }

        return $this->sanitizeArray($_POST);
    }

    public function query(string $key, $default = null)
    {
        return isset($_GET[$key]) ? $this->sanitizeInput($_GET[$key]) : $default;
    }

    public function input(string $key, $default = null)
    {
        return isset($_POST[$key]) ? $this->sanitizeInput($_POST[$key]) : $default;
    }

    public function file(string $key): ?array
    {
        if (!isset($_FILES[$key])) {
            return null;
        }

        // Basic file info sanitization
        $file = $_FILES[$key];
        return [
            'name' => $this->sanitizeString($file['name']),
            'type' => $this->sanitizeString($file['type']),
            'tmp_name' => $file['tmp_name'], // Don't sanitize as it's system-generated
            'error' => (int)$file['error'],
            'size' => (int)$file['size']
        ];
    }

    public function files(): array
    {
        $sanitizedFiles = [];

        foreach ($_FILES as $key => $file) {
            // Handle multiple files under the same key
            if (is_array($file['name'])) {
                $fileCount = count($file['name']);
                $sanitizedFiles[$key] = [];

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($file['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

                    $sanitizedFiles[$key][] = [
                        'name' => $this->sanitizeString($file['name'][$i]),
                        'type' => $this->sanitizeString($file['type'][$i]),
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => (int)$file['error'][$i],
                        'size' => (int)$file['size'][$i]
                    ];
                }
            } else {
                if ($file['error'] === UPLOAD_ERR_NO_FILE) continue;

                $sanitizedFiles[$key] = [
                    'name' => $this->sanitizeString($file['name']),
                    'type' => $this->sanitizeString($file['type']),
                    'tmp_name' => $file['tmp_name'],
                    'error' => (int)$file['error'],
                    'size' => (int)$file['size']
                ];
            }
        }

        return $sanitizedFiles;
    }


    protected function isJsonRequest(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    protected function sanitizeInput($value)
    {
        if (is_array($value)) {
            return $this->sanitizeArray($value);
        }

        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        // if the value is number, int, boolean, return as it is
        if (is_int($value) || is_float($value) || is_bool($value) || is_null($value)) {
            return $value;
        }

        // if other type of data, destroy
        return '';
    }

    protected function sanitizeArray(array $array): array
    {
        $sanitized = [];
        foreach ($array as $key => $value) {
            $cleanKey = is_int($key) ? $key : $this->sanitizeString($key);
            $sanitized[$cleanKey] = $this->sanitizeInput($value);
        }
        return $sanitized;
    }

    protected function sanitizeString($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        // Strip tags and encode special characters
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Trim whitespace
        $value = trim($value);

        return $value;
    }
}
