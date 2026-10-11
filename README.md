# DNS Record Manager

![GitHub Created At](https://img.shields.io/github/created-at/KieranPritchard/dns_record_manger) ![GitHub License](https://img.shields.io/github/license/KieranPritchard/dns_record_manger) ![GitHub commit activity](https://img.shields.io/github/commit-activity/t/KieranPritchard/dns_record_manger) ![GitHub last commit](https://img.shields.io/github/last-commit/KieranPritchard/dns_record_manger) ![GitHub language count](https://img.shields.io/github/languages/count/KieranPritchard/dns_record_manger) ![GitHub Repo stars](https://img.shields.io/github/stars/KieranPritchard/dns_record_manger)

## Project Description

### Objective

To build a PHP web application that keeps track of my domains, the subdomains found under them, their DNS records, and any changes to those records over time. I also wanted to make something that helps to detect subdomain takeovers, which is why every subdomain has a status. I wanted to build a full MVC project from scratch without a framework, with a real database behind it and a background service that keeps the data up to date by itself.

### Features

- **User Accounts:** Registration and login, with passwords hashed using Argon2id.
- **Domain Management:** Add, edit and delete domains, subdomains and DNS records through the web interface.
- **Dashboard:** Charts that summarise the number of domains, subdomains and records.
- **Record History:** Every addition, removal and value change to a record is logged, using a database trigger.
- **Automatic Discovery:** A separate service finds subdomains from Certificate Transparency data, then checks them with DNS lookups.
- **Subdomain Status:** Each subdomain has a status that the sync service keeps updated, so subdomains that could be taken over are easier to spot.
- **Toast Notifications:** Small pop up alerts for adding, editing, deleting and errors, styled to match the modals and sidebar.
- **Automated Tests:** PHPUnit tests for each model, run in Docker against their own test database so my real data is never touched.
- **Docker Compose Setup:** One command starts the web app, the PostgreSQL database and the DNS sync service.

### Technology and Tools Used

- **Language:** PHP 8.4, JavaScript, SQL, Bash.
- **Framework/Library:** Tailwind CSS, Chart.js, Lucide icons, PHPUnit, Composer.
- **Database:** PostgreSQL 15, accessed with PDO.
- **Tools:** Git, Docker, Docker Compose, `dig`, crt.sh.

### Challenges Faced

**Autoloading in Docker:** My tests worked on my Mac but failed in the container with `Class not found` errors. The cause was the PSR-4 path in `composer.json` starting with a `/`, which made it an absolute path, and a `Core\` namespace that was not mapped at all. I fixed it by using relative paths and adding a mapping for `app/Core/`.

**Database Setup Only Runs Once:** The scripts in `docker-entrypoint-initdb.d` only run when the database volume is first created. I spent a long time wondering why my test database never existed, when it was just an old volume being reused. Running `docker compose down -v` before the first start fixed it. I also had to stop mounting empty folders by accident, because Docker creates a directory if the file you are mounting does not exist yet.

**Transactions Left Open:** Some of my model methods returned a "not found" message after calling `beginTransaction()` without calling `rollBack()`. The tests then hung forever, because the next test's `TRUNCATE` was waiting on a lock that never got released. I found it by looking at `pg_stat_activity`, then added a rollback before every early return and a check with `inTransaction()` in each `catch` block.

### Outcome

The project works as a full DNS tracking tool. Domains can be managed through the web interface, and the sync service fills in subdomains and records on its own and logs what changed. The test setup lets me change the models and check them quickly without risking real data. It taught me a lot about PDO transactions, Docker volumes and how much an environment difference between my Mac and Linux can hide.

## How to Use the Project

1. **Clone the Repository:**
   - Use git to clone the project and open the folder in a terminal.

2. **Create the Database Password:**
   - Make a local password file. It is ignored by Git, so do not commit it.

```
mkdir -p secrets/db
openssl rand -base64 32 > secrets/db/password.txt
chmod 600 secrets/db/password.txt
```

   - If you already have a password file, keep it where it is. The database and the app both use the same secret.

3. **Start the Services:**
   - Make sure Docker and the Docker Compose plugin are installed, and that ports `3000` and `5432` are free.

```
docker compose up --build -d
```

   - On the first start, PostgreSQL creates the main database and a separate test database, and loads the schema from `database/init.sql` into both.

4. **Open the App:**
   - Go to `http://localhost:3000`, create an account and sign in.
   - To follow the logs, use `docker compose logs -f app db dns-cron`.

5. **Use the App:**
   - `/home` is the dashboard with the charts.
   - `/domains`, `/subdomains` and `/dns-records` are for adding, editing and deleting.
   - `/history` shows every change to the DNS records.
   - Management pages need you to be signed in. The `*-create`, `*-update`, `*-delete` and `/json` routes are used by the forms and validation scripts.

6. **DNS Synchronization:**
   - The `dns-cron` service runs when the stack starts, then repeats every ten minutes.
   - It reads the tracked domains, asks crt.sh for matching Certificate Transparency entries, checks the discovered names and the domain apex with `dig`, then updates subdomain status and DNS records.
   - It needs outbound access to crt.sh and DNS resolvers, and it runs whether or not anyone is logged in.

7. **Run the Tests:**
   - The tests use the `dns_record_manager_test` database and are not part of a normal `up`.

```
docker compose run --rm --build test
```

   - `--build` is needed because the test files are copied into the image.
   - The test database is only created on a fresh volume. If you change the schema, recreate it with `docker compose down -v`, which also deletes your saved data.

8. **Rebuild the Stylesheet (Optional):**
   - The Docker build compiles Tailwind from the views and JavaScript. To rebuild it locally after changing classes:

```
npm install
npx @tailwindcss/cli -i public/assets/css/input.css -o public/assets/css/main.css
```

   - Charts use Chart.js and icons use Lucide, both loaded from their CDN URLs by the browser.

9. **Find Your Way Around the Code:**
   - `app/Controllers/` has the route handlers.
   - `app/Models/` has authentication and data access.
   - `app/Core/` has the router, database connection and base classes.
   - `app/Views/` has the pages, shared components and form modals.
   - `config/routes.php` has the routes and `config/links.php` has the sidebar links.
   - `database/` has the schema, triggers and the SQL statements.
   - `docker/cron/` has the DNS sync scripts.
   - `public/` has the entry point, compiled CSS and JavaScript.
   - `tests/` has the PHPUnit tests.

10. **Run Without Docker (Optional):**
    - You need PHP with the PDO PostgreSQL extension and a PostgreSQL database with the schema loaded.
    - Set `DB_HOST`, `DB_PORT`, `DB_DATABASE` and `DB_USER`, plus either `DB_PASSWORD` or `DB_PASSWORD_FILE`.

11. **Stop the Services:**

```
docker compose down
```

   - The data stays in the `db-data` Docker volume until you remove it with `-v`.

## Licenses

License is located in the root of the repository.