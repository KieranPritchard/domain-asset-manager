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

        // Method to show the view
        public function show(array $parems = []):void
        {
            // Stores the user domains
            $domains = $this->fetch_domains();

            // Shows the page
            $this->view("domains/show", ["domains" => $domains]);
        }
    }
?>