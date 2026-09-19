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
            // Checks if the database attribute is
            if (self::$conn === null) {
                // Creates a new database object
                self::$conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_DATABASE"]);

                // Checks for a connection error
                if (self::$conn->connect_error) {
                    die("Connection failed: " . self::$conn->connect_error);
                }
            }

            return self::$conn;
        }
    }
?>