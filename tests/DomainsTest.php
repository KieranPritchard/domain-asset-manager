<?php 
    // Brings in the the test case and the models
    use PHPUnit\Framework\TestCase;
    use App\Models\Domains;
    use App\Models\Auth;

    // DomainsTest class
    class DomainsTest extends TestCase
    {
        // Connection used to clean the database between tests
        private static PDO $pdo;

        // The Domains model being tested
        private Domains $domains;

        // The id of the user that owns the test domains
        private int $user_id;

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

        // Runs before every test: empties the tables, then adds one known user
        protected function setUp(): void
        {
            // Removes every user (and their domains) so each test starts from a clean state
            self::$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");

            // Creates the model for this test
            $this->domains = new Domains();

            // Adds the user that owns the domains
            $auth = new Auth();
            $auth->register_user("domain_owner", "password123", "password123");

            // Looks up the id of the user that was just created
            $find_user = self::$pdo->prepare("SELECT id FROM users WHERE username = ?");
            $find_user->execute(["domain_owner"]);
            $this->user_id = (int) $find_user->fetchColumn();

            // Stops the tests early if the user was not created
            $this->assertGreaterThan(0, $this->user_id, "Test user was not created");
        }

        // Runs after every test: clears the session so logins don't leak between tests
        protected function tearDown(): void
        {
            $_SESSION = [];
        }

        // Adds a domain and returns its id so update and delete tests can use it
        private function add_test_domain(string $name = "example.com"): int
        {
            $this->domains->add_domain($name, "Namecheap", $this->user_id);

            // Gets the user's domains and takes the first one
            $rows = $this->domains->get_domains($this->user_id);
            return (int) $rows[0]["id"];
        }

        // Tests the get_domains method returns the domains that were added
        public function test_get_all_domains()
        {
            $this->domains->add_domain("example.com", "Namecheap", $this->user_id);
            $this->domains->add_domain("example.org", "GoDaddy", $this->user_id);

            // Test case where the user has two domains
            $result = $this->domains->get_domains($this->user_id);
            $this->assertIsArray($result);
            $this->assertCount(2, $result);
        }

        // Tests the get_domains method when the user has no domains
        public function test_get_domains_none_added()
        {
            // Test case where the user has no domains
            $result = $this->domains->get_domains($this->user_id);
            $this->assertSame([], $result);
        }

        // Tests the get_domains method with an invalid id
        public function test_get_domains_invalid_id()
        {
            // Test case where the id is zero
            $this->assertEquals("Invalid ID", $this->domains->get_domains(0));

            // Test case where the id is negative
            $this->assertEquals("Invalid ID", $this->domains->get_domains(-1));
        }

        // Tests the add_domain method
        public function test_add_domain()
        {
            // Test case where the domain is new
            $result = $this->domains->add_domain("example.com", "Namecheap", $this->user_id);
            $this->assertEquals("", $result);
        }

        // Tests the add_domain method when the domain already exists
        public function test_add_domain_already_exists()
        {
            $this->domains->add_domain("example.com", "Namecheap", $this->user_id);

            // Test case where the domain is added a second time
            $result = $this->domains->add_domain("example.com", "Namecheap", $this->user_id);
            $this->assertEquals("Domain already exists", $result);
        }

        // Tests the update_domain method
        public function test_update_domain()
        {
            $domain_id = $this->add_test_domain();

            // Test case where the domain exists
            $result = $this->domains->update_domain($domain_id, "updated.com", "GoDaddy");
            $this->assertEquals("", $result);
        }

        // Tests the update_domain method when the domain does not exist
        public function test_update_domain_not_found()
        {
            // Test case where the domain id does not exist
            $result = $this->domains->update_domain(9999, "updated.com", "GoDaddy");
            $this->assertEquals("No domain updated (not found or unchanged)", $result);
        }

        // Tests the delete_domain method
        public function test_delete_domain()
        {
            $domain_id = $this->add_test_domain();

            // Test case where the domain exists
            $result = $this->domains->delete_domain($domain_id);
            $this->assertEquals("", $result);

            // Checks the domain is really gone
            $this->assertSame([], $this->domains->get_domains($this->user_id));
        }

        // Tests the delete_domain method when the domain does not exist
        public function test_delete_domain_not_found()
        {
            // Test case where the domain id does not exist
            $result = $this->domains->delete_domain(9999);
            $this->assertEquals("No domain deleted (not found)", $result);
        }

        // Tests that a failed delete does not leave a transaction open
        public function test_failed_delete_does_not_break_next_call()
        {
            $this->domains->delete_domain(9999);

            // Test case where a normal call follows the failed one
            $result = $this->domains->add_domain("example.com", "Namecheap", $this->user_id);
            $this->assertEquals("", $result);
        }
    }
?>