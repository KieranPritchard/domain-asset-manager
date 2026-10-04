<?php 
    // Creates a new router object
    $router = new Core\Router();

    // Root for first entry
    $router->get("/", "IndexController@index");

    // Login page controllers
    $router->get("/login", "LoginController@show");
    $router->post("/login", "LoginController@sign_in");

    // Handles the register routes
    $router->get("/signup", "RegisterController@show");
    $router->post("/signup", "RegisterController@register_user");

    // Handles the home page functions
    $router->get("/home", "HomeController@show");
    $router->get("/home/domains", "HomeController@domains");
    $router->get("/home/subdomains", "HomeController@subdomains");
    $router->get("/home/records", "HomeController@records");

    // Handles the domains
    $router->get("/domains", "DomainsController@show");
    $router->get("/domains/json", "DomainsController@json");
    $router->post("/domains/create", "DomainsController@create");
    $router->post("/domains/update", "DomainsController@update");
    $router->post("/domains/delete", "DomainsController@delete");
?>