<?php

namespace Core\Helpers;

class URL
{
    public static function base(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            ? "https://"
            : "http://";

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host;
    }

    public static function to(string $path = ''): string
    {
        return rtrim(self::base(), '/') . '/' . ltrim($path, '/');
    }

    public static function asset(string $path): string
    {
        return self::to($path);
    }

    public static function redirect(string $path)
    {
        header("Location: " . self::to($path));
        exit;
    }
}
