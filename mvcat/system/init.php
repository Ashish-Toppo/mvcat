<?php

spl_autoload_register (function ($className) {

    include "classes/" . $className . ".php";
});

$controller = new Controller;
$model = new Model;
$router = new Router;
