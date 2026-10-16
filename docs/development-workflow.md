# Development workflow

How to ship changes to this API without breaking production. For how the pipeline itself works,
see [deployment.md](deployment.md).

## The four rules

1. **Never edit a migration that has been pushed.** Add a new migration instead.
2. **Every seeder must be safe to run again.** Production runs `db:seed` on every deploy.
3. **Every route name must be unique.** Production runs `route:cache` on every start.
4. **Change configuration in Infisical, never on the server.** The server's `.env` is overwritten
   on every deploy.

The migration check enforces the first three before anything is deployed.

## Shipping a change

1. Work on a branch, such as `devops` or a feature branch.
2. Open a pull request to `staging` or `master`.
3. Wait for the **migrations** check on the pull request to pass. Fix anything it reports.
4. Merge. The push to the branch builds the image, runs the migration check again and deploys.
5. Confirm it's running:
   ```bash
   docker ps --filter name=school-smart-app --format '{{.Names}}  {{.Status}}'
   ```
   It should show `Up` and later `(healthy)`, never `Restarting`.

Avoid pushing straight to `master`. A failing direct push won't reach production, because the
migration check runs before the deploy, but you only find out after the push.

## Migrations

### Why old migrations must not change

Laravel records each migration's filename once it has run, and never runs that file again.
If you edit a migration that production already ran:

- your fresh local database gets the new version, so everything looks fine to you;
- production and staging keep the old version, and the code fails there with errors like
  `Unknown column`.

### Changing an existing table

Create a new migration:

```bash
php artisan make:migration add_status_to_example_table
```

Then move it into the matching folder under `database/migrations/` (`00_system`,
`01_school_core`, `02_people`, `03_academics`, ... `07_constraints`). The folder is only for
organisation. Laravel orders migrations by the timestamp in the filename, so a new file always
runs after the existing ones.

### Things that have broken migrations here before

| Mistake | Do this instead |
|---|---|
| Editing a migration that already ran | Add a new migration |
| `foreignId('x')->change()` on a UUID column | Primary keys here are UUID strings. Use `uuid('x')` or `string('x')`; `foreignId` creates an integer column |
| Dropping a foreign key or column that might already be gone | Check first, e.g. `Schema::hasColumn(...)` or `Schema::getForeignKeys(...)`. A migration that fails halfway can't be rolled back on MySQL, so its next run starts from a half-changed table |
| A foreign key to a table created by a later migration | Put the foreign key in a migration dated after the table it references |
| Adding a column in a table migration and again in a `07_constraints` migration | Define each column once |
| Long composite index names (MariaDB's limit is 64 characters) | Pass a short name: `$table->unique([...], 'short_name_unique')` |
| A migration touching a table that now lives in MongoDB | Only MySQL tables belong in migrations |

### Making a migration safe for databases in different states

If a migration might run on databases in different states, check before each step:

```php
public function up(): void
{
    if (!Schema::hasColumn('example', 'status')) {
        Schema::table('example', function (Blueprint $table) {
            $table->string('status')->default('active');
        });
    }
}
```

## Seeders

Seeders hold **reference data only**: countries, roles, permissions, plans, exam types, settings.
Never put test or demo data in `DatabaseSeeder`, because it runs on production at every deploy.

### Writing a seeder that's safe to repeat

Find each row by a value that never changes (a `key`, `name` or `code`), then update it or create
it:

```php
foreach ($items as $item) {
    Example::updateOrCreate(
        ['key' => $item['key']],                    // how to find the existing row
        ['name' => $item['name'], 'status' => 'active']   // values to set
    );
}
```

With the query builder, check first:

```php
if (DB::table('examples')->where('key', $key)->exists()) {
    DB::table('examples')->where('key', $key)->update([...]);
} else {
    DB::table('examples')->insert(['id' => Str::uuid()->toString(), 'key' => $key, ...]);
}
```

Avoid these. Each one fails or adds duplicate rows on the second run:

- `Model::create()` or `DB::table()->insert()` without checking first
- Spatie's `Permission::create()` and `Role::create()` (they throw if the record exists; use
  `updateOrCreate`)
- `upsert()` on columns with no unique index (MariaDB inserts duplicates instead of updating)

