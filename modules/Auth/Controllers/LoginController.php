<?php

namespace Modules\Auth\Controllers;

use Core\Classes\Request;
use Core\Classes\Response;
use Core\Classes\Controller;

class LoginController extends Controller
{
    public function showLoginForm(Request $request, Response $response)
    {
        return $response->send(view('auth::login'));
    }

    public function login(Request $request, Response $response)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // Placeholder for real DB auth logic
        if ($email === 'admin@example.com' && $password === 'password') {
            session('e_user_id', 1);
            session('e_user_type', 1);
            return $response->redirect('/dashboard');
        }

        flash('error', 'Invalid email or password.');
        return $response->redirect('/auth/login');
    }

    public function logout(Request $request, Response $response)
    {
        session_destroy();
        return $response->redirect('/auth/login');
    }
}
