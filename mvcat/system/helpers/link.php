<?php

function linkCss($cssPath) {

    $url = BASE . $cssPath;
    echo "<link rel='stylesheet' href='" . $url . "'/>";
}

function linkJs ($jsPath) {
    $url = BASE . $jsPath;
    echo "<script src='" . $url . "'></script>";
}

function url ($path = '') {
    echo BASE . $path;
}