# Views

Views handle the HTML representation of your application. They are stored in the `app/Views/` directory.

## Creating a View

A view is simply a PHP file that outputs HTML. 

Example `app/Views/home.php`:
```php
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
</head>
<body>
    <h1>Welcome to MVCAT!</h1>
    
    <!-- Using data passed from the controller -->
    <p><?= $information; ?></p>
</body>
</html>
```

## Loading a View

You load a view from within a controller using the `$this->view()` method. The second argument is an associative array of data that will be extracted into individual variables inside the view file.

```php
$data = [
    'information' => 'This is some dynamic data!'
];

$this->view('home', $data);
```

## View Caching
MVCAT comes with a built-in template caching system. When a view is loaded using the custom `@template` tags, it is rendered and saved to the cache directory to make subsequent renders faster.
