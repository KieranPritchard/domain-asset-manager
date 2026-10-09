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

    // Handles the domains
    $router->get("/domains", "DomainsController@show");
    $router->get("/domains/json", "DomainsController@json");
    $router->post("/domains/create", "DomainsController@create");
    $router->post("/domains/update", "DomainsController@update");
    $router->post("/domains/delete", "DomainsController@delete");

    // Handles the subdomains
    $router->get("/subdomains", "SubdomainsController@show");
    $router->get("/subdomains/json", "SubdomainsController@json");
    $router->post("/subdomains/create", "SubdomainsController@create");
    $router->post("/subdomains/update", "SubdomainsController@update");
    $router->post("/subdomains/delete", "SubdomainsController@delete");

    // Handles the DNS records
    $router->get("/dns-records", "RecordsController@show");
    $router->get("/dns-records/json", "RecordsController@json");
    $router->post("/dns-records/create", "RecordsController@create");
    $router->post("/dns-records/update", "RecordsController@update");
    $router->post("/dns-records/delete", "RecordsController@delete");
?>