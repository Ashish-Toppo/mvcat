<?php

namespace App\Middlewares;

use Core\Classes\Request;

class CaptchaMiddleware
{
    public function handle(Request $req, \Closure $next)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If captcha not verified in this session, render the holding page
        if (!isset($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
            view('public/verify_captcha');
            exit;
        }

        return $next($req);
    }
}
