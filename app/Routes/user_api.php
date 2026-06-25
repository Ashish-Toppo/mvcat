<?php

use Core\Classes\App;

$router = $app->getRouter();

// route group used beacuse all routes here must comply with middlewares isUserSignedIn, ValidateCSRCFToken

$router->group('/user/api', ['IsUserSignedIn'], function ($router) {

    // Get current user data
    $router->get('/getData', ['UserAPI', 'getData']);

    // get latest 3 upcoming events
    $router->get('/get-latest-upcoming-events', ['UserAPI', 'getLatestFewUpcomingEvents']);

    // Update user profile
    $router->post('/update-profile', ['UserAPI', 'updateProfile']);

    // Get user certificates
    $router->get('/getUserCertificates', ['UserAPI', 'getUserCertificates']);

    // change user password
    $router->post('/change-password', ['UserAPI', 'change_password'], ['CSRFMiddleware']);

    // get user registered events
    $router->get('/get-registered-events', ['UserAPI', 'get_user_registered_events']);

    // Submit event feedback form
    $router->post('/event/{eventId}/feedback/{formId}/submit', ['User/FeedbackFormController', 'submit'], ['CSRFMiddleware']);
});
