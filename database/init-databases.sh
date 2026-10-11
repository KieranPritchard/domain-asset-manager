#!/bin/sh
set -e

# Loads the schema into the main database
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -f /schema/init.sql

# Creates the test database and loads the same schema into it
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d postgres -c "CREATE DATABASE dns_record_manager_test"
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d dns_record_manager_test -f /schema/init.sql