#!/usr/bin/env bash
# Checks that a change can be deployed on top of an existing database.
#
# Usage: check-migrations.sh <base checkout> <head checkout>
#   base: the code currently deployed (its schema stands in for production's)
#   head: the code about to be deployed
#
# Expects the MYSQL_DB_* variables to point at an empty MariaDB database, and the
# mysql client on PATH. Both checkouts must have their composer dependencies installed.
set -euo pipefail

BASE_DIR="$(cd "$1" && pwd)"
HEAD_DIR="$(cd "$2" && pwd)"

MYSQL=(mysql -h"$MYSQL_DB_HOST" -P"$MYSQL_DB_PORT" -u"$MYSQL_DB_USERNAME" -p"$MYSQL_DB_PASSWORD" -N "$MYSQL_DB_DATABASE")
FAILED=0

section() { echo; echo "==> $*"; }
fail() { echo "::error::$*"; FAILED=1; }

artisan() {
    local dir="$1"; shift
    (cd "$dir" && php artisan "$@")
}

# Row count of every table, used to spot seeders that insert duplicates.
row_counts() {
    local query
    query="$("${MYSQL[@]}" -e "
        SELECT GROUP_CONCAT(CONCAT('SELECT ''', table_name, ''', COUNT(*) FROM \`', table_name, '\`') SEPARATOR ' UNION ALL ')
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name NOT IN ('migrations', 'cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs')")"
    "${MYSQL[@]}" -e "$query" | sort
}

# Columns and foreign keys of every table, used to compare an upgraded database with a fresh one.
schema_snapshot() {
    "${MYSQL[@]}" -e "
        SELECT table_name, column_name, column_type, is_nullable, IFNULL(column_default, 'NULL')
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
        ORDER BY table_name, column_name;
        SELECT k.table_name, k.column_name, k.referenced_table_name, k.referenced_column_name, r.delete_rule
        FROM information_schema.key_column_usage k
        JOIN information_schema.referential_constraints r
          ON r.constraint_schema = k.constraint_schema AND r.constraint_name = k.constraint_name
        WHERE k.table_schema = DATABASE() AND k.referenced_table_name IS NOT NULL
        ORDER BY k.table_name, k.column_name;"
}

# Runs the seeders twice and fails if the second run errors or adds rows.
check_seeding_is_idempotent() {
    local label="$1"
    local before after grown

    before="$(row_counts)"
    if ! artisan "$HEAD_DIR" db:seed --force; then
        fail "$label: second db:seed failed; deploys run db:seed on every start, so seeders must be idempotent"
        return
    fi
    after="$(row_counts)"

    grown="$(join <(echo "$before") <(echo "$after") | awk '$2 != $3 { print "  " $1 ": " $2 " -> " $3 }')"
    if [ -n "$grown" ]; then
        fail "$label: a second db:seed added rows; deploys run db:seed on every start, so this duplicates data:"
        echo "$grown"
    fi
}

section "Check for edited migrations"
EDITED="$(git -C "$HEAD_DIR" diff --name-only --diff-filter=MR "$(git -C "$BASE_DIR" rev-parse HEAD)" HEAD -- database/migrations || true)"
if [ -n "$EDITED" ]; then
    echo "::warning::Migrations that already existed were changed. Databases that already ran them will NOT pick up the change; add a new migration instead:"
    echo "$EDITED" | sed 's/^/  /'
else
    echo "none"
fi

UPGRADED_SCHEMA=""

section "Build the currently deployed schema ($(git -C "$BASE_DIR" rev-parse --short HEAD))"
BASE_BUILT=0
if artisan "$BASE_DIR" migrate:fresh --seed --force; then
    BASE_BUILT=1
elif artisan "$BASE_DIR" migrate:fresh --force; then
    echo "::warning::The deployed code's seeders fail, so the upgrade is checked against its schema without seed data"
    BASE_BUILT=1
fi

if [ "$BASE_BUILT" -eq 1 ]; then
    section "Deploy the new code on top of it ($(git -C "$HEAD_DIR" rev-parse --short HEAD))"
    if ! artisan "$HEAD_DIR" migrate --force; then
        fail "Migrations fail on a database built by the currently deployed code. Never edit a migration that has already run; add a new one."
    else
        UPGRADED_SCHEMA="$(schema_snapshot)"
        if ! artisan "$HEAD_DIR" db:seed --force; then
            fail "db:seed fails on a database built by the currently deployed code"
        else
            check_seeding_is_idempotent "existing database"
        fi
    fi
else
    echo "::warning::The deployed code cannot build a database at all, so the upgrade path was not checked"
fi

section "Build a fresh database with the new code"
if ! artisan "$HEAD_DIR" migrate:fresh --force; then
    fail "migrate:fresh fails on an empty database"
else
    if [ -n "$UPGRADED_SCHEMA" ]; then
        DRIFT="$(diff <(echo "$UPGRADED_SCHEMA") <(schema_snapshot) || true)"
        if [ -n "$DRIFT" ]; then
            fail "An upgraded database ends up with a different structure than a fresh one (usually an edited migration). '<' is the upgraded database (production), '>' is a fresh one:"
            echo "$DRIFT"
        fi
    fi

    if ! artisan "$HEAD_DIR" db:seed --force; then
        fail "db:seed fails on a fresh database"
    else
        check_seeding_is_idempotent "fresh database"
    fi
fi

section "Cache routes"
if ! artisan "$HEAD_DIR" route:cache; then
    fail "route:cache fails (usually two routes with the same name); production runs it on every start"
fi
artisan "$HEAD_DIR" route:clear >/dev/null

echo
if [ "$FAILED" -ne 0 ]; then
    echo "Migration check FAILED"
    exit 1
fi
echo "Migration check passed"
