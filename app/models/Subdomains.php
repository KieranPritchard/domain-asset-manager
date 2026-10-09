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

                $this->db->beginTransaction();

                // Prepares a statement to get the subdomains
                $get_subdomains = $this->db->prepare("SELECT * FROM subdomains WHERE domain_id IN (" . implode(",", array_fill(0, count($domain_ids), "?")) . ")");

                // Executes the statement
                $get_subdomains->execute($domain_ids);

                // Fetches all rows as an associative array
                $subdomains = $get_subdomains->fetchAll();

                $this->db->commit();

                return $subdomains;
            } catch (\Throwable $err) {
                $this->db->rollBack();
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add subdomain
        public function add_subdomain(string $fqdn, int $domain_id): string
        {
            try {
                $this->db->beginTransaction();

                // Prepares a statement to check if the subdomain was already present
                $check_subdomain_exists = $this->db->prepare("SELECT id FROM subdomains WHERE fqdn = ?");

                // Executes the statement
                $check_subdomain_exists->execute([$fqdn]);

                // Checks if the subdomain exists
                if ($check_subdomain_exists->rowCount() >= 1) {
                    $this->db->rollBack();
                    return "Subdomain already exists";
                }

                // Adds the subdomain
                $add_subdomain = $this->db->prepare("INSERT INTO subdomains (domain_id, fqdn) VALUES (?, ?)");

                // Executes and closes
                $add_subdomain->execute([$domain_id, $fqdn]);

                $this->db->commit();

                return "";
            } catch (\Throwable $err) {
                $this->db->rollBack();
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update subdomain
        public function update_subdomain(int $subdomain_id, string $fqdn, string $status): string
        {
            try {
                $this->db->beginTransaction();

                // Statement to update the subdomain entry
                $subdomain_update = $this->db->prepare("UPDATE subdomains SET fqdn = ?, status = ? WHERE id = ?");

                // Executes the statement
                $subdomain_update->execute([$fqdn, $status, $subdomain_id]);

                // Checks whether the row actually existed / changed
                if ($subdomain_update->rowCount() === 0) {
                    $this->db->rollBack();
                    return "No subdomain updated (not found or unchanged)";
                }

                return "";
            } catch (\Throwable $err) {
                $this->db->rollBack();
                return (string) $err;
            }
        }

        // Method to delete the subdomain
        public function delete_subdomain(int $subdomain_id): string
        {
            try {
                $this->db->beginTransaction();

                // Statement to delete the subdomain entry
                $subdomain_delete = $this->db->prepare("DELETE FROM subdomains WHERE id = ?");

                // Binds the parameters
                $subdomain_delete->execute([$subdomain_id]);

                // Checks whether a row was actually deleted
                if ($subdomain_delete->rowCount() === 0) {
                    $this->db->rollBack();
                    return "No subdomain deleted (not found)";
                }

                $this->db->commit();
                return "";
            } catch (\Throwable $err) {
                $this->db->rollBack();
                return (string) $err;
            }
        }
    }
?>