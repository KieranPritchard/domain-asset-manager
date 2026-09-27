<?php 
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;

    class HomeController extends Controller
    {
        public function show(array $params = []):void
        {
            // Stores the database error
            $db_error = $params["db_error"] ?? null;

            // Shows the page
            $this->view("home/show", ["error" => $db_error]);
        }
    }
?>