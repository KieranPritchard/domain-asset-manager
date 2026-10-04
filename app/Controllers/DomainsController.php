<?php
    namespace App\Controllers;

    use App\Models\Auth;
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

        // Method to send to javascript
        public function json():string 
        {
            // Stores the user domains
            $domains = $this->fetch_domains();

            // Sets the headders
            header('Content-Type: application/json');

            // Returns the encoded data
            return json_encode($domains);
        }

        // Method to prepare and create domain from model
        public function create():void
        {
            // Stores the domain details
            $domain_name = htmlspecialchars(trim($_POST["domainName"]));
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

        // Method to prepare and create domain from model
        public function update():void
        {
            // Stores the domain details
            $domain_name = htmlspecialchars(trim($_POST["domainName"]));
            $registar = htmlspecialchars(trim($_POST["registar"]));
            $id = htmlspecialchars(trim($_POST["domainId"]));

            // Stores the feedback from the model
            $feedback = (new Domains)->update_domain($id, $domain_name, $registar);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Domain updated successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        public function delete():void
        {
            // Stores the domain details
            $id = htmlspecialchars(trim($_POST["domainId"]));

            // Stores the feedback from the model
            $feedback = (new Domains)->delete_domain($id);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Domain deleted successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        // Method to show the view
        public function show(array $params = []):void
        {
            // Stores wheter the user is logged in or not
            $loggedin = new Auth()->is_loggedin();

            // Checks if the user is loggedin
            if (!$loggedin) {
                header("location: /home");
                exit;
            }

            // Stores the login error
            $feedback = $params["feedback"] ?? null;

            // Stores the user domains
            $domains = $this->fetch_domains();

            // Shows the page
            $this->view("domains/show", ["domains" => $domains, "feedback" => $feedback]);
        }
    }
?>