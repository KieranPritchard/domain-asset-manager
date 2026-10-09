<?php
    namespace App\Controllers;

    use App\Models\Auth;
    use Core\Controller;
    use App\Models\Domains;
    use App\Models\Subdomains;
    use App\Models\RecordHistory;

    // History controller
    class HistoryController extends Controller
    {
        // ---------- Helpers ----------

        // Method to redirect anyone who isn't logged in
        private function require_login():void
        {
            $loggedin = (new Auth())->is_loggedin();

            if (!$loggedin) {
                header("location: /home");
                exit;
            }
        }

        // Method to fetch the user's domains
        private function fetch_domains():array
        {
            $domains = (new Domains)->get_domains($_SESSION["id"]);

            return is_array($domains) ? $domains : [];
        }

        // Method to fetch the subdomains under the user's domains
        private function fetch_subdomains():array
        {
            $domain_ids = array_map(fn($domain) => (int) $domain["id"], $this->fetch_domains());

            // Nothing to look up if the user has no domains
            if (empty($domain_ids)) {
                return [];
            }

            $subdomains = (new Subdomains)->get_subdomains($domain_ids);

            return is_array($subdomains) ? $subdomains : [];
        }

        // Method to fetch the ids of the subdomains the user owns
        private function owned_subdomain_ids():array
        {
            return array_map(fn($subdomain) => (int) $subdomain["id"], $this->fetch_subdomains());
        }

        // Method to fetch history for the user's subdomains, optionally for just one of them
        private function fetch_history(int $subdomain_id = 0, int $limit = 100):array
        {
            $owned_ids = $this->owned_subdomain_ids();

            // Narrows to one subdomain, but only if the user owns it
            if ($subdomain_id > 0) {
                if (!in_array($subdomain_id, $owned_ids, true)) {
                    return [];
                }

                $owned_ids = [$subdomain_id];
            }

            if (empty($owned_ids)) {
                return [];
            }

            $history = (new RecordHistory)->get_history($owned_ids, $limit);

            // The model returns a string if something went wrong
            return is_array($history) ? $history : [];
        }

        // ---------- Actions ----------

        // Method to send to javascript
        public function json():string
        {
            $this->require_login();

            header('Content-Type: application/json');

            $subdomain_id = (int) ($_GET["subdomainId"] ?? 0);
            $limit = (int) ($_GET["limit"] ?? 100);

            return json_encode($this->fetch_history($subdomain_id, $limit));
        }

        // Method to show the view
        public function show():void
        {
            $this->require_login();

            $subdomain_id = (int) ($_GET["subdomainId"] ?? 0);

            $this->view("history/show", [
                "subdomains" => $this->fetch_subdomains(),
                "history" => $this->fetch_history($subdomain_id),
                "selected_subdomain_id" => $subdomain_id
            ]);
        }
    }
?>