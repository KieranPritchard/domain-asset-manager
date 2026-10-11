<?php 
    // Brings in the the test case and the models
    use PHPUnit\Framework\TestCase;
    use App\Models\Records;
    use App\Models\Subdomains;
    use App\Models\Domains;
    use App\Models\Auth;

    // RecordsTest class
    class RecordsTest extends TestCase
    {
        // Connection used to clean the database between tests
        private static PDO $pdo;

        // The Records model being tested
        private Records $records;

        // The id of the user that owns the test domain
        private int $user_id;

        // The id of the domain that the test subdomain belongs to
        private int $domain_id;

        // The id of the subdomain that the test records belong to
        private int $subdomain_id;

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

        // Runs before every test: empties the tables, then adds a user, a domain and a subdomain
        protected function setUp(): void
        {
            // Removes every user (and their domains, subdomains and records) so each test starts clean
            self::$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");

            // Creates the model for this test
            $this->records = new Records();

            // Adds the user that owns the domain
            $auth = new Auth();
            $auth->register_user("domain_owner", "password123", "password123");

            // Looks up the id of the user that was just created
            $find_user = self::$pdo->prepare("SELECT id FROM users WHERE username = ?");
            $find_user->execute(["domain_owner"]);
            $this->user_id = (int) $find_user->fetchColumn();

            // Adds the domain the subdomain belongs to
            $domains = new Domains();
            $domains->add_domain("example.com", "Namecheap", $this->user_id);

            // Looks up the id of the domain that was just created
            $find_domain = self::$pdo->prepare("SELECT id FROM domains WHERE name = ?");
            $find_domain->execute(["example.com"]);
            $this->domain_id = (int) $find_domain->fetchColumn();

            // Adds the subdomain the records belong to
            $subdomains = new Subdomains();
            $subdomains->add_subdomain("www.example.com", $this->domain_id);

            // Looks up the id of the subdomain that was just created
            $find_subdomain = self::$pdo->prepare("SELECT id FROM subdomains WHERE fqdn = ?");
            $find_subdomain->execute(["www.example.com"]);
            $this->subdomain_id = (int) $find_subdomain->fetchColumn();

            // Stops the tests early if the setup data was not created
            $this->assertGreaterThan(0, $this->user_id, "Test user was not created");
            $this->assertGreaterThan(0, $this->domain_id, "Test domain was not created");
            $this->assertGreaterThan(0, $this->subdomain_id, "Test subdomain was not created");
        }

        // Runs after every test: clears the session so logins don't leak between tests
        protected function tearDown(): void
        {
            $_SESSION = [];
        }

        // Adds a record and returns its id so update and delete tests can use it
        private function add_test_record(string $type = "A", string $value = "192.0.2.1"): int
        {
            $this->records->add_record($type, $value, 3600, 0, $this->subdomain_id);

            // Gets the subdomain's records and takes the first one
            $rows = $this->records->get_records([$this->subdomain_id]);
            return (int) $rows[0]["id"];
        }

        // Tests the get_records method returns the records that were added
        public function test_get_records()
        {
            $this->records->add_record("A", "192.0.2.1", 3600, 0, $this->subdomain_id);
            $this->records->add_record("MX", "mail.example.com", 3600, 10, $this->subdomain_id);

            // Test case where the subdomain has two records
            $result = $this->records->get_records([$this->subdomain_id]);
            $this->assertIsArray($result);
            $this->assertCount(2, $result);
        }

        // Tests the get_records method across more than one subdomain
        public function test_get_records_multiple_subdomains()
        {
            // Adds a second subdomain
            $subdomains = new Subdomains();
            $subdomains->add_subdomain("mail.example.com", $this->domain_id);

            // Looks up the id of the second subdomain
            $find_subdomain = self::$pdo->prepare("SELECT id FROM subdomains WHERE fqdn = ?");
            $find_subdomain->execute(["mail.example.com"]);
            $second_subdomain_id = (int) $find_subdomain->fetchColumn();

            $this->records->add_record("A", "192.0.2.1", 3600, 0, $this->subdomain_id);
            $this->records->add_record("A", "192.0.2.2", 3600, 0, $second_subdomain_id);

            // Test case where one call asks for both subdomains
            $result = $this->records->get_records([$this->subdomain_id, $second_subdomain_id]);
            $this->assertCount(2, $result);

            // Test case where only the first subdomain is asked for
            $result = $this->records->get_records([$this->subdomain_id]);
            $this->assertCount(1, $result);
        }

        // Tests the get_records method when the subdomain has no records
        public function test_get_records_none_added()
        {
            // Test case where the subdomain has no records
            $result = $this->records->get_records([$this->subdomain_id]);
            $this->assertSame([], $result);
        }

        // Tests the get_records method when no subdomain ids are given
        public function test_get_records_no_subdomain_ids()
        {
            // Test case where the list is empty, so no query should run
            $result = $this->records->get_records([]);
            $this->assertSame([], $result);
        }

        // Tests the add_record method
        public function test_add_record()
        {
            // Test case where the record is valid
            $result = $this->records->add_record("A", "192.0.2.1", 3600, 0, $this->subdomain_id);
            $this->assertEquals("", $result);

            // Checks the values were saved
            $rows = $this->records->get_records([$this->subdomain_id]);
            $this->assertCount(1, $rows);
            $this->assertEquals("192.0.2.1", $rows[0]["value"]);
            $this->assertEquals(3600, $rows[0]["ttl"]);
        }

        // Tests the add_record method when the subdomain does not exist
        public function test_add_record_subdomain_does_not_exist()
        {
            // Test case where the foreign key points at nothing, so the database rejects it
            $result = $this->records->add_record("A", "192.0.2.1", 3600, 0, 9999);
            $this->assertNotEquals("", $result);

            // Checks nothing was saved
            $this->assertSame([], $this->records->get_records([$this->subdomain_id]));
        }

        // Tests that a failed add does not leave a transaction open
        public function test_failed_add_does_not_break_next_call()
        {
            $this->records->add_record("A", "192.0.2.1", 3600, 0, 9999);

            // Test case where a normal call follows the failed one
            $result = $this->records->add_record("A", "192.0.2.1", 3600, 0, $this->subdomain_id);
            $this->assertEquals("", $result);
        }

        // Tests the update_record method
        public function test_update_record()
        {
            $record_id = $this->add_test_record();

            // Test case where the record exists
            $result = $this->records->update_record($record_id, "A", "192.0.2.99", 300, 0);
            $this->assertEquals("", $result);

            // Checks the new values were saved
            $rows = $this->records->get_records([$this->subdomain_id]);
            $this->assertEquals("192.0.2.99", $rows[0]["value"]);
            $this->assertEquals(300, $rows[0]["ttl"]);
        }

        // Tests the update_record method when the record does not exist
        public function test_update_record_not_found()
        {
            // Test case where the record id does not exist
            $result = $this->records->update_record(9999, "A", "192.0.2.99", 300, 0);
            $this->assertEquals("No record updated (not found or unchanged)", $result);
        }

        // Tests the delete_record method
        public function test_delete_record()
        {
            $record_id = $this->add_test_record();

            // Test case where the record exists
            $result = $this->records->delete_record($record_id);
            $this->assertEquals("", $result);

            // Checks the record is really gone
            $this->assertSame([], $this->records->get_records([$this->subdomain_id]));
        }

        // Tests the delete_record method when the record does not exist
        public function test_delete_record_not_found()
        {
            // Test case where the record id does not exist
            $result = $this->records->delete_record(9999);
            $this->assertEquals("No record deleted (not found)", $result);
        }

        // Tests that a failed delete does not leave a transaction open
        public function test_failed_delete_does_not_break_next_call()
        {
            $this->records->delete_record(9999);

            // Test case where a normal call follows the failed one
            $result = $this->records->add_record("A", "192.0.2.1", 3600, 0, $this->subdomain_id);
            $this->assertEquals("", $result);
        }
    }
?>