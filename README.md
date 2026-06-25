# MVCAT - A Modern MVC Framework in PHP

**MVCAT** has evolved into a robust, modern MVC (Model-View-Controller) framework designed for PHP web applications. It leverages a powerful Dependency Injection Container, an Eloquent-like Active Record ORM, Middleware Pipelines, and Event Dispatchers to build scalable applications.

## Features
- **Namespaces & Composer:** PSR-4 compliant autoloading.
- **Dependency Injection Container:** Automatic dependency resolution for Controllers and Middlewares.
- **Advanced Routing Engine:** Supports route groups, dynamic parameters (`{id}`), and middleware assignment.
- **Active Record ORM:** Eloquent-style models with relationships (`HasMany`, `BelongsTo`), eager loading, and query building.
- **Middleware Pipeline:** Robust request filtering and handling globally or per-route.
- **Event Dispatcher:** Decouple your logic by registering events and listeners.
- **Environment Configuration:** Uses `.env` files for managing configurations securely.

## Getting Started

Check out the [Documentation](docs/) to learn how to use the new MVCAT!
- [Getting Started](docs/getting_started.md)
- [Routing](docs/routing.md)
- [Controllers](docs/controllers.md)
- [Models](docs/models.md)
- [Middlewares](docs/middlewares.md)
- [Views](docs/views.md)

## Requirements
- PHP 8.0+
- PDO Extension
- Composer
- Apache/Nginx (Apache with `mod_rewrite` is configured via `.htaccess`)

## Installation
1. Clone the repository.
2. Run `composer install` to install dependencies.
3. Copy `.env.example` to `.env` and set your configuration variables.
4. Point your web server to the `public/` directory.

## License
This project is open-source and licensed under the MIT License.
