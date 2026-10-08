<?php 
    namespace App\Models;

    use Core\Model;

    // Auth class
    class Auth extends Model
    {
        // Method to register user
        public function register_user(string $username, string $password, string $confirm_password): string
        {
            // Prepares a statement to check if user is registered
            $check_user_exists_stmt = $this->db->prepare("SELECT * FROM users WHERE username=(?)");

            // Executes the statement
            $check_user_exists_stmt->execute([$username]);

            // Checks the number of rows
            if ($check_user_exists_stmt->rowCount() == 1) {
                return "User already exists.";
            }

            // Checks if the passwords do not match
            if ($password !== $confirm_password) {
                return "Passwords don't match";
            }

            // Hashes the password
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

            // Stores the statement to add the user to the database
            $add_user_statement = $this->db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");

            // Binds the parameters
            $add_user_statement->execute([$username, $hashed_password]);

            return "";
        }

        // Method to login the user
        public function login_user(string $username, string $password): string
        {
            // Statement to find the user
            $find_user_statement = $this->db->prepare("SELECT * FROM users WHERE username = (?)");

            // Binds the parameters
            $find_user_statement->execute([$username]);

            // Checks the number of rows
            if ($find_user_statement->rowCount() !== 1) {
                return "Username/password do not match.";
            }

            // Fetches the row
            $row = $find_user_statement->fetch();

            // Verifies the password
            if (!password_verify($password, $row["password_hash"])) {
                return "Username/password do not match.";
            }

            // Sets the sessions
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["loggedin"] = true;

            return "";
        }

        // Method to check if the user is logged in
        public function is_loggedin():bool
        {
            // Checks if the user not is logged in
            if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"]) {
                return false;
            }

            return true;
        }

        // Method to logout user
        public function logout():void
        {
            session_unset();
            session_destroy();
        }
    }
?>