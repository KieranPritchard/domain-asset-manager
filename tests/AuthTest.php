<?php 
    // Brings in the the test case and the Auth class
    use PHPUnit\Framework\TestCase;
    use App\Models\Auth;

    // AuthTest class
    class AuthTest extends TestCase
    {
        // Connection used to clean the database between tests
        private static PDO $pdo;

        // The Auth model being tested
        private Auth $auth;

        // Connects once for cleaning up between tests
        public static function setUpBeforeClass(): void
        {
            // Reads the database password from the secret file
            $password = trim(file_get_contents(getenv('DB_PASSWORD_FILE')));

            // Connects to the test database
            self::$pdo = new PDO(
                "pgsql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_DATABASE'),
                getenv('DB_USER'),
                $password
            );
        }

        // Runs before every test: empties the table, then adds one known user
        protected function setUp(): void
        {
            // Removes every user so each test starts from a clean state
            self::$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");

            // Creates the model for this test
            $this->auth = new Auth();

            // Adds the user that the login and already exists tests rely on
            $this->auth->register_user("existing_user", "password123", "password123");
        }

        // Runs after every test: clears the session so logins don't leak between tests
        protected function tearDown(): void
        {
            $_SESSION = [];
        }

        // Test for register_user method
        public function testRegisterUser()
        {
            // Test case where the user is new and the passwords match
            $result = $this->auth->register_user("new_user", "Password123!", "Password123!");
            $this->assertEquals("", $result);
        }

        // Test for register_user method when passwords do not match
        public function testRegisterUserPasswordDoesNotMatch()
        {
            // Test case where passwords do not match
            $result = $this->auth->register_user("new_user", "Password123!", "Password456!");
            $this->assertEquals("Passwords don't match", $result);
        }

        // Test for register_user method when user already exists
        public function testRegisterUserAlreadyExists()
        {
            // Test case where user already exists
            $result = $this->auth->register_user("existing_user", "Password123!", "Password123!");
            $this->assertEquals("User already exists.", $result);
        }

        // Test for register_user method when no data is provided
        public function testRegisterUserNoData()
        {
            // Test case where no data is provided
            $result = $this->auth->register_user("", "", "");
            $this->assertEquals("All fields are required.", $result);
        }

        // Test for login_user method
        public function testLoginUser()
        {
            // Test case for successful login
            // "existing_user" with password "password123" is created in setUp
            $result = $this->auth->login_user("existing_user", "password123");
            $this->assertEquals("", $result);

            // Checks the session was set
            $this->assertTrue($this->auth->is_loggedin());
        }

        // Test for login_user method when username/password do not match
        public function testLoginUserUsernamePasswordDoNotMatch()
        {
            // Test case where username/password do not match
            $result = $this->auth->login_user("existing_user", "wrongpassword");
            $this->assertEquals("Username/password do not match.", $result);

            // Checks the user was not logged in
            $this->assertFalse($this->auth->is_loggedin());
        }

        // Test for login_user method when user does not exist
        public function testLoginUserUserDoesNotExist()
        {
            // Test case where user does not exist
            $result = $this->auth->login_user("nonexistent_user", "password123");
            $this->assertEquals("Username/password do not match.", $result);
        }

        // Test for login_user method when no data is provided
        public function testLoginUserNoData()
        {
            // Test case where no data is provided
            $result = $this->auth->login_user("", "");
            $this->assertEquals("Username/password do not match.", $result);
        }
    }
?>