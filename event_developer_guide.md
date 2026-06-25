# Event System Developer Guide

The MVCat framework includes an Event/Listener system to help you decouple various aspects of your application. This guide will walk you through creating, registering, and dispatching events.

## 1. Concepts

- **Events**: Simple PHP classes that act as data containers holding information about what just happened (e.g., `UserRegistered`).
- **Listeners**: PHP classes that perform an action in response to an event (e.g., `SendWelcomeEmail`).
- **Dispatcher**: A centralized mechanism that receives an event and invokes all the listeners mapped to it.

## 2. Directory Structure and Namespaces

Events and Listeners should reside in their respective folders under `app/`. You can create as many subdirectories as you need to group related events and listeners logically.

For example, for User-related events:
- `app/Events/User/UserRegistered.php`
- `app/Listeners/User/SendWelcomeEmail.php`
- `app/Listeners/User/NotifyAdmin.php`

Because the framework uses PSR-4 autoloading (`App\` maps to `app/`), the namespaces for the above classes would be:
- `namespace App\Events\User;`
- `namespace App\Listeners\User;`

## 3. Creating an Event

Events are simple Data Transfer Objects (DTOs). They just hold the data needed by the listeners.

**File:** `app/Events/User/UserRegistered.php`
```php
<?php
namespace App\Events\User;

class UserRegistered
{
    public array $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }
}
```

## 4. Creating a Listener

A Listener is a class that contains a `handle()` method. This method will receive the event object as its argument.

**File:** `app/Listeners/User/SendWelcomeEmail.php`
```php
<?php
namespace App\Listeners\User;

use App\Events\User\UserRegistered;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        // Code to send welcome email to $user['email']
        error_log("Welcome email sent to: " . $user['email']);
    }
}
```

## 5. Registering Events and Listeners

Events and their corresponding Listeners must be mapped in the `config/events.php` file.

**File:** `config/events.php`
```php
<?php

return [
    \App\Events\User\UserRegistered::class => [
        \App\Listeners\User\SendWelcomeEmail::class,
        // You can add more listeners here, e.g.:
        // \App\Listeners\User\NotifyAdmin::class,
    ],
];
```

## 6. Firing an Event

You can trigger an event from anywhere in your application (e.g., within a Controller) using the `fireEvent()` global helper function.

**Example Usage in a Controller:**
```php
<?php
namespace App\Controllers;

use Core\Classes\Controller;
use App\Events\User\UserRegistered;

class AuthController extends Controller
{
    public function register()
    {
        // ... (Code to save user to the database) ...
        
        $user = [
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        // Trigger the event
        fireEvent(new UserRegistered($user));

        return $this->response->send("Registration successful!");
    }
}
```

### How it works behind the scenes:
1. When you call `fireEvent(new UserRegistered($user))`, the `EventDispatcher` looks up the `UserRegistered` class in `config/events.php`.
2. It finds `SendWelcomeEmail` mapped to it.
3. It automatically instantiates `SendWelcomeEmail` and calls its `handle()` method, passing the event object.
