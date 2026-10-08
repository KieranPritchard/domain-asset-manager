<?php
    namespace App\Models;

    use Core\Model;

    // Modals Class
    class Domains extends Model
    {
        // Method to get the domains
        public function get_domains(int $id): string|array
        {
            try {
                // Prepares a statement to get the domains
                $get_user_domains = $this->db->prepare("SELECT * FROM domains WHERE user_id = ?");

                // Executes the statement
                $get_user_domains->execute([$id]);

                // Fetches all rows as an associative array
                $domains = $get_user_domains->fetchAll();
                return $domains;
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add domain
        public function add_domain(string $domain_name, string $registar, int $user_id): string
        {
            try {
                // Prepares a statement to check if the domain was already present
                $check_domain_exists = $this->db->prepare("SELECT * FROM domains WHERE name = ?");

                // Executes the statement
                $check_domain_exists->execute([$domain_name]);

                // Checks if the domain exists
                if ($check_domain_exists->rowCount() == 1) {
                    return "Domain already exists";
                }

                // Adds the domain
                $add_domain = $this->db->prepare("INSERT INTO domains (name, user_id, registrar) VALUES (?, ?, ?)");

                // Executes and closes
                $add_domain->execute([$domain_name, $user_id, $registar]);

                return "";
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update domain
        public function update_domain(int $domain_id, string $domain_name, string $registar): string
        {
            try {
                // Statement to update the domain entry
                $domain_update = $this->db->prepare("UPDATE domains SET name = ?, registrar = ? WHERE id = ?");

                // Binds the parameters
                $domain_update->execute([$domain_name, $registar, $domain_id]);

                // Executes the statement
                $domain_update->execute();

                // Checks whether the row actually existed / changed
                if ($domain_update->rowCount() === 0) {
                    return "No domain updated (not found or unchanged)";
                }

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }

        // Method to delete the domain
        public function delete_domain(int $domain_id): string
        {
            try {
                // Statement to delete the domain entry
                $domain_delete = $this->db->prepare("DELETE FROM domains WHERE id = ?");

                // Executes the statement
                $domain_delete->execute();

                // Checks whether a row was actually deleted
                if ($domain_delete->rowCount() === 0) {
                    return "No domain deleted (not found)";
                }

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }
    }