# Routing

Routing in MVCAT maps URLs to specific controllers or closures. All routes are defined in `app/setup/routes.php`.

## Defining Routes

The router supports `GET` and `POST` methods via the `$routes->get()` and `$routes->post()` methods.

### Controller Routing
You can route a URL directly to a Controller's method by passing an array `['controller_name', 'method_name']`.

```php
// Route to the 'home' method of the 'view' controller
$routes->get('/', ['view', 'home'], '');
```

### Closure Routing
You can use an anonymous function (closure) for simple logic directly in the route file. The closure takes a `$controller` object which gives you access to the base controller methods.

```php
$routes->get('/about', function($controller) {
    $controller->view('about');
});
```

## Route Not Found (404)
You can define a custom "404 Not Found" handler in `routes.php`:

```php
$routes->routeNotFound(function () {
    echo "<h1>404 - Page Not Found</h1>";
});
```
