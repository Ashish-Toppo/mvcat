<?php

namespace App\Middlewares;

use Core\Classes\Request;

class IsUserSignedIn
{
    public function handle(Request $request, \Closure $next)
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if required session variables exist
        if (
            !isset($_SESSION['e_user_id']) ||
            !isset($_SESSION['e_user_uid']) ||
            !isset($_SESSION['e_user_type'])
        ) {
            // Redirect to login page or throw exception
            redirect("/");
            exit();
        }

        // Verify user type is 3 (3 = university student) , now also allow external users
        if (!in_array($_SESSION['e_user_type'], [1, 2, 3, 4])) {
            // User doesn't have required permissions
            redirect("/");
            exit();
        }

        // Check if user_id starts with 'ERP_', if not, prepend it
        if (!str_starts_with($_SESSION['e_user_id'], 'ERP_') && !str_starts_with($_SESSION['e_user_id'], 'LCL_')) {
            $_SESSION['e_user_id'] = 'ERP_' . $_SESSION['e_user_id'];
        }

        // If we get here, user is properly authenticated and authorized
        // Continue with your logic...
        return $next($request);
    }
}
