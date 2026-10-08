<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    use Core\Database;

    class IndexController extends Controller
    {
        // Index command
        public function index(): void
        {
            // Stores wheter the user is logged in or not
            $loggedin = new Auth()->is_loggedin();

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