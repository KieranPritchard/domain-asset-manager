<?php
    namespace App\Controllers;

    use App\Models\Auth;
    use Core\Controller;
    use App\Models\Domains;
    use App\Models\Subdomains;
    use App\Models\Records;

    // Records controller
    class RecordsController extends Controller
    {
        // Record types allowed by the ENUM in the table
        private const RECORD_TYPES = ["A", "AAAA", "CNAME", "MX", "TXT", "NS", "SOA", "SRV"];

        // Record types that need a priority
        private const PRIORITY_TYPES = ["MX", "SRV"];

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

        // Method to fetch every record belonging to the user's subdomains
        private function fetch_records():array
        {
            $subdomain_ids = $this->owned_subdomain_ids();

            if (empty($subdomain_ids)) {
                return [];
            }

            $records = (new Records)->get_records($subdomain_ids);

            // The model returns a string if something went wrong
            return is_array($records) ? $records : [];
        }

        // Method to check a subdomain belongs to the logged in user
        private function owns_subdomain(int $subdomain_id):bool
        {
            return in_array($subdomain_id, $this->owned_subdomain_ids(), true);
        }

        // Method to check a record belongs to one of the logged in user's subdomains
        private function owns_record(int $record_id):bool
        {
            foreach ($this->fetch_records() as $record) {
                if ((int) $record["id"] === $record_id) {
                    return true;
                }
            }

            return false;
        }

        // Method to check a hostname looks valid, e.g. "mail.example.com"
        private function valid_hostname(string $hostname):bool
        {
            // A trailing dot is valid in DNS
            $hostname = rtrim($hostname, ".");

            return strlen($hostname) <= 253
                && (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/i', $hostname);
        }

        // Method to check the value suits the record type, returns an error message or an empty string
        private function validate_value(string $type, string $value):string
        {
            if ($value === "" || strlen($value) > 512) {
                return "Value must be between 1 and 512 characters";
            }

            switch ($type) {
                case "A":
                    return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? "" : "A records need a valid IPv4 address";

                case "AAAA":
                    return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? "" : "AAAA records need a valid IPv6 address";

                case "CNAME":
                case "NS":
                case "MX":
                    return $this->valid_hostname($value) ? "" : "$type records need a valid hostname";

                case "SRV":
                    // Format: "weight port target"
                    $parts = preg_split('/\s+/', $value);

                    if (count($parts) !== 3
                        || !ctype_digit($parts[0])
                        || !ctype_digit($parts[1])
                        || (int) $parts[1] > 65535
                        || !$this->valid_hostname($parts[2])) {
                        return "SRV value must look like: weight port target";
                    }

                    return "";

                // TXT and SOA are free-form
                default:
                    return "";
            }
        }

        // Method to read and clean the record fields from the form, returns [error, data]
        private function read_record_input():array
        {
            $subdomain_id = (int) ($_POST["subdomainId"] ?? 0);
            $type = strtoupper(trim($_POST["recordType"] ?? ""));
            $value = trim($_POST["value"] ?? "");
            $ttl_raw = trim($_POST["ttl"] ?? "");
            $priority_raw = trim($_POST["priority"] ?? "");

            // Checks the subdomain belongs to the user
            if (!$this->owns_subdomain($subdomain_id)) {
                return ["Subdomain not found", null];
            }

            // Checks the record type is allowed
            if (!in_array($type, self::RECORD_TYPES, true)) {
                return ["Invalid record type", null];
            }

            // Checks the value suits the type
            $value_error = $this->validate_value($type, $value);

            if ($value_error !== "") {
                return [$value_error, null];
            }

            // TTL is optional, but must be a sensible number when given
            $ttl = null;

            if ($ttl_raw !== "") {
                $ttl = filter_var($ttl_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 2147483647]]);

                if ($ttl === false) {
                    return ["TTL must be a whole number of seconds", null];
                }
            }

            // Priority is only used by MX and SRV
            $priority = null;

            if (in_array($type, self::PRIORITY_TYPES, true)) {
                $priority = filter_var($priority_raw, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 65535]]);

                if ($priority === false) {
                    return ["$type records need a priority between 0 and 65535", null];
                }
            }

            return ["", [
                "subdomain_id" => $subdomain_id,
                "type" => $type,
                "value" => $value,
                "ttl" => $ttl,
                "priority" => $priority
            ]];
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

            header('Content-Type: application/json');

            return json_encode($this->fetch_records());
        }

        // Method to prepare and create a record from model
        public function create():void
        {
            $this->require_login();

            [$error, $data] = $this->read_record_input();

            if ($error !== "") {
                $this->show(["feedback" => $error]);
                return;
            }

            $feedback = (new Records)->add_record(
                $data["type"],
                $data["value"],
                $data["ttl"],
                $data["priority"],
                $data["subdomain_id"]
            );

            $this->respond($feedback, "Record created successfully");
        }

        // Method to prepare and update a record from model
        public function update():void
        {
            $this->require_login();

            $id = (int) ($_POST["recordId"] ?? 0);

            // Checks the record belongs to the user
            if (!$this->owns_record($id)) {
                $this->show(["feedback" => "Record not found"]);
                return;
            }

            [$error, $data] = $this->read_record_input();

            if ($error !== "") {
                $this->show(["feedback" => $error]);
                return;
            }

            $feedback = (new Records)->update_record(
                $id,
                $data["type"],
                $data["value"],
                $data["ttl"],
                $data["priority"]
            );

            $this->respond($feedback, "Record updated successfully");
        }

        // Method to prepare and delete a record from model
        public function delete():void
        {
            $this->require_login();

            $id = (int) ($_POST["recordId"] ?? 0);

            // Checks the record belongs to the user
            if (!$this->owns_record($id)) {
                $this->show(["feedback" => "Record not found"]);
                return;
            }

            $feedback = (new Records)->delete_record($id);

            $this->respond($feedback, "Record deleted successfully");
        }

        // Method to show the view
        public function show(array $params = []):void
        {
            $this->require_login();

            $this->view("records/show", [
                "subdomains" => $this->fetch_subdomains(),
                "records" => $this->fetch_records(),
                "feedback" => $params["feedback"] ?? null
            ]);
        }
    }
?>