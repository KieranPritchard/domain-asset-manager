# DNS Record Manager

DNS Record Manager is a PHP web application for tracking domains, discovered subdomains, DNS records, and changes to those records. It includes a dashboard, authenticated domain management, and a background DNS discovery and synchronization service.

## Features

- User registration and login, with Argon2id password hashing.
- Manage domains, subdomains, and DNS records through the web interface.
- Dashboard charts summarizing domains, subdomains, and records.
- Record history for additions, removals, and value changes.
- Periodic subdomain discovery from Certificate Transparency data and DNS lookups.
- PostgreSQL-backed data storage with triggers for timestamps and record history.
- Docker Compose setup for the web app, database, and DNS synchronization service.

## Requirements

- Docker Engine and the Docker Compose plugin.
- An available host port `3000` for the web application. PostgreSQL is also published on host port `5432` by the Compose configuration.

## Getting started with Docker Compose

1. From the project root, create a local PostgreSQL password file. The file is ignored by Git and should not be committed:

   ```sh
   mkdir -p secrets/db
   openssl rand -base64 32 > secrets/db/password.txt
   chmod 600 secrets/db/password.txt
   ```

   If you already have a password file, keep it in place; Compose uses the same secret for the database and application.

2. Build and start the services:

   ```sh
   docker compose up --build -d
   ```

3. Open [http://localhost:3000](http://localhost:3000), create an account, and sign in.

4. Follow service output when needed:

   ```sh
   docker compose logs -f app db dns-cron
   ```

Stop the services with:

```sh
docker compose down
```

The PostgreSQL data is stored in the `db-data` Docker volume and remains when the services are stopped. The database schema in `database/init.sql` is run when PostgreSQL initializes a new data volume.

## DNS synchronization

The `dns-cron` service runs the synchronization script immediately at startup, then repeats it every ten minutes. It reads tracked domains from PostgreSQL, requests matching Certificate Transparency entries from `crt.sh`, checks discovered names and the domain apex with `dig`, and updates subdomain status and DNS records.

The service needs outbound network access to `crt.sh` and DNS resolvers. The scheduled synchronization runs independently of the logged-in web user.

## Application routes

| Route | Purpose |
| --- | --- |
| `/` | Redirects to the dashboard or login page |
| `/login`, `/signup`, `/logout` | Authentication |
| `/home` | Dashboard |
| `/domains` | Domain management |
| `/subdomains` | Subdomain management |
| `/dns-records` | DNS record management |
| `/history` | DNS record change history |

Management pages require authentication. The `*-create`, `*-update`, `*-delete`, and `/json` routes are used by the app’s forms and validation scripts.

## Project structure

| Path | Description |
| --- | --- |
| `app/Controllers/` | Route handlers and request workflows |
| `app/Models/` | Authentication and data-access code |
| `app/Core/` | Router, database connection, and base controller/model |
| `app/Views/` | PHP pages, shared components, and form modals |
| `config/routes.php` | HTTP route definitions |
| `config/links.php` | Sidebar navigation |
| `database/` | Schema, triggers, and parameterized SQL statements |
| `docker/cron/` | DNS discovery/synchronization scripts and container definition |
| `public/` | Web entry point, compiled CSS, and JavaScript assets |

## Front-end assets

The Docker build compiles Tailwind CSS from the PHP views and JavaScript assets. To rebuild the stylesheet locally after changing utility classes, install the Node development dependencies and run:

```sh
npm install
npx @tailwindcss/cli -i public/assets/css/input.css -o public/assets/css/main.css
```

Dashboard charts use Chart.js, and icons use Lucide; both are loaded by the browser from their configured CDN URLs.

## Configuration and secrets

The Compose file configures the database connection for the app and DNS sync service. The database password is supplied through the `db-password` Compose secret at `secrets/db/password.txt`. Keep that file private; do not put credentials in source control.

For a non-Docker PHP setup, the database connection can be configured with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, and `DB_USER`, plus either `DB_PASSWORD` or `DB_PASSWORD_FILE`. The PHP runtime needs the PDO PostgreSQL extension enabled.
