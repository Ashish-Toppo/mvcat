<?php

use Modules\Auth\Controllers\LoginController;

// Auth Routes
$router->get('/auth/login', [LoginController::class, 'showLoginForm']);
$router->post('/auth/login', [LoginController::class, 'login']);
$router->get('/auth/logout', [LoginController::class, 'logout']);
