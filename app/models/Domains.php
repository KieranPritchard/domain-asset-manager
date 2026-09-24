<?php 
    namespace App\Models;

    use Core\Model;

    // Modals Class
    class Domains extends Model
    {
        // Method to add domain
        public function add_domain(string $domain_name, string $registar, int $user_id):string
        {
            // Prepares a statement to check if the domain was already present
            $check_domain_exists = $this->db->prepare("SELECT * FROM domains WHERE name=(?)");

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
            
            // Adds the domain
            $add_domain = $this->db->prepare("INSERT INTO domains (name, user_id, registrar) VALUES (?,?,?)");

            // Binds the parameters
            $add_domain->bind_param("sis", $domain_name, $user_id, $registar);

            // Executes and closes
            $add_domain->execute();
            $add_domain->close();

            return "";
        }
    }
?>