<?php
session_start();
include_once __DIR__ . '/../sso_user.php';

// prefix erp redirects user ids with "ERP_"
if (isset($_SESSION['e_user_id'])) {
    if (is_numeric($_SESSION['e_user_id'])) {
        $_SESSION['e_user_id'] = 'ERP_' . $_SESSION['e_user_id'];
    }
}

$_SESSION['showUnderConstructionModules'] = false;

// default sessions
if (!isset($_SESSION['e_is_external_user']))
	$_SESSION['e_is_external_user'] = 1;

// Load environment
require_once __DIR__ . '/../loadenv.php';
loadEnv(__DIR__ . '/../.env');

// load helpers
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/centralFunctions.php';

// set default time zone
date_default_timezone_set('Asia/Kolkata');

logToFile("access.log", "user id ".$_SESSION['e_user_id']." ");

// Autoloader (PSR-4)
spl_autoload_register(function ($class) {
    $baseDir = dirname(__DIR__) . '/';

    $map = [
        'App\\'                => 'app/',
        'Core\\'               => 'core/',
        'Modules\\'            => 'modules/',
        'PHPMailer\\PHPMailer\\' => 'vendor/phpmailer/',
    ];

    foreach ($map as $prefix => $folder) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = $baseDir . $folder . $relativeClass . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            } else {
                error_log("Autoload failed: $file");
            }
        }
    }
});
require_once __DIR__ . '/../vendor/autoload.php';

// create session token if not already created
if (!isset($_SESSION['e_csrf_token'])) $_SESSION['e_csrf_token'] = substr(str_shuffle(bin2hex(random_bytes(16))), 0, 16);

use App\Models\SystemSetting;
use App\Helpers\AccessControl;

$settingsModel = new SystemSetting();
$settings = $settingsModel->getAllSettingsFormated();
if (!empty($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') {
    if (!AccessControl::isAdmin()) {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!preg_match('#/login|/authenticate|/assets|/fontawesome#', $requestUri)) {
            http_response_code(503);
            $message = $settings['maintenance_message'] ?? 'We are currently undergoing scheduled maintenance. Please check back soon.';
            echo view('errors/maintenance', ['message' => $message]);
            exit;
        }
    }
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the core app
use Core\Classes\App;
$app = new App();

// Load Events
$eventsMap = require_once __DIR__ . '/../config/events.php';
if (is_array($eventsMap)) {
    \Core\Classes\EventDispatcher::register($eventsMap);
}

// Load Service Providers
$providers = require_once __DIR__ . '/../config/providers.php';
if (is_array($providers)) {
    $app->registerProviders($providers);
}

// Load all route files from app/Routes
$app->loadRoutes(__DIR__ . '/../app/Routes');

// Run the app
$app->run();
