# MVCAT - A Lightweight MVC Framework in PHP

**MVCAT** is a custom, lightweight MVC (Model-View-Controller) framework designed for fast, simple, and clean PHP web applications. 

## Features
- **Routing Engine:** Simple closure and controller-based routing system.
- **MVC Architecture:** Separation of logic via Controllers, Models, and Views.
- **Database Abstraction:** Built on PDO with easy-to-use querying methods.
- **Built-in Session and Security:** Basic session management and CSRF token generation out of the box.
- **Autoloading:** Dynamically loads classes and helpers as needed.
- **View Caching:** Basic template rendering with caching capabilities.

## Getting Started

Check out the [Documentation](docs/) to learn how to use MVCAT!
- [Getting Started](docs/getting_started.md)
- [Routing](docs/routing.md)
- [Controllers](docs/controllers.md)
- [Models](docs/models.md)
- [Views](docs/views.md)

## Requirements
- PHP 8+
- PDO
- Apache/Nginx (Apache with `mod_rewrite` is configured via `.htaccess`)

## License
This project is open-source and licensed under the MIT License.
