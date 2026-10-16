# Deployment

How the API gets from a push on GitHub to a running container, and what each piece does. For the
day-to-day rules (writing migrations and seeders, changing config, shipping a change), see
[development-workflow.md](development-workflow.md).

This repository is public, so this document leaves out server addresses, credentials and account
identifiers. Ask a maintainer for access to the server and to Infisical.

## Overview

```
push to master / staging
        │
        ├── build-push ──────── builds the Docker image, pushes it to GHCR
        │
        ├── migration-check ─── builds the currently deployed schema, applies the new
        │                       migrations on top, compares, seeds twice, caches routes
        │
        └── deploy (only if both succeed, runs on the server's self-hosted runner)
                ├── writes .env from Infisical
                ├── pulls the image, writes docker-compose.yml
                └── recreates the container
                        └── entrypoint: migrate → db:seed → config/route/view cache → start
```

Everything above is defined in [.github/workflows/publish-ghcr.yml](../.github/workflows/publish-ghcr.yml).

## Environments

| | Production | Staging |
|---|---|---|
| Deployed from branch | `master` | `staging` |
| Directory on the server | `/opt/school-smart` | `/opt/school-smart-staging` |
| Container | `school-smart-app` | `school-smart-app-staging` |
| Infisical environment | `prod` | `staging` |

Both containers join the external Docker network `proxy-network`, where the reverse proxy routes
traffic to them on port 80.

## Workflows

| Workflow | Trigger | What it does |
|---|---|---|
| [publish-ghcr.yml](../.github/workflows/publish-ghcr.yml) | Push to `master`/`staging`, `v*` tags, manual | Builds and pushes the image; on branch pushes runs the migration check and deploys |
| [migration-check.yml](../.github/workflows/migration-check.yml) | Pull requests to `master`/`staging`; called by `publish-ghcr.yml` | Runs [scripts/ci/check-migrations.sh](../scripts/ci/check-migrations.sh) |
| [deploy-vps.yml](../.github/workflows/deploy-vps.yml) | Manual only | Deploys a chosen image tag to a chosen environment; used for rollbacks |

### Image

The [Dockerfile](../Dockerfile) has two stages:

1. **vendor** installs Composer dependencies (`--no-dev`). It skips the platform check for `gd`
   and `exif`, which are only needed at runtime.
2. **runtime** is `php:8.3-fpm` with nginx and supervisor, plus the extensions the app needs
   (`bcmath`, `exif`, `gd`, `intl`, `opcache`, `pcntl`, `pdo_mysql`, `zip`, `redis`, `mongodb`).

Images are tagged with the full commit SHA, the short SHA and the branch name; `master` builds are
also tagged `latest`. Deploys always use the full commit SHA.

### Migration check

[scripts/ci/check-migrations.sh](../scripts/ci/check-migrations.sh) compares two checkouts: the
**base** (the code currently deployed) and the **head** (the code about to be deployed). On a push,
the base is the previous commit on the branch; on a pull request, it is the target branch.

Against a throwaway MariaDB 10.11 (the same version as the server), it:

1. **Warns** if a migration file that already existed was modified.
2. Builds the base schema with `migrate:fresh --seed`. This stands in for the live database.
3. Runs the head's `migrate`. **Fails** if any migration errors.
4. Builds a fresh database with the head's migrations. **Fails** if its structure (columns, types,
   nullability, defaults, foreign keys) differs from the upgraded one. That difference means
   existing databases and new ones have drifted apart, almost always because an old migration was
   edited.
5. Runs `db:seed` twice on each database. **Fails** if the second run errors or adds rows.
6. Runs `route:cache`. **Fails** on errors such as duplicate route names.

If the check fails during a push, the deploy job is skipped and production keeps running the
previous version.

**What it cannot catch:** the check database contains seed data but no real schools, admins or
students. A migration that only breaks on real data (for example, converting a populated UUID
column to an integer) can pass the check and still fail in production.

## Configuration and secrets

All runtime configuration lives in **Infisical** (US cloud), in one project with `prod` and
`staging` environments.
See [secrets.md](secrets.md) for how to get access, change values and use them locally.

On every deploy, the workflow:

