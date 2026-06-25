<?php

namespace Core\Helpers;

class Debug
{
    public static function dump(...$vars)
    {
        echo "<pre>";
        foreach ($vars as $v) print_r($v);
        echo "</pre>";
    }

    public static function dd(...$vars)
    {
        self::dump(...$vars);
        exit;
    }

    public static function json($data)
    {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
