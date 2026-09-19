<?php 
    namespace Apps\Models;

    use Core\Model;

    // Auth class
    class Auth extends Model
    {
        // Method to register user
        public function register_user(string $username, string $password, string $confirm_password):string
        {
            // Prepares a statement to check if user is registered
            $check_user_exists_stmt = $this->db->prepare("SELECT * FROM users WHERE username=(?)");

            // Binds the parameters
            $check_user_exists_stmt->bind_param("s", $username);

            // Executes the statement
            $check_user_exists_stmt->execute();

            // Checks the number of rows
            if ($check_user_exists_stmt->num_rows() == 1) {
                return "User already exists.";
                exit(1);
            }

            // Checks if the passwords do not match
            if ($password !== $confirm_password) {
                return "Passwords don't match";
                exit(1);
            }

            // Hashes the password
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

            // Stores the statement to add the user to the database
            $add_user_statement = $this->db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");

            // Binds the parameters
            $add_user_statement->bind_param("ss", $username, $hashed_password);

            // Executes the statement
            $add_user_statement->execute();

            return "";
        }

        // Method to login the user
        public function login_user(string $username, string $password): string
        {
            // Statement to find the user
            $find_user_statement = $this->db->prepare("SELECT * FROM users WHERE username = (?)");

            // Binds the parameters
            $find_user_statement->bind_param("s", $username);

            // Executes the statement
            $find_user_statement->execute();

            // Checks the number of rows
            if ($find_user_statement->num_rows() !== 1) {
                return "Username/password do not match.";
                exit(1);
            }

            // Stores the result
            $row = $find_user_statement->get_result()->fetch_assoc();

            // Verifies the password
            if (!password_verify($password, $row["password_hash"])) {
                return "Username/password do not match.";
                exit(1);
            }

            // Sets the sessions
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["loggedin"] = true;

            return "";
        }
    }
?>