<?php

namespace Core\Helpers;

class Security
{
    public static function randomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::randomString(32);
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf($token): bool
    {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