1. Logs in to Infisical as the read-only machine identity `github-deploy`, using the GitHub secrets
   `INFISICAL_CLIENT_ID`, `INFISICAL_CLIENT_SECRET` and `INFISICAL_PROJECT_ID`.
2. Exports the environment to a temporary file.
3. Replaces the server's `.env` only if the export succeeded and contains `APP_KEY`. Otherwise the
   deploy fails and the current `.env` is left in place.

**The server's `.env` is overwritten on every deploy.** Editing it by hand only lasts until the
next deploy; change values in Infisical instead.

The Infisical CLI must be installed on the server, because the deploy job runs there.

## Container startup

[docker/entrypoint.sh](../docker/entrypoint.sh) runs each time the container starts:

1. Exits if `APP_KEY` is not set.
2. If `RUN_MIGRATIONS=true`: runs `php artisan migrate --force`, then `php artisan db:seed --force`.
3. If `APP_ENV=production`: runs `config:cache`, `route:cache` and `view:cache`.
4. Starts supervisor, which runs php-fpm and nginx.

The script stops at the first failing command. A failed migration or seeder therefore stops the
container, and Docker restarts it in a loop. This is deliberate: the app never serves traffic
against a half-migrated database.

The container exposes a health check at `/up`.

### Known gap: no queue worker or scheduler

Supervisor only runs php-fpm and nginx. Nothing in the container runs `php artisan queue:work`
or `php artisan schedule:run`. Queued jobs and scheduled commands, such as the nightly
`students:promote`, will not run unless they are started some other way. If the app depends on
them, add supervisor programs for both.

## Database

- **MySQL data:** MariaDB 10.11 installed directly on the server, not in Docker. The app reaches
  it at `host.docker.internal`, which the deploy maps to the server through `extra_hosts`.
  `127.0.0.1` or `localhost` would point at the container itself and will not work.
- **The configuration keys are `MYSQL_DB_*`**, not Laravel's default `DB_*`. See
  [config/database.php](../config/database.php).
- **MongoDB** (`MONGO_DB_*`) holds job events, promotion logs and analytics.
- The MariaDB user for the app only accepts connections from the Docker networks, and port 3306
  is closed to the internet.

## Troubleshooting

Run these on the server.

| Symptom | Likely cause | Where to look |
|---|---|---|
| Container status `Restarting` | A migration, seeder or cache command fails at startup | `docker logs --tail 60 school-smart-app` |
| `SQLSTATE[HY000] [2002] Connection refused` | `MYSQL_DB_HOST` is `127.0.0.1` or `localhost` | Set it to `host.docker.internal` in Infisical |
| `[1045] Access denied` | Wrong database password in Infisical, or the DB user doesn't allow the Docker network | Compare Infisical with the MariaDB user |
| `[1049] Unknown database` | `MYSQL_DB_DATABASE` names a database that doesn't exist | `mysql -e "SHOW DATABASES;"` |
| Deploy fails at "Write environment file from Infisical" | Bad Infisical credentials, or the environment has no `APP_KEY` | The job log; the old `.env` is still in place |
| Deploy skipped, `migration-check` red | The change fails the migration check | The check's log; see [development-workflow.md](development-workflow.md#when-the-migration-check-fails) |
| `docker login ghcr.io` denied | Token problem | Both workflows log in with the built-in `GITHUB_TOKEN`; the package must allow this repository's Actions |

Useful commands:

```bash
docker ps --format '{{.Names}}  {{.Status}}'                     # container state
docker logs --tail 100 school-smart-app                          # startup errors
docker exec school-smart-app sh -c 'tail -n 100 storage/logs/laravel.log'   # app errors
docker exec school-smart-app php artisan db:show                  # database connection
docker exec school-smart-app php artisan migrate:status           # which migrations ran
cut -d= -f1 /opt/school-smart/.env                                # variable names only
```

## Rolling back

Run **Actions → Deploy to VPS → Run workflow**, choose the environment and enter the full commit
SHA of the last good version as `image_tag`.

This deploys the old image but **does not undo migrations**. If the bad version ran a migration
that the old code can't work with, restore the schema by hand or fix forward with a new commit.
