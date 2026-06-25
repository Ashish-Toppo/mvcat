# Models

Models handle the interaction with your database. They are stored in the `app/models/` directory and should extend the base `Model` class.

## Creating a Model

A model generally maps to a single database table. You must define the `$table` and `$primary` properties.

Example `app/models/user.php`:
```php
<?php

class user extends Model {
    public $table = 'users';
    public $primary = 'id';
}
```

## Using the Model

You can load a model inside a controller using `$this->model('model_name')`.

```php
class myController extends Controller {
    public function index() {
        $userModel = $this->model('user');
        
        // Fetch all users
        $allUsers = $userModel->fetchall();
        
        // Custom Queries using PDO prepared statements
        $activeUsers = $userModel->query("SELECT * FROM users WHERE status = ?", ['active']);
    }
}
```

## Built-in Model Methods

- **`fetchall()`**: Returns all records from the model's table.
- **`query($sql, $params = [])`**: Executes a raw SQL query. It uses PDO prepared statements if `$params` are provided.
- **`insert($data_array)`**: Inserts a new record into the table using an associative array of columns and values.

```php
$userModel->insert([
    'username' => 'john_doe',
    'email' => 'john@example.com'
]);
```
