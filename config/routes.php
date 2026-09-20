<?php 
    // Creates a new router object
    $router = new Core\Router();

    // Root for first entry
    $router->get("/", "HomeController@index");

    // Login page controllers
    $router->get("/login", "LoginController@show");
?>