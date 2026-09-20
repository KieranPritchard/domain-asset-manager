<?php 
    namespace Core;

    use mysqli;

    class Database
    {
        // Sets the database as the private attribute
        private static ?mysqli $conn = null;

        // Connects to the database
        public static function connect(): mysqli
        {
            $passwordPath = getenv('DB_PASSWORD'); // this holds the *path* to the secret
            $password = trim(file_get_contents($passwordPath));

            // Checks if the database attribute is
            if (self::$conn === null) {
                // Creates a new database object
                self::$conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $password, $_ENV["DB_DATABASE"]);

                // Checks for a connection error
                if (self::$conn->connect_error) {
                    die("Connection failed: " . self::$conn->connect_error);
                }
            }

            return self::$conn;
        }
    }
?>