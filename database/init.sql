-- ---------- Types ----------
CREATE TYPE status_enum AS ENUM ('active', 'inactive', 'unknown');
CREATE TYPE record_type_enum AS ENUM ('A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SOA', 'SRV');
CREATE TYPE change_type_enum AS ENUM ('added', 'removed', 'modified');

-- ---------- Functions ----------
-- Keeps updated_at current on any update
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Keeps last_verified current on any update (replaces MySQL's ON UPDATE CURRENT_TIMESTAMP)
CREATE OR REPLACE FUNCTION update_last_verified()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_verified = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------- Tables ----------
-- Creates the users table
CREATE TABLE IF NOT EXISTS users (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Top-level domains being tracked
CREATE TABLE IF NOT EXISTS domains (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL UNIQUE,        -- e.g. 'example.com'
    registrar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Discovered/tracked subdomains
CREATE TABLE IF NOT EXISTS subdomains (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    domain_id INT NOT NULL,
    fqdn VARCHAR(255) NOT NULL UNIQUE,        -- e.g. 'api.example.com'
    status status_enum DEFAULT 'unknown',
    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_checked TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

-- DNS records for each subdomain (a subdomain can have many record types)
CREATE TABLE IF NOT EXISTS dns_records (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    subdomain_id INT NOT NULL,
    record_type record_type_enum NOT NULL,
    value VARCHAR(512) NOT NULL,              -- IP, target hostname, TXT content, etc.
    ttl INT,
    priority INT NULL,                        -- used by MX/SRV, null otherwise
    last_verified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subdomain_id) REFERENCES subdomains(id) ON DELETE CASCADE
);

-- History log so you can see what changed and when (useful for detecting takeovers/drift)
CREATE TABLE IF NOT EXISTS record_history (
    id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    domain_id INT NOT NULL,
    subdomain_id INT NOT NULL,
    record_type VARCHAR(10) NOT NULL,
    old_value VARCHAR(512),
    new_value VARCHAR(512),
    change_type change_type_enum NOT NULL,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE,
    FOREIGN KEY (subdomain_id) REFERENCES subdomains(id) ON DELETE CASCADE
);

-- ---------- Indexes ----------
CREATE INDEX IF NOT EXISTS idx_domains_user_id ON domains(user_id);
CREATE INDEX IF NOT EXISTS idx_subdomains_domain_id ON subdomains(domain_id);
CREATE INDEX IF NOT EXISTS idx_dns_records_subdomain_id ON dns_records(subdomain_id);
CREATE INDEX IF NOT EXISTS idx_record_history_subdomain_id ON record_history(subdomain_id);
CREATE INDEX IF NOT EXISTS idx_record_history_domain_id ON record_history(domain_id);

-- ---------- Triggers ----------
CREATE TRIGGER update_domains_modtime
BEFORE UPDATE ON domains
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER update_subdomains_modtime
BEFORE UPDATE ON subdomains
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER update_dns_records_last_verified
BEFORE UPDATE ON dns_records
FOR EACH ROW
EXECUTE FUNCTION update_last_verified();