#!/bin/bash
set -e

# Read database password from secret file if specified
if [ -n "$DB_PASSWORD_FILE" ] && [ -f "$DB_PASSWORD_FILE" ]; then
    export PGPASSWORD=$(cat "$DB_PASSWORD_FILE")
else
    export PGPASSWORD=${DB_PASSWORD:-root}
fi

export PGHOST=${DB_HOST:-db}
export PGPORT=${DB_PORT:-5432}
export PGUSER=${DB_USER:-root}
export PGDATABASE=${DB_DATABASE:-dns_record_manager}

RECORD_TYPES=("A" "AAAA" "CNAME" "MX" "TXT" "NS")

echo "[$(date)] Starting Certificate Transparency DNS auto-discovery..."

# Fetch all primary domains from database
QUERY="SELECT id, name FROM domains;"

psql -h "$PGHOST" -U "$PGUSER" -d "$PGDATABASE" -t -A -F"," -c "$QUERY" | while IFS="," read -r domain_id domain_name; do
    [ -z "$domain_id" ] && continue

    echo "[$(date)] Discovering subdomains for: $domain_name"

    # 1. Fetch subdomains from crt.sh API using curl + jq
    # - Filters out wildcard prefixes (*.)
    # - Converts to lowercase and removes duplicates
    discovered_fqdns=$(curl -s "https://crt.sh/?q=%.${domain_name}&output=json" 2>/dev/null \
        | jq -r '.[].name_value' 2>/dev/null \
        | tr '[:upper:]' '[:lower:]' \
        | sed 's/\*\.//g' \
        | sort -u)

    # Always ensure the apex domain itself is included
    all_fqdns=$(echo -e "${domain_name}\n${discovered_fqdns}" | sort -u)

    echo "$all_fqdns" | while read -r fqdn; do
        [ -z "$fqdn" ] && continue

        has_records=false

        # 2. Check each DNS record type using dig
        for rtype in "${RECORD_TYPES[@]}"; do
            results=$(dig +short "$rtype" "$fqdn" | sed 's/\.$//')

            if [ -n "$results" ]; then
                has_records=true

                # Upsert Subdomain into PostgreSQL
                subdomain_id=$(psql -q -h "$PGHOST" -U "$PGUSER" -d "$PGDATABASE" -t -A -c "
                    INSERT INTO subdomains (domain_id, fqdn, status, last_checked)
                    VALUES ($domain_id, '$fqdn', 'active', NOW())
                    ON CONFLICT (fqdn) DO UPDATE 
                        SET status = 'active',
                            last_checked = NOW()
                    RETURNING id;
                ")

                # Upsert individual DNS records
                echo "$results" | while read -r line; do
                    clean_val=$(echo "$line" | tr -d '"' | sed "s/'/''/g")
                    priority="NULL"
                    record_val="$clean_val"

                    if [ "$rtype" == "MX" ]; then
                        priority=$(echo "$clean_val" | awk '{print $1}')
                        record_val=$(echo "$clean_val" | awk '{$1=""; print $0}' | sed 's/^ //')
                    fi

                    [ -z "$record_val" ] && continue

                    # Insert into dns_records (Triggers will automatically write to record_history)
                    psql -h "$PGHOST" -U "$PGUSER" -d "$PGDATABASE" -c "
                        INSERT INTO dns_records (subdomain_id, record_type, value, priority, last_verified)
                        VALUES ($subdomain_id, '$rtype'::record_type_enum, '$record_val', $priority, NOW())
                        ON CONFLICT DO NOTHING;
                    " > /dev/null 2>&1
                done
            fi
        done

        # 3. Mark subdomain as inactive if no active DNS records respond
        if [ "$has_records" = false ]; then
            psql -h "$PGHOST" -U "$PGUSER" -d "$PGDATABASE" -c "
                UPDATE subdomains 
                SET status = 'inactive', last_checked = NOW() 
                WHERE fqdn = '$fqdn';
            " > /dev/null 2>&1
        fi
    done
done

echo "[$(date)] Auto-discovery and DNS synchronization complete."