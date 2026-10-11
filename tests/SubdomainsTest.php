<?php 
    // Brings in the the test case and the models
    use PHPUnit\Framework\TestCase;
    use App\Models\Subdomains;
    use App\Models\Domains;
    use App\Models\Auth;

    // SubdomainsTest class
    class SubdomainsTest extends TestCase
    {
        // Connection used to clean the database between tests
        private static PDO $pdo;

        // The Subdomains model being tested
        private Subdomains $subdomains;

        // The id of the user that owns the test domain
        private int $user_id;

        // The id of the domain that the test subdomains belong to
        private int $domain_id;

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

        // Runs before every test: empties the tables, then adds a user and a domain
        protected function setUp(): void
        {
            // Removes every user (and their domains and subdomains) so each test starts clean
            self::$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");

            // Creates the model for this test
            $this->subdomains = new Subdomains();

            // Adds the user that owns the domain
            $auth = new Auth();
            $auth->register_user("domain_owner", "password123", "password123");

            // Looks up the id of the user that was just created
            $find_user = self::$pdo->prepare("SELECT id FROM users WHERE username = ?");
            $find_user->execute(["domain_owner"]);
            $this->user_id = (int) $find_user->fetchColumn();

            // Adds the domain the subdomains belong to
            $domains = new Domains();
            $domains->add_domain("example.com", "Namecheap", $this->user_id);

            // Looks up the id of the domain that was just created
            $find_domain = self::$pdo->prepare("SELECT id FROM domains WHERE name = ?");
            $find_domain->execute(["example.com"]);
            $this->domain_id = (int) $find_domain->fetchColumn();

            // Stops the tests early if the setup data was not created
            $this->assertGreaterThan(0, $this->user_id, "Test user was not created");
            $this->assertGreaterThan(0, $this->domain_id, "Test domain was not created");
        }

        // Runs after every test: clears the session so logins don't leak between tests
        protected function tearDown(): void
        {
            $_SESSION = [];
        }

        // Adds a subdomain and returns its id so update and delete tests can use it
        private function add_test_subdomain(string $fqdn = "www.example.com"): int
        {
            $this->subdomains->add_subdomain($fqdn, $this->domain_id);

            // Gets the domain's subdomains and takes the first one
            $rows = $this->subdomains->get_subdomains([$this->domain_id]);
            return (int) $rows[0]["id"];
        }

        // Tests the get_subdomains method returns the subdomains that were added
        public function test_get_subdomains()
        {
            $this->subdomains->add_subdomain("www.example.com", $this->domain_id);
            $this->subdomains->add_subdomain("mail.example.com", $this->domain_id);

            // Test case where the domain has two subdomains
            $result = $this->subdomains->get_subdomains([$this->domain_id]);
            $this->assertIsArray($result);
            $this->assertCount(2, $result);
        }

        // Tests the get_subdomains method across more than one domain
        public function test_get_subdomains_multiple_domains()
        {
            // Adds a second domain
            $domains = new Domains();
            $domains->add_domain("example.org", "GoDaddy", $this->user_id);

            // Looks up the id of the second domain
            $find_domain = self::$pdo->prepare("SELECT id FROM domains WHERE name = ?");
            $find_domain->execute(["example.org"]);
            $second_domain_id = (int) $find_domain->fetchColumn();

            $this->subdomains->add_subdomain("www.example.com", $this->domain_id);
            $this->subdomains->add_subdomain("www.example.org", $second_domain_id);

            // Test case where one call asks for both domains
            $result = $this->subdomains->get_subdomains([$this->domain_id, $second_domain_id]);
            $this->assertCount(2, $result);

            // Test case where only the first domain is asked for
            $result = $this->subdomains->get_subdomains([$this->domain_id]);
            $this->assertCount(1, $result);
        }

        // Tests the get_subdomains method when the domain has no subdomains
        public function test_get_subdomains_none_added()
        {
            // Test case where the domain has no subdomains
            $result = $this->subdomains->get_subdomains([$this->domain_id]);
            $this->assertSame([], $result);
        }

        // Tests the get_subdomains method when no domain ids are given
        public function test_get_subdomains_no_domain_ids()
        {
            // Test case where the list is empty, so no query should run
            $result = $this->subdomains->get_subdomains([]);
            $this->assertSame([], $result);
        }

        // Tests the add_subdomain method
        public function test_add_subdomain()
        {
            // Test case where the subdomain is new
            $result = $this->subdomains->add_subdomain("www.example.com", $this->domain_id);
            $this->assertEquals("", $result);
        }

        // Tests the add_subdomain method when the subdomain already exists
        public function test_add_subdomain_already_exists()
        {
            $this->subdomains->add_subdomain("www.example.com", $this->domain_id);

            // Test case where the subdomain is added a second time
            $result = $this->subdomains->add_subdomain("www.example.com", $this->domain_id);
            $this->assertEquals("Subdomain already exists", $result);
        }

        // Tests the update_subdomain method
        public function test_update_subdomain()
        {
            $subdomain_id = $this->add_test_subdomain();

            // Test case where the subdomain exists
            $result = $this->subdomains->update_subdomain($subdomain_id, "blog.example.com", "active");
            $this->assertEquals("", $result);

            // Checks the new name was saved
            $rows = $this->subdomains->get_subdomains([$this->domain_id]);
            $this->assertEquals("blog.example.com", $rows[0]["fqdn"]);
        }

        // Tests the update_subdomain method when the subdomain does not exist
        public function test_update_subdomain_not_found()
        {
            // Test case where the subdomain id does not exist
            $result = $this->subdomains->update_subdomain(9999, "blog.example.com", "active");
            $this->assertEquals("No subdomain updated (not found or unchanged)", $result);
        }

        // Tests the delete_subdomain method
        public function test_delete_subdomain()
        {
            $subdomain_id = $this->add_test_subdomain();

            // Test case where the subdomain exists
            $result = $this->subdomains->delete_subdomain($subdomain_id);
            $this->assertEquals("", $result);

            // Checks the subdomain is really gone
            $this->assertSame([], $this->subdomains->get_subdomains([$this->domain_id]));
        }

        // Tests the delete_subdomain method when the subdomain does not exist
        public function test_delete_subdomain_not_found()
        {
            // Test case where the subdomain id does not exist
            $result = $this->subdomains->delete_subdomain(9999);
            $this->assertEquals("No subdomain deleted (not found)", $result);
        }

        // Tests that a failed delete does not leave a transaction open
        public function test_failed_delete_does_not_break_next_call()
        {
            $this->subdomains->delete_subdomain(9999);

            // Test case where a normal call follows the failed one
            $result = $this->subdomains->add_subdomain("www.example.com", $this->domain_id);
            $this->assertEquals("", $result);
        }
    }
?>