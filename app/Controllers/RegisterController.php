<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    
    class RegisterController extends Controller
    {
        public function register_user():void
        {
            try {
                // Stores the username and password
                $username = htmlspecialchars(trim($_POST["username"]));
                $password = htmlspecialchars(trim($_POST["password"]));
                $confim_password = htmlspecialchars(trim($_POST["confirm-password"]));

                // Stores the error
                $error = (new Auth())->register_user($username, $password, $confim_password);

                // Checks if there is an error
                if ($error) {
                    $this->show(["error" => $error]);
                    return; 
                }

                // Redirects the user
                header("location: /login");
            } catch (\Throwable $e) {
                error_log("build_db failed: " . $e->getMessage());
                throw $e;
            }
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