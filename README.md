# School Smart Suite API

The backend for **School Smart Suite**, a multi-tenant school management platform for secondary and
higher-education institutions. One deployment serves many schools, and each school can run several
branches (campuses). The API handles the full academic and administrative cycle: enrolment,
courses and timetables, exams and resits, results and transcripts, promotion and graduation, fees
and expenses, elections, announcements, events and analytics.

It is a JSON REST API built with **Laravel 11** on **PHP 8.3**. The School Smart Suite desktop app
and other clients use it.

---

## Table of contents

- [Who uses it](#who-uses-it)
- [Features](#features)
- [Architecture](#architecture)
- [Tech stack](#tech-stack)
- [Project structure](#project-structure)
- [Getting started](#getting-started)
- [Configuration](#configuration)
- [API conventions](#api-conventions)
- [Authentication](#authentication)
- [Multi-tenancy](#multi-tenancy)
- [Timetable engines](#timetable-engines)
- [Promotion engine](#promotion-engine)
- [Background work and scheduling](#background-work-and-scheduling)
- [Real-time and notifications](#real-time-and-notifications)
- [Analytics](#analytics)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)

---

## Who uses it

Five kinds of user, each with its own login flow and route group:

| Actor | What they do |
|---|---|
| **App admin** | Runs the platform: schools, subscription plans, features, payment methods, system academic years. |
| **School admin** | Runs a school branch: staff, students, academics, exams, finance, communication. Permissions are role-based. |
| **Teacher** | Sets availability and course preferences, sees their timetable, enters scores, receives notifications. |
| **Student** | Sees timetables, results, fees, announcements and events; takes part in elections. |
| **Parent** | Linked to one or more students through parent–student relationships. |

---

## Features

### School and organisation
- Schools with several **branches**, each with its own data, settings and API keys.
- **Departments**, **specialties** (programmes), **levels** and level types.
- **Halls** (classrooms and exam halls), hall types and specialty–hall assignments.
- School **assets**.
- Per-branch **settings** (`SchoolBranchSetting`) grouped into categories. These control behaviour
  such as promotion thresholds and automatic record creation.
- Reference data: countries, genders, student sources, qualifications.

### People
- Students (single and batch creation), teachers, parents and school admins.
- Teacher qualifications, specialties, levels, **course preferences** and **availability slots**.
- Student–parent relationships.
- **Account activation**: schools buy activation codes and use them to activate student and
  teacher accounts. The API tracks code usage, expiry and renewals.
- Account limits per subscription plan, enforced by the `Limit*` middleware.

### Academics
- **System academic years**, created automatically each month as needed, and per-school academic years.
- **Semesters** with statuses that update automatically.
- **Courses** and course types, **joint courses** shared across specialties, and joint course slots.
- **Semester timetables**, generated automatically under constraints and stored as versions (see
  [Timetable engines](#timetable-engines)).
- Timetable **constraints**, constraint categories and constraint types.

### Exams and results
- **Exams**, exam types, exam candidates and **exam timetables** (generated automatically, with
  hall and invigilator allocation).
- **Invigilators**.
- **Exam scores**, evaluation helpers and **exam results**.
- **Grade scales**, grade categories and **letter grades**, configurable per school.
- **Resits**: automatic detection of resit candidates, resit exams, resit timetables, resit
  payments, resit scores, resit evaluation and resit results.
- PDF output for documents such as results and timetables (DomPDF).

### Progression
- **Promotion runs**: rules decide whether each student moves up a level (see
  [Promotion engine](#promotion-engine)). Every decision is logged with its reasons.
- **Graduation batches**, graduation records and **alumni**.

### Finance
- **Tuition fees**, **registration fees** and **additional fees** with categories.
- **Fee payment schedules**, schedule slots, **installments** and **fee waivers**.
- Transaction records for every fee type.
- **Payment methods** and payment method categories.
- **School expenses** and expense categories.
- Financial KPI snapshots for the dashboards.

### Communication and student life
- **Announcements** with categories, labels, tags, target audiences, scheduling and engagement
  statistics.
- **School events** with categories and tags, scheduled reminders and expiry.
- **Elections**: election types, offices, roles, applications, candidates, voting with live
  vote events, and automatic status transitions.
- Email, push and in-app **notifications** for most actions (results released, fee paid,
  timetable available, election open, and others).

### Platform and subscriptions
- **Subscription plans**, features, plan–feature mapping and plan recommendations.
- **School subscriptions** and subscription payments.
- Usage tracking (`SubscriptionUsage`) and renewal reminders.
- An affiliate programme (applications, commissions, payouts).

### Analytics
- Academic, enrolment, financial, operational and election analytics engines, served through
  `academic-stats`, `financial-stats` and `operational-stats` endpoints.

---

## Architecture

The code is layered so that controllers stay thin:

```
HTTP request
   │
   ├── Middleware ─── auth:sanctum → IdentifyTenant (sets the school branch) → account / limit checks
   │
   ├── Form Request ─ validation (app/Http/Requests)
   │
   ├── Controller ─── reads input and the current school, calls one service, formats the response
   │
   ├── Service ────── business logic (app/Services/<Domain>)
   │     ├── Models (Eloquent, MySQL)       ← relational data
   │     ├── Mongo models                   ← timetables, logs, analytics
   │     ├── Jobs / Events / Notifications  ← side effects
   │     └── Engines (app/Schedular, app/Interpreter, app/Analytics)
   │
   └── ApiResponseService ─ one JSON envelope for every response
```

Most domains follow the same layout. A domain such as `Exam` has a folder under `routes/`,
`app/Http/Controllers/`, `app/Services/` and `app/Models/`. Data passed between layers uses DTOs
(`app/DTOs`). Listing endpoints use reusable **filters**, **sorters**, **groupers** and
**formatters** (`app/Filters`, `app/Sorters`, `app/Groupers`, `app/Formatters`).

### Data stores

| Store | Used for |
|---|---|
| **MySQL / MariaDB** | Main relational data. Primary keys are UUID strings. |
| **MongoDB** | Generated timetables (slots, inputs, diagnostics), job events, promotion logs, analytics snapshots and time series. |
| **Meilisearch** (Laravel Scout) | Installed for full-text search. No model is indexed yet. |
| **S3-compatible storage** | Files and media (Spatie Media Library, Flysystem S3). |

---

## Tech stack

| Area | Tooling |
|---|---|
| Framework | Laravel 11, PHP 8.3 |
| Auth | Laravel Sanctum (bearer tokens), email OTP verification |
| Authorisation | `spatie/laravel-permission` (roles and permissions) |
| Databases | MySQL/MariaDB, MongoDB (`mongodb/laravel-mongodb`) |
| Search | Laravel Scout with Meilisearch (installed, not yet used by models) |
| Files | Spatie Media Library, AWS S3 adapter |
| Real-time | Ably (token endpoint at `/api/v1/auth/ably-token`), Pusher-compatible broadcasting |
| Push notifications | Firebase Cloud Messaging (`kreait/firebase-php`) |
| Email | Resend |
| Documents | DomPDF (PDF), PhpSpreadsheet (Excel import and export) |
| Monitoring | Sentry |
| Runtime | Docker (PHP-FPM, Nginx, Supervisor) |
| CI/CD | GitHub Actions → GitHub Container Registry → self-hosted VPS |

---

## Project structure

```
app/
├── Analytics/            Analytics engines and projections (academic, financial, ...)
├── Console/Commands/     Scheduled and maintenance Artisan commands
├── DTOs/                 Data transfer objects
├── Events/ Listeners/    Domain events (timetable generation, votes, analytics)
├── Exceptions/           AppException, AuthException and handlers
├── Filters/ Sorters/ Groupers/ Formatters/   Reusable query and response helpers
├── Http/
│   ├── Controllers/      One folder per domain (~60 domains)
│   ├── Middleware/       Tenant resolution, account control, plan limits
│   └── Requests/         Form request validation
├── Interpreter/          Turns timetable engine output into readable explanations and suggestions
├── Jobs/                 Queued jobs (notifications, data creation, cleanup, statistics, ...)
├── Mail/ Notifications/  Email and notification classes
├── Models/               Eloquent and MongoDB models (~126)
├── Rules/                Custom validation rules
├── Schedular/            Constraint-based exam and semester timetable engines
├── Services/             Business logic, one folder per domain
└── Traits/
database/
├── migrations/           Grouped: 00_system, 01_school_core, 02_people, 03_academics,
│                         04_finance, 05_student_ops, 06_communication, 07_constraints
├── seeders/              Idempotent reference-data seeders
└── factories/
routes/
├── api.php               Bootstrap routes
└── <Domain>/*.php        One file per resource, registered in bootstrap/app.php
docker/                   Nginx, PHP-FPM, Supervisor and entrypoint config
docs/                     Deployment, development workflow and secrets guides
tests/                    Feature and unit tests (PHPUnit)
```

All route groups are registered in [`bootstrap/app.php`](bootstrap/app.php) under the `api/v1`
prefix. In total there are about 900 route definitions.

---

## Getting started

### Requirements

- PHP **8.3** with `bcmath`, `gd`, `intl`, `zip`, `pdo_mysql`, `exif`, `pcntl` and the **`mongodb`** PECL extension
- Composer 2
- MySQL 8 or MariaDB 10.11
- MongoDB 6+
- Optional: Redis and an S3-compatible bucket

### Install

```bash
git clone https://github.com/smart-school-suite/school_smart_suite_api.git
cd school_smart_suite_api
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` (see [Configuration](#configuration)), then create the schema and the reference data:

```bash
php artisan migrate
php artisan db:seed
```

### Run

```bash
php artisan serve
```

Generated timetables, notifications and other side effects run as queued jobs. Run a worker in
another terminal:

```bash
php artisan queue:work
```

To run the scheduled tasks locally:

```bash
php artisan schedule:work
```

Check that the API is up:

```bash
curl http://localhost:8000/up
```

### Docker

```bash
docker build -t school-smart-api .
docker run --env-file .env -e RUN_MIGRATIONS=true -p 8080:80 school-smart-api
```

On start, the entrypoint runs migrations and seeders when `RUN_MIGRATIONS=true`. In production it
also caches config, routes and views, and it starts the scheduler when `APP_ENV=production` or
`ENABLE_SCHEDULER=true`. Nginx and PHP-FPM run under Supervisor.

---

## Configuration

> **Note:** the main database connection reads **`MYSQL_DB_*`** variables, not Laravel's default
> `DB_*` (see [`config/database.php`](config/database.php)). `.env.example` still shows the
> default `DB_*` block, so add the `MYSQL_DB_*` keys yourself.

| Group | Variables |
|---|---|
| App | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `TRUSTED_PROXIES` |
| MySQL | `MYSQL_DB_CONNECTION`, `MYSQL_DB_HOST`, `MYSQL_DB_PORT`, `MYSQL_DB_DATABASE`, `MYSQL_DB_USERNAME`, `MYSQL_DB_PASSWORD` |
| MongoDB | `MONGO_DB_URI`, `MONGO_DB_DATABASE` (default `sss_analytics`) |
| Queue and cache | `QUEUE_CONNECTION`, `CACHE_STORE`, `REDIS_*` |
| Search | `SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST`, `MEILISEARCH_KEY` |
| Storage | `FILESYSTEM_DISK`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_URL` |
| Email | `MAIL_MAILER`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| Real-time | `BROADCAST_CONNECTION`, `ABLY_KEY`, `PUSHER_*` |
| Push | `FIREBASE_CREDENTIALS` (path to the service account JSON) |
| AI | `GEMINI_KEY`, `GEMINI_MODEL`, `GEMINI_BASE_URL` |
| Monitoring | `SENTRY_LARAVEL_DSN`, `SENTRY_ENVIRONMENT`, `SENTRY_TRACES_SAMPLE_RATE` |
| Container | `RUN_MIGRATIONS`, `ENABLE_SCHEDULER` |

Production and staging secrets are managed in Infisical and written to `.env` on every deploy.
See [docs/secrets.md](docs/secrets.md).

---

## API conventions

- **Base path:** `/api/v1/<resource>`, for example `/api/v1/exam`, `/api/v1/tuition-fee` or
  `/api/v1/semester-timetable`.
- **Format:** JSON requests and responses. Send `Accept: application/json`.
- **IDs:** UUID strings.
- **Health check:** `GET /up`.

Every response uses the same envelope, built by
[`ApiResponseService`](app/Services/ApiResponseService.php):

```json
{
  "status": "success",
  "message": "Exams fetched successfully",
  "data": [ ... ],
  "errors": null,
  "meta": {
    "pagination": { "total": 120, "per_page": 15, "current_page": 1, "last_page": 8 }
  }
}
```

Errors use `"status": "error"`, a readable `message` and an `errors` object (for example field
validation errors), with the matching HTTP status code.

---

## Authentication

Each actor has its own auth routes:

| Actor | Prefix |
|---|---|
| School admin | `/api/v1/auth/school-admin` |
| Teacher | `/api/v1/auth/teacher` |
| Student | `/api/v1/auth/student` |
| App admin | `/api/v1/auth/app-admin` |

A typical school admin flow:

1. `POST /login` with email and password. The API sends a one-time code by email.
2. `POST /verify-otp` with the code. The response contains a **Sanctum token**.
3. Call protected endpoints with `Authorization: Bearer <token>`.
4. `GET /me` returns the signed-in user, and `POST /logout` revokes the token.

The same prefixes also hold OTP resend, password reset (with OTP), password change and first-time
super-admin registration (`POST /register/super-admin`).

---

## Multi-tenancy

Every school user belongs to a **school branch**. On protected routes, the
[`IdentifyTenant`](app/Http/Middleware/IdentifyTenant.php) middleware loads the signed-in user's
branch, with its school, and stores it on the request as `currentSchool`. Services take this
object and scope every query to the branch, so one branch can never read another's data. Other
middleware enforces account status (`*AccountControl`) and the student, teacher, parent and admin
limits of the school's subscription plan (`Limit*`).

---

## Timetable engines

The API includes two constraint-based schedulers written in PHP, in [`app/Schedular`](app/Schedular).

### Semester timetable

Builds a weekly timetable for a specialty from its courses, teacher availability and course
preferences, halls, joint courses and the school's constraints (constraint categories and types).
Each generation creates a new **version**, so schools can compare versions and keep one active.
Results and diagnostics are stored in MongoDB. The **interpreter**
([`app/Interpreter/SemesterTimetable`](app/Interpreter/SemesterTimetable)) explains the engine's
output per teacher, course, hall and assignment. It lists constraint violations and suggests fixes,
so an admin can see why something could not be placed.

### Exam timetable

Schedules one exam. It gives every course a date, a start time, halls and invigilators, and it
guarantees that:

- exams for the same students never overlap;
- every candidate gets a seat, using as few halls as possible;
- every hall has enough invigilators, based on the number of candidates;
- **no invigilator supervises a course they teach**;
- no hall or person is in two places at once, including commitments in other active exam timetables.

It spreads exams across the exam period and reports any constraint it could not satisfy as a
structured diagnostic. The full design is in
[`app/Schedular/ExamTimetable/README.md`](app/Schedular/ExamTimetable/README.md).

Both engines run as **queued jobs**. They broadcast progress events, and clients poll the version
status endpoints, for example `GET /api/v1/exam-timetable/version/{versionId}/status`.

---

## Promotion engine

`php artisan students:promote` runs every night, and promotion runs can also be started from the
API. Each student is checked against rules set in the branch settings, implemented in
[`app/Services/PromotionRules`](app/Services/PromotionRules):

| Rule | Checks |
|---|---|
| `ExamScoreRuleService` | Exam performance |
| `EvaluationRuleService` | Continuous assessment and evaluation results |
| `ResitRuleService` | Outstanding resits |
| `TuitionFeeRuleService` | Tuition debt against `promotion.max_tuition_fee_debt` |
| `AdditionalFeeRuleService` | Unpaid additional fees |

Each run and each student decision is logged in MongoDB (`PromotionRunLog`,
`StudentPromotionLog`) with the reasons, so every promotion or hold-back can be explained.

---

## Background work and scheduling

There are more than 100 queued jobs in [`app/Jobs`](app/Jobs). They send email and push
notifications, create records in bulk (for example exam candidates and academic years), clean up
data, update statistics and generate timetables.

Scheduled tasks are defined in [`routes/console.php`](routes/console.php) and run in the
`Africa/Douala` timezone:

| Command | When | Does |
|---|---|---|
| `semesters:update-statuses` | Daily 00:00 | Moves semesters between statuses |
| `students:promote` | Daily 00:00 | Runs automatic promotion |
| `exams:create-missing-candidates` | Daily 01:00 | Adds candidates missing from exams |
| `admin:activation-code-expire-reminder-notification` | Daily 09:00 | Warns admins about expiring activation codes |
| `teacher:subscription-renewal-reminder-notification` | Daily 10:00 | Teacher renewal reminders |
| `student:subscription-renewal-reminder-notification` | Daily 11:00 | Student renewal reminders |
| `admin:activation-code-renewal-reminder-notification` | Daily 12:00 | Admin renewal reminders |
| `create-system-academic-year-command` | Monthly, 1st | Creates upcoming system academic years |
| `elections:update-statuses` | Every 15 min | Opens and closes election phases |

Other commands: `exams:update-statuses`, `birthday:send`, `exam:good-luck-wish-send`, and
generators for this project's layout (`make:service`, `make:requests`, and `make:migration --group=`).

> **Known gap:** the Docker image runs the scheduler but **not a queue worker**. Until a
> `queue:work` Supervisor program is added, queued jobs need a worker started some other way.
> See [docs/deployment.md](docs/deployment.md).

---

## Real-time and notifications

- **Broadcasting:** timetable generation progress, election votes (`VoteCastEvent`) and analytics
  events are broadcast. Clients get an Ably token from `GET /api/v1/auth/ably-token`.
- **Push:** Firebase Cloud Messaging, sent to the devices registered in `UserDevices`.
- **Email:** Resend.
- **In-app:** Laravel database notifications, read through the student, teacher and school admin
  notification routes.

---

## Analytics

[`app/Analytics`](app/Analytics) has five engines: **academic**, **enrolment**, **financial**,
**operational** and **election**. Each comes with projections that turn domain events into
snapshots and time series stored in MongoDB. The `academic-stats`, `financial-stats` and
`operational-stats` endpoints read these precomputed values, so dashboards load quickly without
heavy queries on the main database.

---

## Testing

```bash
php artisan test
```

PHPUnit feature and unit tests live in [`tests/`](tests). They currently cover academic years,
exams, exam joint courses, exam timetables, promotion, school branch settings, school events and
semesters. Code style follows Laravel Pint:

```bash
./vendor/bin/pint
```

---

## Deployment

```
push to master / staging
   ├── build-push ─────── builds the Docker image and pushes it to GHCR
   ├── migration-check ── applies new migrations to the deployed schema, seeds twice, caches routes
   └── deploy ─────────── (only if both pass) writes .env from Infisical, pulls the image,
                          recreates the container → migrate → seed → cache → start
```

| | Production | Staging |
|---|---|---|
| Branch | `master` | `staging` |
| Scheduler | On | Off unless `ENABLE_SCHEDULER=true` |

Details:
- [docs/deployment.md](docs/deployment.md): the pipeline, environments, database and known gaps
- [docs/development-workflow.md](docs/development-workflow.md): rules for migrations, seeders,
  routes and config
- [docs/secrets.md](docs/secrets.md): how secrets are managed

---

## Contributing

Read [docs/development-workflow.md](docs/development-workflow.md) first. The four rules:

1. **Never edit a migration that has been pushed.** Add a new one.
2. **Every seeder must be safe to run more than once.** Production seeds on every deploy.
3. **Every route name must be unique.** Production caches routes.
4. **Change configuration in Infisical, never on the server.**

Work on a feature branch, open a pull request to `staging` or `master`, and wait for the
**migrations** check to pass before merging.
