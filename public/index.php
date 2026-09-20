<?php
spl_autoload_register(function ($class) {
    $class = str_replace('App\\', '', $class);
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php'; // add ../
    if (file_exists($path)) require $path;
});

require __DIR__ . '/../config/routes.php';
$router->dispatch($_SERVER['REQUEST_URI']);