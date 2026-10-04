<?php
    namespace App\Models;

    use Core\Model;

    // Subdomains model
    class Subdomains extends Model
    {
        // Method to get the subdomains for a list of domains
        public function get_subdomains(array $domain_ids): string|array
        {
            try {
                // Nothing to look up, and "IN ()" is invalid SQL
                if (empty($domain_ids)) {
                    return [];
                }

                // Builds one placeholder per id, e.g. "?,?,?"
                $placeholders = implode(",", array_fill(0, count($domain_ids), "?"));

                // Prepares a statement to get the subdomains
                $get_subdomains = $this->db->prepare("SELECT * FROM subdomains WHERE domain_id IN ($placeholders)");

                // Binds every id as an integer
                $types = str_repeat("i", count($domain_ids));
                $get_subdomains->bind_param($types, ...array_map("intval", $domain_ids));

                // Executes the statement
                $get_subdomains->execute();

                // Stores the result
                $result = $get_subdomains->get_result();

                // Fetches all rows as an associative array
                $subdomains = $result->fetch_all(MYSQLI_ASSOC);

                // Closes the statement and returns the subdomains
                $get_subdomains->close();

                return $subdomains;
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add subdomain
        public function add_subdomain(string $fqdn, int $domain_id): string
        {
            try {
                // Prepares a statement to check if the subdomain was already present
                $check_subdomain_exists = $this->db->prepare("SELECT id FROM subdomains WHERE fqdn = ?");

                // Binds the parameters
                $check_subdomain_exists->bind_param("s", $fqdn);

                // Executes the statement
                $check_subdomain_exists->execute();

                // Stores the result
                $check_subdomain_exists->store_result();

                // Checks if the subdomain exists
                if ($check_subdomain_exists->num_rows >= 1) {
                    $check_subdomain_exists->close();
                    return "Subdomain already exists";
                }

                $check_subdomain_exists->close();

                // Adds the subdomain
                $add_subdomain = $this->db->prepare("INSERT INTO subdomains (domain_id, fqdn) VALUES (?, ?)");

                // Binds the parameters
                $add_subdomain->bind_param("is", $domain_id, $fqdn);

                // Executes and closes
                $add_subdomain->execute();
                $add_subdomain->close();

                return "";
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update subdomain
        public function update_subdomain(int $subdomain_id, string $fqdn, string $status): string
        {
            try {
                // Statement to update the subdomain entry
                $subdomain_update = $this->db->prepare("UPDATE subdomains SET fqdn = ?, status = ? WHERE id = ?");

                // Binds the parameters
                $subdomain_update->bind_param("ssi", $fqdn, $status, $subdomain_id);

                // Executes the statement
                $subdomain_update->execute();

                // Checks whether the row actually existed / changed
                if ($subdomain_update->affected_rows === 0) {
                    $subdomain_update->close();
                    return "No subdomain updated (not found or unchanged)";
                }

                $subdomain_update->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }

        // Method to delete the subdomain
        public function delete_subdomain(int $subdomain_id): string
        {
            try {
                // Statement to delete the subdomain entry
                $subdomain_delete = $this->db->prepare("DELETE FROM subdomains WHERE id = ?");

                // Binds the parameters
                $subdomain_delete->bind_param("i", $subdomain_id);

                // Executes the statement
                $subdomain_delete->execute();

                // Checks whether a row was actually deleted
                if ($subdomain_delete->affected_rows === 0) {
                    $subdomain_delete->close();
                    return "No subdomain deleted (not found)";
                }

                $subdomain_delete->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }
    }
?>