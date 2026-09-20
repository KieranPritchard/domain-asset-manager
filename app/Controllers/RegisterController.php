<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    
    class RegisterController extends Controller
    {
        public function register_user():void
        {
            // Stores the username and password
            $username = htmlspecialchars(trim($_POST["username"]));
            $password = htmlspecialchars(trim($_POST["password"]));
            $confim_password = htmlspecialchars(trim($_POST["confirm_password"]));

            // Stores the error
            $error = (new Auth())->register_user($username, $password, $confim_password);

            // Checks if there is an error
            if ($error) {
                $this->show(["error" => $error]);
            }

            // Redirects the user
            header("location: /home");
        }

        // Method to show the page
        public function show(array $params = []): void
        {
            // Stores the login error
            $register_error = $params["error"] ?? null;

            // Shows the login page
            $this->view("register/show", ["error" => $register_error]);
        }
    }
?>