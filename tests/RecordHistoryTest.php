<?php 
    // Brings in the the test case and the models
    use PHPUnit\Framework\TestCase;
    use PHPUnit\Framework\Attributes\DataProvider;
    use App\Models\RecordHistory;
    use App\Models\Subdomains;
    use App\Models\Domains;
    use App\Models\Auth;

    // RecordHistoryTest class
    class RecordHistoryTest extends TestCase
    {
        // The table the history is stored in
        private const HISTORY_TABLE = "record_history";

        // Connection used to clean the database and check the results
        private static PDO $pdo;

        // The RecordHistory model being tested
        private RecordHistory $history;

        // The ids of the rows the history entries belong to
        private int $user_id;
        private int $domain_id;
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
            // Removes every user (and everything that belongs to them) so each test starts clean
            self::$pdo->exec("TRUNCATE TABLE users RESTART IDENTITY CASCADE");

            // Creates the model for this test
            $this->history = new RecordHistory();

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

            // Adds the subdomain the history belongs to
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

        // Gets the database connection the model is using, so the tests can start transactions on it
        private function model_db(): PDO
        {
            $property = new ReflectionProperty(\Core\Model::class, "db");
            return $property->getValue($this->history);
        }

        // Counts the rows in the history table
        private function count_history(): int
        {
            return (int) self::$pdo->query("SELECT COUNT(*) FROM " . self::HISTORY_TABLE)->fetchColumn();
        }

        // Adds a basic history entry
        private function add_test_history(string $old = "192.0.2.1", string $new = "192.0.2.2"): string
        {
            return $this->history->add_history(
                $this->domain_id,
                $this->subdomain_id,
                "A",
                $old,
                $new,
                "modified"
            );
        }

        // Provides every allowed change type
        public static function change_type_provider(): array
        {
            return [
                "added"    => ["added"],
                "removed"  => ["removed"],
                "modified" => ["modified"],
            ];
        }

        // Tests the add_history method for each allowed change type
        #[DataProvider('change_type_provider')]
        public function test_add_history(string $change_type)
        {
            // Test case where the change type is allowed
            $result = $this->history->add_history($this->domain_id, $this->subdomain_id, "A", "192.0.2.1", "192.0.2.2", $change_type);
            $this->assertEquals("", $result);
            $this->assertEquals(1, $this->count_history());
        }

        // Tests the add_history method saves the values
        public function test_add_history_saves_values()
        {
            $this->add_test_history("192.0.2.1", "192.0.2.2");

            // Checks the values were saved
            $rows = $this->history->get_history([$this->subdomain_id]);
            $this->assertCount(1, $rows);
            $this->assertEquals("A", $rows[0]["record_type"]);
            $this->assertEquals("192.0.2.1", $rows[0]["old_value"]);
            $this->assertEquals("192.0.2.2", $rows[0]["new_value"]);
            $this->assertEquals("modified", $rows[0]["change_type"]);
        }

        // Tests the add_history method with null values
        public function test_add_history_null_values()
        {
            // Test case where a record was added, so there is no old value
            $result = $this->history->add_history($this->domain_id, $this->subdomain_id, "A", null, "192.0.2.1", "added");
            $this->assertEquals("", $result);

            // Test case where a record was removed, so there is no new value
            $result = $this->history->add_history($this->domain_id, $this->subdomain_id, "A", "192.0.2.1", null, "removed");
            $this->assertEquals("", $result);

            $this->assertEquals(2, $this->count_history());
        }

        // Tests the add_history method with a change type that is not allowed
        public function test_add_history_invalid_change_type()
        {
            // Test case where the change type is not in the list
            $result = $this->history->add_history($this->domain_id, $this->subdomain_id, "A", "192.0.2.1", "192.0.2.2", "renamed");
            $this->assertEquals("Invalid change type", $result);

            // Checks nothing was saved
            $this->assertEquals(0, $this->count_history());
        }

        // Tests the add_history method is strict about the change type
        public function test_add_history_change_type_is_case_sensitive()
        {
            // Test case where the case is wrong, which the database enum would also reject
            $result = $this->history->add_history($this->domain_id, $this->subdomain_id, "A", "192.0.2.1", "192.0.2.2", "ADDED");
            $this->assertEquals("Invalid change type", $result);
        }

        // Tests the add_history method when the subdomain does not exist
        public function test_add_history_subdomain_does_not_exist()
        {
            // Test case where the foreign key points at nothing, so the database rejects it
            $result = $this->history->add_history($this->domain_id, 9999, "A", "192.0.2.1", "192.0.2.2", "modified");
            $this->assertNotEquals("", $result);
            $this->assertEquals(0, $this->count_history());
        }

        // Tests that a failed add does not leave a transaction open
        public function test_failed_add_does_not_break_next_call()
        {
            $this->history->add_history($this->domain_id, 9999, "A", "192.0.2.1", "192.0.2.2", "modified");

            // Checks the model cleaned up after itself
            $this->assertFalse($this->model_db()->inTransaction());

            // Test case where a normal call follows the failed one
            $this->assertEquals("", $this->add_test_history());
        }

        // Tests that add_history does not leave its own transaction open
        public function test_add_history_commits_when_it_owns_the_transaction()
        {
            $this->add_test_history();

            // Checks the transaction was committed and closed
            $this->assertFalse($this->model_db()->inTransaction());
        }

        // Tests that add_history joins a transaction the caller started
        public function test_add_history_joins_callers_transaction()
        {
            $db = $this->model_db();

            // Test case where the caller starts a transaction and then rolls it back
            $db->beginTransaction();
            $this->assertEquals("", $this->add_test_history());

            // Checks the model did not commit the caller's transaction
            $this->assertTrue($db->inTransaction());
            $db->rollBack();

            // Checks the history went away with the rolled back change
            $this->assertEquals(0, $this->count_history());
        }

        // Tests that the history is kept when the caller commits
        public function test_add_history_kept_when_caller_commits()
        {
            $db = $this->model_db();

            // Test case where the caller starts a transaction and then commits it
            $db->beginTransaction();
            $this->add_test_history();
            $db->commit();

            $this->assertEquals(1, $this->count_history());
        }

        // Tests that a failure inside the caller's transaction does not roll it back
        public function test_add_history_failure_does_not_roll_back_callers_transaction()
        {
            $db = $this->model_db();

            // Test case where the history insert fails inside the caller's transaction
            $db->beginTransaction();
            $result = $this->history->add_history($this->domain_id, 9999, "A", "192.0.2.1", "192.0.2.2", "modified");
            $this->assertNotEquals("", $result);

            // Checks the model left the transaction for the caller to handle
            $this->assertTrue($db->inTransaction());
            $db->rollBack();
        }

        // Tests the get_history method returns the entries that were added
        public function test_get_history()
        {
            $this->add_test_history("192.0.2.1", "192.0.2.2");
            $this->add_test_history("192.0.2.2", "192.0.2.3");

            // Test case where the subdomain has two history entries
            $result = $this->history->get_history([$this->subdomain_id]);
            $this->assertIsArray($result);
            $this->assertCount(2, $result);
        }

        // Tests the get_history method returns the newest entry first
        public function test_get_history_newest_first()
        {
            $this->add_test_history("192.0.2.1", "192.0.2.2");

            // Waits so the two entries get different timestamps
            usleep(20000);

            $this->add_test_history("192.0.2.2", "192.0.2.3");

            // Test case where the order matters
            $result = $this->history->get_history([$this->subdomain_id]);
            $this->assertEquals("192.0.2.3", $result[0]["new_value"]);
            $this->assertEquals("192.0.2.2", $result[1]["new_value"]);
        }

        // Tests the get_history method with a limit
        public function test_get_history_limit()
        {
            $this->add_test_history("1", "2");
            $this->add_test_history("2", "3");
            $this->add_test_history("3", "4");

            // Test case where the limit is lower than the number of entries
            $result = $this->history->get_history([$this->subdomain_id], 2);
            $this->assertCount(2, $result);
        }

        // Tests the get_history method keeps the limit sensible
        public function test_get_history_limit_is_clamped()
        {
            $this->add_test_history("1", "2");
            $this->add_test_history("2", "3");

            // Test case where the limit is zero, which becomes one
            $this->assertCount(1, $this->history->get_history([$this->subdomain_id], 0));

            // Test case where the limit is negative, which also becomes one
            $this->assertCount(1, $this->history->get_history([$this->subdomain_id], -5));

            // Test case where the limit is huge, which is capped and still works
            $this->assertCount(2, $this->history->get_history([$this->subdomain_id], 999999));
        }

        // Tests the get_history method when the subdomain has no history
        public function test_get_history_none_added()
        {
            // Test case where the subdomain has no history
            $result = $this->history->get_history([$this->subdomain_id]);
            $this->assertSame([], $result);
        }

        // Tests the get_history method when no subdomain ids are given
        public function test_get_history_no_subdomain_ids()
        {
            // Test case where the list is empty, so no query should run
            $result = $this->history->get_history([]);
            $this->assertSame([], $result);
        }

        // Tests the get_history method across more than one subdomain
        public function test_get_history_multiple_subdomains()
        {
            // Adds a second subdomain
            $subdomains = new Subdomains();
            $subdomains->add_subdomain("mail.example.com", $this->domain_id);

            // Looks up the id of the second subdomain
            $find_subdomain = self::$pdo->prepare("SELECT id FROM subdomains WHERE fqdn = ?");
            $find_subdomain->execute(["mail.example.com"]);
            $second_subdomain_id = (int) $find_subdomain->fetchColumn();

            $this->add_test_history("1", "2");
            $this->history->add_history($this->domain_id, $second_subdomain_id, "A", "3", "4", "modified");

            // Test case where one call asks for both subdomains
            $result = $this->history->get_history([$this->subdomain_id, $second_subdomain_id]);
            $this->assertIsArray($result);
            $this->assertCount(2, $result);

            // Test case where only the first subdomain is asked for
            $this->assertCount(1, $this->history->get_history([$this->subdomain_id]));
        }
    }
?>