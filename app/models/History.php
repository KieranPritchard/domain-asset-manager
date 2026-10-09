<?php
    namespace App\Models;

    use Core\Model;

    // Record history model
    class RecordHistory extends Model
    {
        // Change types allowed by change_type_enum
        private const CHANGE_TYPES = ["added", "removed", "modified"];

        // Method to get the history for a list of subdomains, newest first
        public function get_history(array $subdomain_ids, int $limit = 100): string|array
        {
            try {
                // Nothing to look up, and "IN ()" is invalid SQL
                if (empty($subdomain_ids)) {
                    return [];
                }

                // Keeps the limit sensible, and cast to int so it's safe to put in the query
                $limit = max(1, min($limit, 1000));

                // Prepares a statement to get the history
                $get_history = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/history/get_history.sql'));

                // Executes the statement
                $get_history->execute(array_merge(array_map("intval", $subdomain_ids), [$limit]));

                // Fetches all rows as an associative array
                return $get_history->fetchAll();
            } catch (\Throwable $err) {
                // Returns an error
                return (string) $err;
            }
        }

        // Method to add a history entry
        public function add_history(
            int $domain_id,
            int $subdomain_id,
            string $record_type,
            ?string $old_value,
            ?string $new_value,
            string $change_type
        ): string {
            // Checks the change type before it reaches the database enum
            if (!in_array($change_type, self::CHANGE_TYPES, true)) {
                return "Invalid change type";
            }

            // If the caller already started a transaction (e.g. saving a record), join it
            // instead of starting a new one, so the change and its history commit together
            $owns_transaction = !$this->db->inTransaction();

            try {
                if ($owns_transaction) {
                    $this->db->beginTransaction();
                }

                // Adds the history entry
                $add_history = $this->db->prepare(file_get_contents(__DIR__ . '/../../database/history/add_history.sql'));

                // Executes the statement
                $add_history->execute([$domain_id, $subdomain_id, $record_type, $old_value, $new_value, $change_type]);

                if ($owns_transaction) {
                    $this->db->commit();
                }

                return "";
            } catch (\Throwable $err) {
                // Only rolls back what this method started, the caller handles its own
                if ($owns_transaction && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                // Returns an error
                return (string) $err;
            }
        }
    }
?>