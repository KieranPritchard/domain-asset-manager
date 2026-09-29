<?php 
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    use App\Models\Domains;

    class HomeController extends Controller
    {
        // Method to fetch the users from the database
        private function fetch_domains():array 
        {
            $user_domains = (new Domains)->get_domains($_SESSION["id"]);

            return $user_domains;
        }

        // Method to fetch the subdomains
        private function fetch_subdomains():string
        {
            return "";
        }

        // Method to send to javascript
        public function domains():string 
        {
            // Stores the user domains
            $domains = $this->fetch_domains();

            // Returns the encoded data
            return json_encode($domains);
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