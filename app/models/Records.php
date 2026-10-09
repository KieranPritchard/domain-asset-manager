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

                $this->db->beginTransaction();

                // Builds one parameter placeholder per subdomain ID
                $placeholders = implode(",", array_fill(0, count($subdomain_ids), "?"));
                $get_records = $this->db->prepare(
                    "SELECT * FROM dns_records WHERE subdomain_id IN ($placeholders)"
                );

                // Executes the statement
                $get_records->execute($subdomain_ids);

                // Fetches all rows as an associative array
                $records = $get_records->fetchAll();

                $this->db->commit();

                return $records;
            } catch (\Throwable $err) {
                $this->db->rollBack();
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add a record
        public function add_record(string $type, string $value, int $ttl, int $priority, int $subdomain_id): string
        {
            try {
                $this->db->beginTransaction();

                // Adds the record
                $add_record = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/records/add_record.sql'));

                // Binds the parameters
                $add_record->execute([$type, $subdomain_id, $value, $ttl, $priority]);

                $this->db->commit();

                return "";
            } catch (\Throwable $err) {
                $this->db->rollBack();
                // Returns an error
                return (string) $err;
            }
        }

        // Method to update a record
        public function update_record(int $record_id, string $type, string $value, int $ttl, int $priority): string
        {
            try {
                $this->db->beginTransaction();

                // Statement to update the record entry
                $record_update = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/records/update_record.sql'));

                // Executes the statement
                $record_update->execute([$type, $value, $ttl, $priority, $record_id]);

                $this->db->commit();

                // Checks whether the row actually existed / changed
                if ($record_update->rowCount() === 0) {
                    return "No record updated (not found or unchanged)";
                }

                return "";
            } catch (\Throwable $err) {
                $this->db->rollBack();
                return (string) $err;
            }
        }

        // Method to delete a record
        public function delete_record(int $record_id): string
        {
            try {
                $this->db->beginTransaction();

                // Statement to delete the record entry
                $record_delete = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/records/delete_record.sql'));

                // Executes the statement
                $record_delete->execute([$record_id]);

                // Checks whether a row was actually deleted
                if ($record_delete->rowCount() === 0) {
                    return "No record deleted (not found)";
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