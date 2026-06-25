# Getting Started

Welcome to the modernized **MVCAT** framework! 

## Directory Structure
Here's a quick overview of the new directory structure:
- **`app/`**: This is where your application logic lives. It contains `Controllers/`, `Models/`, `Middlewares/`, `Providers/`, `Routes/`, and `Views/`.
- **`core/`**: Core framework files (the Engine). You generally don't need to touch these files.
- **`config/`**: Contains configuration files like `events.php` and `providers.php`.
- **`public/`**: The document root of your application. Contains `index.php` and `.htaccess`. All web requests should point here.
- **`modules/`**: Directory for modular features.
- **`vendor/`**: Composer dependencies.

## Installation & Configuration

1. **Install Dependencies:**
   Run the following command in the root of your project:
   ```bash
   composer install
   ```

2. **Environment Variables:**
   MVCAT uses a `.env` file for configuration. Copy the `.env.example` file in the root directory to `.env` and configure your settings:
   ```env
   # DATABASE
   APP_ENV=local # local / prod
   DB_HOST=localhost
   DB_NAME=mvcat_db
   DB_USER=root
   DB_PASS=

   # APP
   APP_BASE = /mvcat #leave as / if hosting at root (no trailing slash allowed)
   ```

## Running the Application
Since the entry point is in the `public/` directory, configure your local web server (like Apache, Nginx, or PHP's built-in server) to point its document root to the `public/` folder.

For example, using PHP's built-in web server:
```bash
cd public
php -S localhost:8000
```
Then visit `http://localhost:8000` in your browser.
