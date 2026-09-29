<?php
    namespace App\Models;

    use Core\Model;

    // Modals Class
    class Subdomains extends Model
    {
        // Method to get the domains
        public function get_subdomains(int $id): string|array
        {
            try {
                // Prepares a statement to get the domains
                $get_user_subdomains = $this->db->prepare("SELECT * FROM subdomains WHERE user_id = ?");

                $get_user_subdomains->bind_param("i", $id);

                // Executes the statement
                $get_user_subdomains->execute();

                // Stores the result
                $result = $get_user_subdomains->get_result();

                // Fetches all rows as an associative array
                $domains = $result->fetch_all(MYSQLI_ASSOC);

                // Closes the statement and returns the domains
                $get_user_subdomains->close();

                return $domains;
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add domain
        public function add_subdomain(string $subdomain_name, string $fqdn, int $domain_id): string
        {
            try {
                // Prepares a statement to check if the domain was already present
                $check_subdomain_exists = $this->db->prepare("SELECT * FROM domains WHERE name = ?");

                // Binds the domain parameters
                $check_subdomain_exists->bind_param("s", $subdomain_name);

                // Executes the statement
                $check_subdomain_exists->execute();

                // Stores the result
                $check_subdomain_exists->store_result();

                // Checks if the domain exists
                if ($check_subdomain_exists->num_rows == 1) {
                    $check_subdomain_exists->close();
                    return "Domain already exists";
                }

                $check_subdomain_exists->close();

                // Adds the domain
                $add_subdomain = $this->db->prepare("INSERT INTO subdomains (name, domain_id, fqdn) VALUES (?, ?, ?)");

                // Binds the parameters
                $add_subdomain->bind_param("sis", $subdomain_name, $domain_id, $fqdn);

                // Executes and closes
                $add_subdomain->execute();
                $add_subdomain->close();

                return "";
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update domain
        public function update_subdomain(int $subdomain_id, string $domain_name, string $fqdn, string $status): string
        {
            try {
                // Statement to update the domain entry
                $subdomain_update = $this->db->prepare("UPDATE domains SET name = ?, fqdn = ?, status = ? WHERE id = ?");

                // Binds the parameters
                $subdomain_update->bind_param("sssi", $domain_name, $fqdn, $status, $subdomain_id);

                // Executes the statement
                $subdomain_update->execute();

                // Checks whether the row actually existed / changed
                if ($subdomain_update->affected_rows === 0) {
                    $subdomain_update->close();
                    return "No domain updated (not found or unchanged)";
                }

                $subdomain_update->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }

        // Method to delete the domain
        public function delete_subdomain(int $subdomain_id): string
        {
            try {
                // Statement to delete the domain entry
                $subdomain_delete = $this->db->prepare("DELETE FROM subdomains WHERE id = ?");

                // Binds the parameters
                $subdomain_delete->bind_param("i", $subdomain_id);

                // Executes the statement
                $subdomain_delete->execute();

                // Checks whether a row was actually deleted
                if ($subdomain_delete->affected_rows === 0) {
                    $subdomain_delete->close();
                    return "No domain deleted (not found)";
                }

                $subdomain_delete->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }
    }