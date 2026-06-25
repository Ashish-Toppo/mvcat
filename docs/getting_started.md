# Getting Started

Welcome to the **MVCAT** framework! 

## Directory Structure
Here's a quick overview of the directory structure:
- **`app/`**: This is where you will do most of your work. It contains your `controllers`, `models`, `views`, and `routes.php`.
- **`config/`**: Contains `config.php` for setting database credentials and environment modes (`dev` or `prod`).
- **`public/`**: The document root of your application. Contains `index.php` and `.htaccess`. All web requests should point here.
- **`system/`**: Core framework files. You generally don't need to touch these files.

## Configuration
Before running the application, set up your configuration in `config/config.php`:

```php
// App Mode: 'dev' or 'prod'
define("MODE", "dev");

// Database Configurations
define("HOST", "localhost");
define("USER", "root");
define("DATABASE", "my_database");
define("PASSWORD", "my_password");
```

## Running the Application
Since the entry point is in the `public/` directory, configure your local web server (like Apache, Nginx, or PHP's built-in server) to point its document root to the `public/` folder.

For example, using PHP's built-in web server:
```bash
cd public
php -S localhost:8000
```
Then visit `http://localhost:8000` in your browser.
