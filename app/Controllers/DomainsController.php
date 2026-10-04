<?php
    namespace App\Controllers;

    use App\Models\Auth;
    use Core\Controller;
    use App\Models\Domains;

    // Domains controller
    class DomainsController extends Controller
    {
        // ---------- Helpers ----------

        // Method to redirect anyone who isn't logged in
        private function require_login():void
        {
            // Stores whether the user is logged in or not
            $loggedin = new Auth()->is_loggedin();

            // Checks if the user is loggedin
            if (!$loggedin) {
                header("location: /home");
                exit;
            }
        }

        // Method to fetch the user's domains from the database
        private function fetch_domains():array
        {
            return (new Domains)->get_domains($_SESSION["id"]);
        }

        // Method to check a domain belongs to the logged in user
        private function owns_domain(int $domain_id):bool
        {
            foreach ($this->fetch_domains() as $domain) {
                if ((int) $domain["id"] === $domain_id) {
                    return true;
                }
            }

            return false;
        }

        // Method to check a domain name looks valid, e.g. "example.com"
        private function valid_domain(string $domain_name):bool
        {
            return strlen($domain_name) <= 253
                && (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/', $domain_name);
        }

        // Method to turn the model's feedback into a message and re-render the page
        private function respond(string $feedback, string $success):void
        {
            // The model returns an empty string on success
            $this->show(["feedback" => $feedback ?: $success]);
        }

        // ---------- Actions ----------

        // Method to send to javascript
        public function json():string
        {
            $this->require_login();

            // Sets the headers
            header('Content-Type: application/json');

            // Returns the encoded user domains
            return json_encode($this->fetch_domains());
        }

        // Method to prepare and create domain from model
        public function create():void
        {
            $this->require_login();

            // Stores the domain details
            $domain_name = strtolower(trim($_POST["domainName"] ?? ""));
            $registar = trim($_POST["registar"] ?? "");

            // Checks the domain name is valid
            if (!$this->valid_domain($domain_name)) {
                $this->show(["feedback" => "Invalid domain name"]);
                return;
            }

            // Stores the feedback from the model
            $feedback = (new Domains)->add_domain($domain_name, $registar, $_SESSION["id"]);

            $this->respond($feedback, "Domain created successfully");
        }

        // Method to prepare and update domain from model
        public function update():void
        {
            $this->require_login();

            // Stores the domain details
            $id = (int) ($_POST["domainId"] ?? 0);
            $domain_name = strtolower(trim($_POST["domainName"] ?? ""));
            $registar = trim($_POST["registar"] ?? "");

            // Checks the domain belongs to the user
            if (!$this->owns_domain($id)) {
                $this->show(["feedback" => "Domain not found"]);
                return;
            }

            // Checks the domain name is valid
            if (!$this->valid_domain($domain_name)) {
                $this->show(["feedback" => "Invalid domain name"]);
                return;
            }

            // Stores the feedback from the model
            $feedback = (new Domains)->update_domain($id, $domain_name, $registar);

            $this->respond($feedback, "Domain updated successfully");
        }

        // Method to prepare and delete domain from model
        public function delete():void
        {
            $this->require_login();

            // Stores the domain id
            $id = (int) ($_POST["domainId"] ?? 0);

            // Checks the domain belongs to the user
            if (!$this->owns_domain($id)) {
                $this->show(["feedback" => "Domain not found"]);
                return;
            }

            // Stores the feedback from the model
            $feedback = (new Domains)->delete_domain($id);

            $this->respond($feedback, "Domain deleted successfully");
        }

        // Method to show the view
        public function show(array $params = []):void
        {
            $this->require_login();

            // Shows the page
            $this->view("domains/show", [
                "domains" => $this->fetch_domains(),
                "feedback" => $params["feedback"] ?? null
            ]);
        }
    }
?>