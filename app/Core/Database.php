<?php 
    namespace Core;

    // Brings in the PDO class and PDOException class from the global namespace
    use PDO;
    use PDOException;

    class Database
    {
        // Singleton instance of the PDO connection
        private static ?PDO $conn = null;

        // Establishes a connection to the PostgreSQL database using PDO
        public static function connect(): PDO
        {
            // If the connection is already established, return it
            if (self::$conn === null) {
                // Determine password directly or from file path secret
                $passwordPath = $_ENV['DB_PASSWORD_FILE'] ?? getenv('DB_PASSWORD_FILE') ?: getenv('DB_PASSWORD');
                
                // Read the password from the file if it exists, otherwise use the environment variable
                $password = '';
                if ($passwordPath && file_exists($passwordPath)) {
                    $password = trim(file_get_contents($passwordPath));
                } elseif (isset($_ENV['DB_PASSWORD'])) {
                    $password = $_ENV['DB_PASSWORD'];
                }

                // Get database connection parameters from environment variables or use defaults
                $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
                $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
                $db   = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '';
                $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';

                // Build PostgreSQL DSN
                $dsn = "pgsql:host={$host};port={$port};dbname={$db}";

                try {
                    self::$conn = new PDO($dsn, $user, $password, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } catch (PDOException $e) {
                    die("Connection failed: " . $e->getMessage());
                }
            }

            return self::$conn;
        }
    }
?>