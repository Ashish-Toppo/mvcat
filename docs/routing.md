# Routing

Routing in MVCAT maps URLs to specific controllers or closures. Routes are automatically loaded from any `.php` file inside the `app/Routes/` directory.

The Router is accessible via the `$app->getRouter()` or directly in the route files using the scoped `$app` variable.

## Defining Routes

The router supports `GET`, `POST`, and `DELETE` methods.

### Controller Routing
Route a URL directly to a Controller's method. Note that you must provide the class name as a string or use `::class`. MVCAT's Dependency Injection Container will automatically instantiate the controller and resolve its dependencies.

```php
use App\Controllers\HomeController;

// In app/Routes/web.php
$router = $app->getRouter();

$router->get('/', [HomeController::class, 'index']);
```

### Route Parameters
You can capture segments of the URI using `{}`. These parameters are injected into the Request object and passed directly to your controller method.

```php
$router->get('/user/{id}', [UserController::class, 'show']);
```
In your controller:
```php
public function show($request, $response, $id) {
    echo "User ID: " . $id;
}
```

### Route Groups & Middleware
You can group routes to share prefixes and middlewares.

```php
$router->group('/api', ['AuthMiddleware'], function($router) {
    
    // This route responds to /api/users
    $router->get('/users', [ApiController::class, 'users']);
    
});
```

### Route Not Found (404)
If no route matches the URI, the framework automatically returns a 404 response.
