# Middlewares

Middlewares provide a convenient mechanism for inspecting and filtering HTTP requests entering your application. MVCAT utilizes a robust Pipeline architecture for middlewares.

## Creating a Middleware

Middlewares should be placed in the `app/Middlewares/` directory.

Example `app/Middlewares/AuthMiddleware.php`:
```php
<?php

namespace App\Middlewares;

class AuthMiddleware {

    public function handle($request, $next) {
        // Perform action BEFORE the controller runs
        if (!isset($_SESSION['user_id'])) {
            // Redirect or return error response
            header("Location: /login");
            exit;
        }

        // Pass the request deeper into the application pipeline
        $response = $next($request);

        // Perform action AFTER the controller runs (optional)
        
        return $response;
    }
}
```

## Assigning Middleware

Middlewares can be applied globally or to specific routes.

### Route Specific Middleware

You can attach middleware to a single route or a group of routes in your route definitions (`app/Routes/*.php`):

```php
// Single Route
$router->get('/dashboard', [DashboardController::class, 'index'], ['AuthMiddleware']);

// Route Group
$router->group('/admin', ['AuthMiddleware', 'AdminOnlyMiddleware'], function($router) {
    $router->get('/settings', [SettingsController::class, 'index']);
});
```

*Note: You must pass the base name of the middleware class (e.g., `'AuthMiddleware'`). The router assumes the namespace is `App\Middlewares\`.*
