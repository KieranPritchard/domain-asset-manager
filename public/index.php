<?php
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require $path;
});

require '../config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI']);