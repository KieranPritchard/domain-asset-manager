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
                $get_user_domains = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/domains/get_domain_by_id.sql'));

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
                $check_domain_exists = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/domains/get_domain_by_name.sql'));

                // Executes the statement
                $check_domain_exists->execute([$domain_name]);

                // Checks if the domain exists
                if ($check_domain_exists->rowCount() == 1) {
                    return "Domain already exists";
                }

                // Starts a transaction
                $this->db->beginTransaction();

                // Adds the domain
                $add_domain = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/domains/add_domain.sql'));

                // Executes and closes
                $add_domain->execute([$domain_name, $user_id, $registar]);
                
                // Commits the transaction
                $this->db->commit();

                return "";
            } catch (\Throwable $err) {
                // Rolls back the transaction in case of an error
                $this->db->rollBack();

                // Returns an error
                return (string) $err;
            }
        }

        // Method to update domain
        public function update_domain(int $domain_id, string $domain_name, string $registar): string
        {
            try {
                // Begins a transaction
                $this->db->beginTransaction();

                // Statement to update the domain entry
                $domain_update = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/domains/update_domain.sql'));

                // Executes the statement with the update values
                $domain_update->execute([$domain_name, $registar, $domain_id]);

                // Checks whether the row actually existed / changed
                if ($domain_update->rowCount() === 0) {
                    return "No domain updated (not found or unchanged)";
                }

                // Commits the transaction
                $this->db->commit();

                return "";
            } catch (\Throwable $err) {
                // Rolls back the transaction in case of an error
                $this->db->rollBack();

                return (string) $err;
            }
        }

        // Method to delete the domain
        public function delete_domain(int $domain_id): string
        {
            try {
                // Begins a transaction
                $this->db->beginTransaction();

                // Statement to delete the domain entry
                $domain_delete = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/domains/delete_domain.sql'));

                // Executes the statement
                $domain_delete->execute([$domain_id]);

                // Checks whether a row was actually deleted
                if ($domain_delete->rowCount() === 0) {
                    return "No domain deleted (not found)";
                }

                // Commits the transaction
                $this->db->commit();

                return "";
            } catch (\Throwable $err) {
                // Rolls back the transaction in case of an error
                $this->db->rollBack();

                return (string) $err;
            }
        }
    }