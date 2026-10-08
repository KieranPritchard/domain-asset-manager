<?php
    namespace App\Controllers;

    use Core\Controller;
    use App\Models\Auth;
    use Core\Database;

    class IndexController extends Controller
    {
        // Private method to build the database on first run
        private function build_db() {
            $db = new Database()->connect();

            $users_table = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(255) NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";

            $domains_table = "
                -- Top-level domains being tracked
                CREATE TABLE IF NOT EXISTS domains (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    name VARCHAR(255) NOT NULL UNIQUE,        -- e.g. 'example.com'
                    registrar VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
            ";

            $subdomain_table = "
                -- Discovered/tracked subdomains
                CREATE TABLE IF NOT EXISTS subdomains (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    domain_id INT NOT NULL,
                    fqdn VARCHAR(255) NOT NULL UNIQUE,        -- e.g. 'api.example.com'
                    status ENUM('active', 'inactive', 'unknown') DEFAULT 'unknown',
                    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
                );
            ";

            $records_table = "
                -- DNS records for each subdomain (a subdomain can have many record types)
                CREATE TABLE IF NOT EXISTS dns_records (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    domain_id INT NOT NULL,
                    record_type ENUM('A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SOA', 'SRV') NOT NULL,
                    value VARCHAR(512) NOT NULL,              -- IP, target hostname, TXT content, etc.
                    ttl INT,
                    priority INT NULL,                        -- used by MX/SRV, null otherwise
                    last_verified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
                );
            ";

            $history_table = "
                -- History log so you can see what changed and when (useful for detecting takeovers/drift)
                CREATE TABLE IF NOT EXISTS record_history (    
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    domain_id INT NOT NULL,
                    subdomain_id INT NOT NULL,
                    record_type VARCHAR(10) NOT NULL,
                    old_value VARCHAR(512),
                    new_value VARCHAR(512),
                    change_type ENUM('added', 'removed', 'modified') NOT NULL,
                    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
                    FOREIGN KEY (subdomain_id) REFERENCES subdomains(id) ON DELETE CASCADE
                );
            ";

            try{
                // Runs the queries
                $db->query($users_table);
                $db->query($domains_table);
                $db->query($subdomain_table);
                $db->query($records_table);
                $db->query($history_table);
            } catch (\Throwable $e) {
                error_log("build_db failed: " . $e->getMessage());
                throw $e;
            }
        }

        // Index command
        public function index(): void
        {
            // Builds the database
            $this->build_db();

            // Stores wheter the user is logged in or not
            $loggedin = new Auth()->is_loggedin();

            // Checks if the user is loggedin
            if ($loggedin) {
                header("location: /home");
                exit;
            }

            header("location: /login");
            exit;
        }
    }
?>