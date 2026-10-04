<?php 
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    use App\Models\Subdomains;
    use App\Models\Domains;
use App\Models\Records;

    class HomeController extends Controller
    {
        // Method to fetch the users from the database
        private function fetch_domains():array 
        {
            $user_domains = (new Domains)->get_domains($_SESSION["id"]);

            return $user_domains;
        }

        // Method to fetch the subdomains
        private function fetch_subdomains():array
        {
            // Stores the domain ids
            $ids = [];

            // Stores the user domains
            $domains = $this->fetch_domains();

            // Loops over the domains
            foreach ($domains as $domain) {
                // Adds the domain id to ids
                array_push($ids, $domain["id"]);
            }

            $user_subdomains = (new Subdomains)->get_subdomains($ids);

            return $user_subdomains;
        }

        // Method to fetch the subdomains
        private function fetch_records():array
        {
            // Stores the domain ids
            $ids = [];

            // Stores the user domains
            $subdomains = $this->fetch_subdomains();

            // Loops over the domains
            foreach ($subdomains as $subdomain) {
                // Adds the domain id to ids
                array_push($ids, $subdomain["id"]);
            }

            $user_records = (new Records)->get_records($ids);

            return $user_records;
        }

        public function subdomains():string 
        {
            // Stores the user domains
            $subdomains = $this->fetch_subdomains();

            // Returns the encoded data
            return json_encode($subdomains);
        }

        public function records():string 
        {
            // Stores the user domains
            $records = $this->fetch_records();

            // Returns the encoded data
            return json_encode($records);
        }

        public function show(array $params = []):void
        {
            // Stores the user domains
            $domains = $this->fetch_domains();

            // Shows the page
            $this->view("home/show", ["domains" => $domains]);
        }
    }
?>