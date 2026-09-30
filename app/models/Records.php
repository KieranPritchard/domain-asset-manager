<?php 
    namespace App\Models;

    use Core\Model;

    // Records class
    class Records extends Model
    {
        // Method to get the records for a list of subdomains
        public function get_records(array $subdomain_ids): string | array
        {
            try {
                // Nothing to look up, and "IN ()" is invalid SQL
                if (empty($subdomain_ids)) {
                    return [];
                }

                // Builds one placeholder per id, e.g. "?,?,?"
                $placeholders = implode(",", array_fill(0, count($subdomain_ids), "?"));

                // Prepares a statement to get the records
                $get_records = $this->db->prepare("SELECT * FROM dns_records WHERE subdomain_id IN ($placeholders)");

                // Binds every id as an integer
                $types = str_repeat("i", count($subdomain_ids));
                $get_records->bind_param($types, ...array_map("intval", $subdomain_ids));

                // Executes the statement
                $get_records->execute();

                // Stores the result
                $result = $get_records->get_result();

                // Fetches all rows as an associative array
                $records = $result->fetch_all(MYSQLI_ASSOC);

                // Closes the statement and returns the records
                $get_records->close();

                return $records;
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add a record
        public function add_record(string $type, string $value, int $ttl, int $priority, int $subdomain_id): string
        {
            try {
                // Adds the record
                $add_record = $this->db->prepare("INSERT INTO dns_records (record_type, subdomain_id, value, ttl, priority) VALUES (?, ?, ?, ?, ?)");

                // Binds the parameters
                $add_record->bind_param("sisii", $type, $subdomain_id, $value, $ttl, $priority);

                // Executes and closes
                $add_record->execute();
                $add_record->close();

                return "";
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update a record
        public function update_record(int $record_id, string $type, string $value, int $ttl, int $priority): string
        {
            try {
                // Statement to update the record entry
                $record_update = $this->db->prepare("UPDATE dns_records SET record_type = ?, value = ?, ttl = ?, priority = ? WHERE id = ?");

                // Binds the parameters
                $record_update->bind_param("ssiii", $type, $value, $ttl, $priority, $record_id);

                // Executes the statement
                $record_update->execute();

                // Checks whether the row actually existed / changed
                if ($record_update->affected_rows === 0) {
                    $record_update->close();
                    return "No record updated (not found or unchanged)";
                }

                $record_update->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }

        // Method to delete a record
        public function delete_record(int $record_id): string
        {
            try {
                // Statement to delete the record entry
                $record_delete = $this->db->prepare("DELETE FROM dns_records WHERE id = ?");

                // Binds the parameters
                $record_delete->bind_param("i", $record_id);

                // Executes the statement
                $record_delete->execute();

                // Checks whether a row was actually deleted
                if ($record_delete->affected_rows === 0) {
                    $record_delete->close();
                    return "No record deleted (not found)";
                }

                $record_delete->close();

                return "";
            } catch (\Throwable $err) {
                return (string) $err;
            }
        }
    }
?>