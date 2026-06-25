<?php

namespace App\Helpers;

class Arr {

    public static function get(array $arr, string $key, $default = null) {
        return $arr[$key] ?? $default;
    }

    public static function only(array $arr, array $keys): array {
        return array_intersect_key($arr, array_flip($keys));
    }

    public static function except(array $arr, array $keys): array {
        return array_diff_key($arr, array_flip($keys));
    }
}
