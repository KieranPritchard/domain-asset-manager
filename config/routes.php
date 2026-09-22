<?php 
    // Creates a new router object
    $router = new Core\Router();

    // Root for first entry
    $router->get("/", "HomeController@index");

    // Login page controllers
    $router->get("/login", "LoginController@show");
    $router->post("/login", "LoginController@sign_in");

    // Handles the register routes
    $router->get("/signup", "RegisterController@show");
    $router->post("/signup", "RegisterController@register_user");
?>