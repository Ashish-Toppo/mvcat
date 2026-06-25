# Controllers

Controllers contain the logic of your application. They are stored in the `app/Controllers/` directory, utilize PSR-4 namespaces, and should extend the base `Core\Classes\Controller` class.

## Creating a Controller

Here is an example of a modern controller `app/Controllers/HomeController.php`:

```php
<?php

namespace App\Controllers;

use Core\Classes\Controller;
use App\Models\User;

class HomeController extends Controller {

    public function index($request, $response, $id = null) {
        // Use the new Active Record Model
        $users = User::all();

        // Pass data to the view
        $this->view('home', ['users' => $users]);
    }
}
```

## Dependency Injection

MVCAT now features a robust Dependency Injection Container. Any dependencies type-hinted in your controller constructor will be automatically resolved and injected by the framework!

```php
<?php

namespace App\Controllers;

use Core\Classes\Controller;
use App\Services\CustomService;

class DashboardController extends Controller {

    protected CustomService $service;

    // CustomService is automatically injected!
    public function __construct(CustomService $service) {
        parent::__construct();
        $this->service = $service;
    }
}
```

## Available Controller Methods

By extending the `Controller` class, you have access to several useful methods:

- **`$this->view(string $viewName, array $data = [])`**: Renders a view.
- **`$this->redirect(string $url)`**: Redirects the user to a new URL.
- **`$this->input(?string $key = null, mixed $default = null)`**: Safely retrieves request input (`$_GET` or `$_POST`).
- **`$this->validate(array $rules)`**: Validates request data against the provided rules.
