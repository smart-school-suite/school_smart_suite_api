# Secrets and configuration (Infisical)

All configuration for this API (database credentials, `APP_KEY`, mail, storage keys and other
settings) is stored in [Infisical](https://infisical.com), a hosted vault for secrets. This
document explains how to get access, how to change a value, and how to use the same values when
developing locally.

For how deploys use these values, see [deployment.md](deployment.md#configuration-and-secrets).

This repository is public: never commit a secret, a `.env` file or an Infisical ID.

## How it fits together

```
Infisical project
 ├── dev      ← local development
 ├── staging  ← written to the staging server's .env on each staging deploy
 └── prod     ← written to the production server's .env on each production deploy
```

- **Infisical is the only place to change configuration.** Each deploy overwrites the server's
  `.env` with what's in Infisical, so edits made on the server are lost on the next deploy.
- Deploys log in with a read-only machine identity called `github-deploy`. It can read secrets but
  never change them.
- Infisical keeps a version history and an audit log, so you can see who changed a value and roll
  it back.

## Getting access

1. Create an account at <https://app.infisical.com> (**US region**; the project isn't visible from
   the EU region).
2. Ask a maintainer to invite you to the organisation and add you to the project.

Who should have which role:

| Role | Who | Can |
|---|---|---|
| Admin | Maintainers | Manage members, machine identities and every environment |
| Member | Developers | Read and edit secrets |
| Viewer | Anyone who only needs to look | Read secrets |

Keep the number of people who can edit `prod` small. A wrong production value takes the app down
on the next deploy.

## Changing a value

### In the web app

1. Open the project and pick the environment (`prod`, `staging` or `dev`).
2. Find the variable, edit it, and save.
3. **Redeploy** for the server to pick it up. Values are only read during a deploy:
   **GitHub → Actions → Publish image to GHCR → latest run on the branch → Re-run all jobs**.
4. Check the app is healthy:
   ```bash
   docker ps --filter name=school-smart-app --format '{{.Names}}  {{.Status}}'
   ```

### Adding a new variable

1. Add it to **every environment** that needs it: usually `dev`, `staging` and `prod`.
2. Add it to `.env.example` with a placeholder value, so others know it exists:
   ```
   NEW_SERVICE_API_KEY=
   ```
3. Read it through a config file (`config/*.php`), not with `env()` directly in the code.
   Production caches config, and `env()` returns `null` outside config files once config is
   cached.

### Values that must stay as they are

| Variable | Value | Why |
|---|---|---|
| `APP_KEY` | the existing key | Changing it makes encrypted data and existing sessions unreadable. A deploy is refused if it's missing |
| `MYSQL_DB_HOST` | `host.docker.internal` | The database runs on the server, outside the container. `127.0.0.1` points at the container itself |
| `RUN_MIGRATIONS` | `true` | Runs migrations and seeders on each deploy |
| `APP_ENV` | `production` on `prod` | Enables config, route and view caching |

The database settings use the `MYSQL_DB_*` prefix (not Laravel's default `DB_*`), and MongoDB uses
`MONGO_DB_*`.

## Using it for local development

### Install the CLI

Follow the instructions for your system at <https://infisical.com/docs/cli/overview>. For example,
on Ubuntu/Debian:

```bash
curl -1sLf 'https://artifacts-cli.infisical.com/setup.deb.sh' | sudo -E bash
sudo apt-get update && sudo apt-get install -y infisical
```

### Connect the project (once per machine)

From the repository root:

```bash
infisical login     # choose "US Cloud", then sign in in the browser
infisical init      # pick the organisation and this project
```

`infisical init` creates `.infisical.json`, which points the CLI at the project. It's in
`.gitignore`; keep it out of commits.

### Run the app with the dev values

Either inject the values without writing them to disk:

```bash
infisical run --env=dev -- php artisan serve
infisical run --env=dev -- php artisan migrate
```

or write them to your local `.env`:

```bash
infisical export --env=dev > .env
```

Values injected with `infisical run` take priority over your `.env`. If your machine needs a
different value for one variable (for example a local database password), keep it in your own
`.env` and use the export option, or ask a maintainer about Infisical's personal overrides.

If changes don't seem to apply, clear Laravel's cached config:

```bash
php artisan config:clear
```

### Other useful commands

```bash
infisical secrets --env=dev                    # list dev variables and values
infisical secrets get MAIL_HOST --env=dev       # read one value
infisical secrets set MAIL_HOST=smtp.example.com --env=dev   # change one value
```

Be careful when changing `prod` from the command line: there's no confirmation step.

## Rotating a secret

Rotate a secret if it was shared in chat, pasted into a ticket, committed by accident, or known by
someone who has left.

1. Generate the new value at its source (the database, the mail provider, AWS, and so on).
2. Update it in Infisical, in every environment that uses it.
3. Redeploy and check that the app is healthy.
4. Revoke or delete the old value at its source.

For the database password, the new password has to be set in MariaDB on the server **and** in
Infisical before redeploying. If they don't match, the app fails with `Access denied`.

## The deploy identity

The workflows authenticate as the machine identity `github-deploy` (Universal Auth, Viewer role on
the project). Its credentials are stored as GitHub repository secrets:

| GitHub secret | Where to find the value in Infisical |
|---|---|
| `INFISICAL_CLIENT_ID` | Organisation → Access Control → Identities → `github-deploy` → Universal Auth |
| `INFISICAL_CLIENT_SECRET` | Created on the same page; shown only once |
| `INFISICAL_PROJECT_ID` | Project → Settings |

To replace the client secret:

1. Create a new client secret on the identity's Universal Auth page.
2. Update `INFISICAL_CLIENT_SECRET` in **GitHub → Settings → Secrets and variables → Actions**.
3. Re-run a deploy to confirm it works.
4. Revoke the old client secret.

Don't delete the identity or remove it from the project: deploys stop at the "Write environment
file from Infisical" step until it's restored.

## When someone leaves

1. Remove them from the Infisical organisation.
2. Rotate any secrets they could read and that would be harmful in the wrong hands: database
   password, mail credentials, storage keys and payment keys.
3. Remove their access to the GitHub repository and the server.

## Troubleshooting

| Problem | Fix |
|---|---|
| Deploy fails at **Write environment file from Infisical** | Check the three `INFISICAL_*` GitHub secrets and that `github-deploy` still has access to the project. The server keeps its previous `.env` |
| Deploy log says the export has no `APP_KEY` | The environment is empty or `APP_KEY` was deleted; restore it from the version history |
| A changed value isn't used on the server | Values are only read during a deploy; redeploy |
| `infisical: command not found` on the server | Reinstall the CLI on the server; the deploy job runs there |
| `infisical init` doesn't list the project | Make sure you logged in to the US region and were added to the project |
| A value was changed by mistake | Open the variable's version history in Infisical, restore the previous value, then redeploy |
