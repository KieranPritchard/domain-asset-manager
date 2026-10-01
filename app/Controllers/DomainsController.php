<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Domains;

    // Domains controller
    class DomainsController extends Controller
    {
        // Method to fetch the users from the database
        private function fetch_domains():array 
        {
            $user_domains = (new Domains)->get_domains($_SESSION["id"]);

            return $user_domains;
        }

        // Method to prepare and create domain from model
        public function create_domain():void
        {
            // Stores the domain details
            $domain_name = htmlspecialchars(trim($_POST["name"]));
            $registar = htmlspecialchars(trim($_POST["registar"]));
            $id = $_SESSION["id"];

            // Stores the feedback from the model
            $feedback = (new Domains)->add_domain($domain_name, $registar, $id);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Domain created successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        // Method to show the view
        public function show(array $params = []):void
        {
            // Stores the login error
            $feedback = $params["feedback"] ?? null;

            // Stores the user domains
            $domains = $this->fetch_domains();

            // Shows the page
            $this->view("domains/show", ["domains" => $domains, "feedback" => $feedback]);
        }
    }
?>