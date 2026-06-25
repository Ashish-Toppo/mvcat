<?php

$router = $app->getRouter();

// View and Manage Events
$router->get('/event', ['EventController', 'view_event']);