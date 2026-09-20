<?php
    namespace App\Controllers;

    use Core\Controller;
    
    class LoginController extends Controller
    {
        public function show(array $params): void
        {
            // Stores the login error
            $login_error = $params["error"];

            // Shows the login page
            $this->view("login/show", ["error" => $login_error]);
        }
    }
?>