Also make sure:

- The column you look rows up by exists in the migration and is in the model's `$fillable`.
- Models that use UUID primary keys use the `GeneratesUuid` trait.
- A seeder that depends on another (exam types need semesters) runs after it in
  `DatabaseSeeder`.

### Running seeders locally

```bash
php artisan db:seed            # all seeders
php artisan db:seed            # again: should add nothing and print no errors
```

## Routes

Every `->name('...')` must be unique across all route files. To list duplicates:

```bash
php artisan route:list --json | python3 -c "import json,sys,collections; c=collections.Counter(r['name'] for r in json.load(sys.stdin) if r['name']); print({k:v for k,v in c.items() if v>1} or 'no duplicates')"
```

## Configuration

Full guide: [secrets.md](secrets.md). In short, to add or change a setting:

1. Open the project in Infisical and choose the environment (`prod` or `staging`).
2. Add or edit the variable.
3. Redeploy so the server picks it up: **Actions → Publish image to GHCR → latest run on the
   branch → Re-run all jobs**.

For a new variable, also add it to `.env.example` with a placeholder value, so others know it
exists.

Settings that must stay this way:

| Variable | Value | Why |
|---|---|---|
| `MYSQL_DB_HOST` | `host.docker.internal` | MariaDB runs on the server, outside the container |
| `RUN_MIGRATIONS` | `true` | Runs migrations and seeders on each deploy |
| `APP_KEY` | the existing key | Changing it invalidates encrypted data and sessions; the deploy refuses an environment without one |

Never commit secrets: this repository is public.

## When the migration check fails

Open the failed **migrations** job and find the line starting with `Error:`.

| Message | What it means | Fix |
|---|---|---|
| *Migrations fail on a database built by the currently deployed code* | A new migration errors on an existing database | Run the failing migration locally against a database built from `master`, then fix it |
| *An upgraded database ends up with a different structure than a fresh one* | An existing migration was edited. The diff shows what production would be missing (`<` is production, `>` is a fresh database) | Undo the edit and add a new migration that makes the same change |
| *second db:seed failed* or *a second db:seed added rows* | A seeder isn't safe to repeat | Make it update-or-create (see [Seeders](#seeders)) |
| *migrate:fresh fails on an empty database* | A migration only works on an existing database, or migrations run in the wrong order | Check filename timestamps and foreign keys to later tables |
| *route:cache fails* | Usually two routes with the same name | Rename one |
| Warning: *Migrations that already existed were changed* | An existing migration file was modified | Fine only if that migration never completed anywhere; otherwise add a new migration |

### Running the check locally

You need Docker and a MySQL client (`sudo apt install mariadb-client`).

```bash
# 1. Throwaway MariaDB on port 33099
docker run -d --rm --name migration-check -e MARIADB_ROOT_PASSWORD=test \
  -e MARIADB_DATABASE=migration_check -p 127.0.0.1:33099:3306 mariadb:10.11

# 2. A checkout of the deployed code (the base), with its own dependencies
git fetch origin
git worktree add ../sss-base origin/master
(cd ../sss-base && composer install --no-dev --no-interaction)

# 3. Run the check, pointing it at the throwaway database
MYSQL_DB_HOST=127.0.0.1 MYSQL_DB_PORT=33099 MYSQL_DB_DATABASE=migration_check \
MYSQL_DB_USERNAME=root MYSQL_DB_PASSWORD=test \
CACHE_STORE=array QUEUE_CONNECTION=sync MAIL_MAILER=log BROADCAST_CONNECTION=log \
  bash scripts/ci/check-migrations.sh ../sss-base .

# 4. Clean up
docker rm -f migration-check
git worktree remove ../sss-base
```

The check wipes the database it's given. Only point it at a throwaway database.

## Before opening a pull request

- [ ] No migration that already exists in `master` was edited
- [ ] New migrations use `uuid`/`string` for references to UUID primary keys
- [ ] `php artisan migrate:fresh --seed` works locally
- [ ] Running `php artisan db:seed` a second time adds nothing
- [ ] No duplicate route names
- [ ] New config variables are in Infisical (`prod` and `staging`) and `.env.example`
- [ ] No secrets in the code
