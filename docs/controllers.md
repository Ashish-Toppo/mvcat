# Controllers

Controllers contain the logic of your application. They tie together models and views.
Controllers are stored in the `app/controllers/` directory and should extend the base `Controller` class.

## Creating a Controller

Here is an example of a simple controller `app/controllers/view.php`:

```php
<?php

class view extends Controller {

    public function home($data = []) {
        // Prepare data
        $info = ['information' => 'Hello World!'];

        // Load a view and pass data
        $this->view('home', $info);
    }
}
```

## Available Controller Methods

By extending the `Controller` class, you have access to several useful methods:

- **`$this->view($viewName, $data = [])`**: Loads a view file from `app/views/` and extracts `$data` array to be used as variables in the view.
- **`$this->model($modelName)`**: Loads and returns an instance of a model from `app/models/`.
- **`$this->controller($controllerName)`**: Loads and returns an instance of another controller.
- **`$this->input($inputName)`**: Safely retrieves `$_POST` or `$_GET` input via stripping tags and trimming.
- **Session Methods**: 
  - `$this->setSession($name, $value)`
  - `$this->getSession($name)`
  - `$this->unsetSession($name)`
  - `$this->destroy()`
