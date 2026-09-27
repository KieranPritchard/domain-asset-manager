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

                $get_user_domains->bind_param("i", $id);

                // Executes the statement
                $get_user_domains->execute();

                // Stores the result
                $result = $get_user_domains->get_result();

                // Fetches all rows as an associative array
                $domains = $result->fetch_all(MYSQLI_ASSOC);

                // Closes the statement and returns the domains
                $get_user_domains->close();

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

                // Binds the domain parameters
                $check_domain_exists->bind_param("s", $domain_name);

                // Executes the statement
                $check_domain_exists->execute();

                // Stores the result
                $check_domain_exists->store_result();

                // Checks if the domain exists
                if ($check_domain_exists->num_rows == 1) {
                    $check_domain_exists->close();
                    return "Domain already exists";
                }

                $check_domain_exists->close();

                // Adds the domain
                $add_domain = $this->db->prepare("INSERT INTO domains (name, user_id, registrar) VALUES (?, ?, ?)");

                // Binds the parameters
                $add_domain->bind_param("sis", $domain_name, $user_id, $registar);

                // Executes and closes
                $add_domain->execute();
                $add_domain->close();

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
                $domain_update->bind_param("ssi", $domain_name, $registar, $domain_id);

                // Executes the statement
                $domain_update->execute();

                // Checks whether the row actually existed / changed
                if ($domain_update->affected_rows === 0) {
                    $domain_update->close();
                    return "No domain updated (not found or unchanged)";
                }

                $domain_update->close();

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

                // Binds the parameters
                $domain_delete->bind_param("i", $domain_id);

                // Executes the statement
                $domain_delete->execute();

                // Checks whether a row was actually deleted
                if ($domain_delete->affected_rows === 0) {
                    $domain_delete->close();
                    return "No domain deleted (not found)";
                }

                $domain_delete->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }
    }