# Spreadsheet imports

Every "Import" button in the app uploads a spreadsheet plus the column mapping the person chose,
and a queued job saves the rows. The shared parts live in one place, so a module only describes
what its rows mean.

| Piece | Where | Does |
|---|---|---|
| `ImportType` | `app/Services/Import/ImportType.php` | The job's type key, title, module, category and record name |
| `ImportFileRequest` | `app/Http/Requests/Import/` | Validates the upload and the mapping; a module lists its required columns |
| `ImportJobDispatcher` | `app/Services/Import/` | Stores the file, creates the system job, queues the import, returns the job |
| `BaseImportJob` | `app/Jobs/Import/` | Reads the file, checks columns, validates and saves each row, reports progress, groups problems, finishes the job |
| `ImportLookups` | `app/Services/Import/` | Turns names in cells (gender, level, teacher, specialty, course) into records, the same way for every import |
| `ImportErrorCollector` | `app/Services/Import/` | Groups problems by reason with row ranges: "Rows 2–21: already exists" |

## What the app can rely on

- **The job row is the source of truth.** Status, progress, counts and the result are saved on
  `system_jobs` before each `job.update` event. If an event is missed, `GET system-jobs/{id}`
  returns the same data. The app polls it while a job is running.
- **A job always ends.** It finishes as `completed`, `completed_with_issues` (some rows failed) or
  `failed` (nothing imported, or the file could not be used). A worker that dies is caught by
  `failed()`; one that dies without it (out of memory, server restart) is marked failed by
  `system-jobs:fail-stalled-imports`, which runs every five minutes.
- **Problems are grouped.** `GET system-jobs/{id}/errors` returns `groups`, each with a `reason`
  (`already_exists`, `duplicate_in_file`, `missing_value`, `invalid_value`, `not_found`,
  `not_allowed`, `unexpected_error`), one `message`, the `rows`, `row_ranges` ("2–21, 25"),
  a `summary` line and a few `examples` of the values.
- **Existing records are reported, not overwritten**, like the create forms.
- The import runs once (`$tries = 1`); a retry would report its own earlier rows as existing.

## Adding an import

1. Add a case to `ImportType` (its value is stored in `system_jobs.type`; never change it later).
2. Create the request, extending `ImportFileRequest`, and list the required columns.
3. Create the job, extending `BaseImportJob`:
   - `importType()`: the case from step 1;
   - `rules()` and `attributes()`: validation of a normalized row (messages must not contain row
     values, so rows group together);
   - `normalize()`: resolve cells to ids with `$this->lookups`, throw `ImportRowException` for a
     value that cannot be resolved;
   - `duplicateKey()`: what makes two rows of the file the same record;
   - `importRow()`: check the domain rules, throw `ImportRowException` with a reason, save, return
     `self::OUTCOME_CREATED`. It runs in a transaction.
4. In the controller, call `ImportJobDispatcher::dispatch()` and return the job with status 202.
5. Add a feature test that uploads a CSV through the route (see `tests/Feature/Import`).

Write messages for the person who uploaded the file: say what is wrong and how to fix it, and keep
the value out of the message (pass it as the exception's `value`).
