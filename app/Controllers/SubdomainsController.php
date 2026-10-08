<?php
    namespace App\Controllers;

    use App\Models\Auth;
    use Core\Controller;
    use App\Models\Domains;
    use App\Models\Subdomains;

    // Subdomains controller
    class SubdomainsController extends Controller
    {
        // Allowed values for the status column
        private const STATUSES = ["active", "inactive", "unknown"];

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

        // Method to fetch the subdomains for every domain the user owns
        private function fetch_subdomains():array
        {
            // Stores the ids of the user's domains
            $domain_ids = array_column($this->fetch_domains(), "id");

            // Stores the subdomains (the model returns a string on error)
            $subdomains = (new Subdomains)->get_subdomains($domain_ids);

            return is_array($subdomains) ? $subdomains : [];
        }

        // Method to find one of the user's domains by id (also proves ownership)
        private function find_domain(int $domain_id):?array
        {
            foreach ($this->fetch_domains() as $domain) {
                if ((int) $domain["id"] === $domain_id) {
                    return $domain;
                }
            }

            return null;
        }

        // Method to find one of the user's subdomains by id (also proves ownership)
        private function find_subdomain(int $subdomain_id):?array
        {
            foreach ($this->fetch_subdomains() as $subdomain) {
                if ((int) $subdomain["id"] === $subdomain_id) {
                    return $subdomain;
                }
            }

            return null;
        }

        // Method to check the part before the parent domain, e.g. "api" or "dev.api"
        private function valid_prefix(string $prefix):bool
        {
            // Each label is 1-63 chars, letters/digits/hyphens, no leading or trailing hyphen
            return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*$/', $prefix)
                && strlen($prefix) <= 190;
        }

        // Method to send to javascript
        public function json():string
        {
            $this->require_login();

            // Stores the user subdomains
            $subdomains = $this->fetch_subdomains();

            // Sets the headers
            header('Content-Type: application/json');

            // Returns the encoded data
            return json_encode($subdomains);
        }

        // Method to prepare and create subdomain from model
        public function create():void
        {
            $this->require_login();

            // Stores the subdomain details
            $prefix = strtolower(trim($_POST["subdomainName"] ?? ""));
            $domain_id = (int) ($_POST["domainId"] ?? 0);

            // Checks the parent domain exists and belongs to the user
            $parent = $this->find_domain($domain_id);

            if (!$parent) {
                $this->show(["feedback" => "Domain not found"]);
                return;
            }

            // Checks the subdomain name is valid
            if (!$this->valid_prefix($prefix)) {
                $this->show(["feedback" => "Invalid subdomain name"]);
                return;
            }

            // Builds the fqdn, e.g. api + example.com
            $fqdn = $prefix . "." . $parent["name"];

            // Stores the feedback from the model
            $feedback = (new Subdomains)->add_subdomain($fqdn, $domain_id);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Subdomain created successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        // Method to prepare and update subdomain from model
        public function update():void
        {
            $this->require_login();

            // Stores the subdomain details
            $subdomain_id = (int) ($_POST["subdomainId"] ?? 0);
            $prefix = strtolower(trim($_POST["subdomainName"] ?? ""));
            $status = trim($_POST["status"] ?? "");

            // Checks the subdomain exists and belongs to the user
            $subdomain = $this->find_subdomain($subdomain_id);

            if (!$subdomain) {
                $this->show(["feedback" => "Subdomain not found"]);
                return;
            }

            // Checks the inputs are valid
            if (!$this->valid_prefix($prefix)) {
                $this->show(["feedback" => "Invalid subdomain name"]);
                return;
            }

            if (!in_array($status, self::STATUSES, true)) {
                $this->show(["feedback" => "Invalid status"]);
                return;
            }

            // Rebuilds the fqdn from the subdomain's own parent domain
            $parent = $this->find_domain((int) $subdomain["domain_id"]);
            $fqdn = $prefix . "." . $parent["name"];

            // Stores the feedback from the model
            $feedback = (new Subdomains)->update_subdomain($subdomain_id, $fqdn, $status);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Subdomain updated successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        // Method to prepare and delete subdomain from model
        public function delete():void
        {
            $this->require_login();

            // Stores the subdomain id
            $subdomain_id = (int) ($_POST["subdomainId"] ?? 0);

            // Checks the subdomain exists and belongs to the user
            if (!$this->find_subdomain($subdomain_id)) {
                $this->show(["feedback" => "Subdomain not found"]);
                return;
            }

            // Stores the feedback from the model
            $feedback = (new Subdomains)->delete_subdomain($subdomain_id);

            // Checks if there is any feedback
            if (!$feedback) {
                $this->show(["feedback" => "Subdomain deleted successfully"]);
            } else {
                $this->show(["feedback" => $feedback]);
            }
        }

        // Method to show the view
        public function show(array $params = []):void
        {
            $this->require_login();

            // Stores the feedback
            $feedback = $params["feedback"] ?? null;

            // Shows the page
            $this->view("subdomains/show", [
                "domains" => $this->fetch_domains(),
                "subdomains" => $this->fetch_subdomains(),
                "feedback" => $feedback
            ]);
        }
    }
?>