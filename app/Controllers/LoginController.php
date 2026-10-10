<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    
    class LoginController extends Controller
    {
        public function sign_in():void
        {
            // Stores the username and password
            $username = htmlspecialchars(trim($_POST["username"]));
            $password = htmlspecialchars(trim($_POST["password"]));

            // Stores the error
            $error = (new Auth())->login_user($username, $password);

            // Checks if there is an error
            if ($error) {
                $this->redirectWithToast("/login", "error", "Could not sign in", $error);
            }

            $this->redirectWithToast("/home", "success", "Signed in successfully");
        }

        public function logout():void
        {
            (new Auth())->logout();

            session_start();
            session_regenerate_id(true);
            $this->redirectWithToast("/login", "success", "Signed out successfully");
        }

        // Method to show the page
        public function show(array $params = []): void
        {
            // Stores the login error
            $login_error = $params["error"] ?? null;

            // Shows the login page
            $this->view("login/show", ["error" => $login_error]);
        }
    }
?>