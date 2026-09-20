<?php
    namespace App\Controllers;

    use Core\Controller;
    use Apps\Models\Auth;

    class HomeController extends Controller
    {
        public function index(): void
        {
            // Stores wheter the user is logged in or not
            $loggedin = (new Auth()->is_loggedin());

            // Checks if the user is loggedin
            if ($loggedin) {
                header("location: /home");
                exit;
            }

            header("location: /login");
            exit;
        }
    }
?>