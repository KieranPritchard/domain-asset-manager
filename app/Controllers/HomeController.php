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

            if (!is_array($user_domains)) {
                throw new \RuntimeException("Could not load dashboard domains: " . $user_domains);
            }

            return $user_domains;
        }

        // Method to fetch the subdomains
        private function fetch_subdomains(?array $domains = null):array
        {
            // Stores the domain ids
            $ids = [];

            // Stores the user domains
            $domains ??= $this->fetch_domains();

            // Loops over the domains
            foreach ($domains as $domain) {
                // Adds the domain id to ids
                array_push($ids, $domain["id"]);
            }

            $user_subdomains = (new Subdomains)->get_subdomains($ids);

            if (!is_array($user_subdomains)) {
                throw new \RuntimeException("Could not load dashboard subdomains: " . $user_subdomains);
            }

            return $user_subdomains;
        }

        // Method to fetch the subdomains
        private function fetch_records(?array $subdomains = null):array
        {
            // Stores the domain ids
            $ids = [];

            // Stores the user domains
            $subdomains ??= $this->fetch_subdomains();

            // Loops over the domains
            foreach ($subdomains as $subdomain) {
                // Adds the domain id to ids
                array_push($ids, $subdomain["id"]);
            }

            $user_records = (new Records)->get_records($ids);

            if (!is_array($user_records)) {
                throw new \RuntimeException("Could not load dashboard DNS records: " . $user_records);
            }

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
            // Stores wheter the user is logged in or not
            $loggedin = new Auth()->is_loggedin();

            // Checks if the user is loggedin
            if (!$loggedin) {
                header("location: /home");
                exit;
            }

            // Stores the user domains
            $domains = $this->fetch_domains();
            $subdomains = $this->fetch_subdomains($domains);
            $records = $this->fetch_records($subdomains);

            // Shows the page
            $this->view("home/show", [
                "domains" => $domains,
                "subdomains" => $subdomains,
                "records" => $records
            ]);
        }
    }
?>