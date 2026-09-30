<?php 
    namespace App\Models;

    use Core\Model;

    // Records class
    class Records extends Model
    {
        // Method to get the records
        public function get_records(array $domain_ids):string | array
        {
            try {
                // Builds the id list
                $ids = join(",", $domain_ids);
                
                // Prepares a statement to get the records
                $get_records = $this->db->prepare("SELECT * FROM records WHERE subdomain_id IN ?");

                $get_records->bind_param("s", $ids);

                // Stores the result
                $result = $get_records->get_result();

                // Fetches all rows as an associative array
                $records = $result->fetch_all(MYSQLI_ASSOC);

                // Closes the statement and returns the domains
                $get_records->close();

                return $records;
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }
    }
?>