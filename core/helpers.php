<?php

use Core\Classes\View;

function view(string $view, array $data = []): void
{
    View::render($view, $data);
}

function url($path = '')
{
    $base = $_ENV['APP_BASE'];

    if ($path === '') {
        return $base;
    }

    return $base . '/' . ltrim($path, '/');
}

function url_with_hash($hash = '') {
    if ($hash === '') return '';

    // Get protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

    // Get current host
    $host = $_SERVER['HTTP_HOST'];

    // Get current request URI (path + query string)
    $requestUri = $_SERVER['REQUEST_URI'];

    // Remove existing hash if any
    $requestUri = preg_replace('/#.*$/', '', $requestUri);

    // Build final URL
    return $protocol . '://' . $host . $requestUri . '#' . ltrim($hash, '#');
}


function component(string $name, array $data = [], bool $return = false)
{
    // Remove leading slash if present
    if (str_starts_with($name, '/')) {
        $name = ltrim($name, '/');
    }
    
    $path = __DIR__ . "/../app/Views/$name.php";

    if (file_exists($path)) {
        extract($data); // inject variables into component

        if ($return) {
            ob_start();
            include $path;
            return ob_get_clean();
        } else {
            include $path;
        }
    } else {
        echo "<div style='color:red; background:black;'>
                 Component not found: $name <br>
                Please make sure you have this file: ROOT/app/views/$name.php 
            </div>";
    }
}



function redirect($path = "/")
{
    $redirectPath = $_ENV['APP_BASE'] . $path;
    header("Location: $redirectPath");
}

function redirect_path($path = "/")
{
    return $_ENV['APP_BASE'] . $path;
}

function base_url($path = '')
{
    // Automatically detect subfolder path
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $base = rtrim($scriptName, '/\\');
    return $base . '/' . ltrim($path, '/');
}




function link_to($path = '', $params = [])
{
    $url = base_url($path);

    if (!empty($params)) {
        $query = http_build_query($params);
        $url .= '?' . $query;
    }

    return $url;
}

function redirectBack($fallback = '/')
{
    $back = $_SERVER['HTTP_REFERER'] ?? $fallback;
    header("Location: " . $back);
    exit;
}


function getBaseUrl()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    return "$scheme://$host";
}

function generateToken($length = 16)
{
    return substr(str_shuffle(bin2hex(random_bytes($length))), 0, $length);
}

function verifyPageToken($sessionName, $valueFromUser): bool
{
    if (!isset($_SESSION[$sessionName])) return false;

    $isValid = hash_equals($_SESSION[$sessionName], $valueFromUser); // Safe comparison (prevent timing attacks)
    unset($_SESSION[$sessionName]); // Clear after checking to prevent reuse (safer than setting to '')

    return $isValid;
}

function session(string $key, $value = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // SET mode
    if (func_num_args() === 2) {
        $_SESSION[$key] = $value;
        return true;
    }

    // GET mode
    return $_SESSION[$key] ?? null;
}

function flash(string $key, $value = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $flashKey = '__flash__.' . $key;

    // SET mode
    if (func_num_args() === 2) {
        $_SESSION[$flashKey] = $value;
        return true;
    }

    // GET mode (and destroy)
    $val = $_SESSION[$flashKey] ?? null;
    unset($_SESSION[$flashKey]);
    return $val;
}

function logToFile($filename, $data, $folder = __DIR__ . "/../logs")
{
    // Ensure folder exists
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $filePath = rtrim($folder, '/') . '/' . $filename;

    // Ensure file exists
    if (!file_exists($filePath)) {
        file_put_contents($filePath, "=== Log started: " . date('Y-m-d H:i:s') . " ===\n");
    }

    // Convert arrays/objects to JSON for readability
    if (is_array($data) || is_object($data)) {
        $data = json_encode($data);
    }

    // Append log entry
    file_put_contents(
        $filePath,
        date('Y-m-d H:i:s') . " - " . $data . PHP_EOL,
        FILE_APPEND
    );
}

/**
 * Dispatch an event to its registered listeners
 *
 * @param object $event
 * @return void
 */
function fireEvent(object $event): void
{
    \Core\Classes\EventDispatcher::dispatch($event);
}

