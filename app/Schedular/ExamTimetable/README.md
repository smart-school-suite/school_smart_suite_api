# Exam timetable scheduler

Generates the exam timetable of one exam (one specialty's candidates) and explains, with appliable solutions,
anything it could not do. It follows the semester timetable flow: generate a version, read its status, slots,
errors, diagnostics and solutions, apply a solution (which generates a new version), then activate a version.

## Engine

```
SchedularEngine::run(payload)
  DayBuilder        one grid entry per operational exam date; joint course sessions fixed in it      (hard)
  Constraints       requested_assignment, course_requested_slot pinned                              (soft)
  PlacementEngine   every other course gets a date, time, halls and invigilators                   (hard)
  Constraints       invigilator_requested_slot, hall_requested_slot, student_daily_load_range checked (soft)
  ResponseBuilder   status, timetable, unscheduled courses, diagnostics, solution paths
```

Status is `error` when any hard constraint failed (a joint course could not be held, a course got no exam),
`partial` when only requests or the daily range were not honoured, `optimal` otherwise.

Rules the engine always keeps:

- Students never sit two exams at once (plus `session_duration.gap_minutes` between exams, default 0).
- Every candidate has a seat. A hall is free, *shared* (another exam already sits there for exactly the same
  window and seats are left, its invigilators are reused) or busy. One free hall that fits is preferred, then
  one shared hall, then the fewest halls that fit together.
- Every hall without invigilators gets one free invigilator. People who teach the course are used only when
  nobody else is free; the least loaded people go first.
- Halls and invigilators booked by other exams' **active** timetables are taken into account. Other exams'
  invigilators are matched to this exam's invigilators by the person behind them.

Placement: courses are placed longest exam first. Dates are tried from least to most busy within the daily
maximum; dates still below the daily minimum are filled first. Within a date the smallest free stretch that holds
the exam is used, flush against the neighbouring exam, so the day is not cut into unusable pieces. Only when no
date works within the maximum is it exceeded (reported as a `student_daily_load_range` warning).

### Payload

Built by `GenerateFixedExamTimetable` (see `Context/RequestContext.php` for every field):

```json
{
  "start_date": "2026-12-07", "end_date": "2026-12-11", "specialty_id": "...", "candidate_count": 120,
  "courses": [{ "course_id": "...", "course_name": "..." }],
  "halls": [{ "hall_id": "...", "hall_name": "...", "capacity": 80 }],
  "invigilators": [{ "invigilator_id": "exam_invigilators.id", "name": "...", "course_taught": ["course id"] }],
  "hall_busy_slots": [{ "hall_id": "...", "date": "...", "start_time": "09:00", "end_time": "11:00", "invigilators": [], "candidate_groups": [] }],
  "invigilator_busy_slots": [{ "invigilator_id": "...", "date": "...", "start_time": "...", "end_time": "..." }],
  "hard_constraints": { "operational_period": {}, "session_duration": {}, "required_joint_course_periods": [] },
  "soft_constraints": { "requested_assignment": [], "course_requested_slot": [], "invigilator_requested_slot": [],
                        "hall_requested_slot": [], "student_daily_load_range": {} }
}
```

Constraint values have the same keys and shapes as the generate request (`GenerateExamTimetableRequest`).
`Solution/Support/ExamPayload` maps between the two, so every solution step is written once, against the
request, and replayed on the payload to verify it.

## Diagnostics

Violations are `{constraint_failed: {key, ...}, blockers: [{key, ..., conflict}]}`. `DiagnosticRegistry` and
`BlockerRegistry` turn them into `DiagnosticDTO` / `BlockerDTO` with the builder each catalog definition names.
Ids hash the identity fields only, so the same problem keeps its id between runs.

| Constraint (issue) | Severity | Typical blockers |
|---|---|---|
| `required_joint_course_periods` | hard | `operational_period_violation`, `joint_course_period_violation`, `hall_unavailable`, `invigilator_unavailable` |
| `course_exam_session` | hard | `operational_period_violation` (no free time), `hall_unavailable`, `invigilator_unavailable` |
| `requested_assignment`, `course_requested_slot` | soft | operational period, session duration, joint course, other requests, `hall_busy`, `hall_capacity_exceeded`, `invigilator_busy` |
| `invigilator_requested_slot`, `hall_requested_slot` | soft | operational period, `invigilator_busy`, `hall_busy`, `no_exam_in_window` |
| `student_daily_load_range` | soft | `student_daily_load_violation` |

## Solutions

`Solution/` mirrors `SemesterTimetable/Solution` (see its README): issues, remedies and solution paths with the
`adjust_request`, `clear_obstacles`, `keep_one` and `shared_fix` strategies, ranked by issues fixed, verified,
then cost. Differences:

- Paths are grouped by exam date (`Y-m-d`); courses that got no exam are grouped under `unscheduled`.
- Every path whose steps can all be applied is **verified** by applying them and running the scheduler again:
  `verified: true` when the issues it fixes are gone and no new blocking problem appeared. Paths with a manual
  step (more halls, more invigilators, other exam dates) are `verified: false`. When a group has verified paths,
  unverified ones are dropped.
- Joint course sessions are locked: they never move, only what is in their way changes.
- Sessions the engine placed are not commitments when looking for a new time; joint courses, other pinned
  requests and other exams' bookings are.

Remedies that apply to the request: move or remove a request, longer hours on a date
(`operational_period.date_exceptions`), reopen an excluded date, change a course's exam length
(`session_duration.course_exceptions`), swap or drop the hall / invigilator named by a request, relax the
daily range on a date (`student_daily_load_range.date_exceptions`).

To add a constraint: add its constants, validator, diagnostic and blocker builders, one definition under
`Solution/Definitions`, and register the definition in `Solution/Catalog/ConstraintCatalog.php`.

## API (`/api/v1/exam-timetable`)

| Endpoint | Does |
|---|---|
| `POST /generate` | queues generation, returns the version (`version_id` regenerates an existing one) |
| `GET /version/{id}/status` | scheduler status of the version |
| `GET /version/{id}/errors` | setup problems of a `failed` version (no courses, candidates, halls, invigilators) |
| `GET /version/{id}/slots` | exam sessions by date, with halls and invigilators, plus unscheduled courses |
| `GET /version/{id}/payload` | the generate request used |
| `GET /version/{id}/raw-diagnostics` | flat list of failed constraints |
| `GET /version/{id}/parsed-diagnostics` | the same in plain language |
| `GET /version/{id}/solutions` | problems and ranked options by date (`dates[]`, `unscheduled_courses`, `setup_problems`) |
| `POST /version/{id}/solutions/apply` | applies `solution_ids[]` and generates a new version |
| `GET /version/{id}/activation-check` | clashes with other active exam timetables |
| `POST /version/{id}/activate`, `POST /create/active` | writes the sessions to the exam timetable tables and makes the version active |
| `GET /exam/{examId}/active-timetable` | the active version of an exam |

Versions are listed, created and deleted under `/api/v1/exam-timetable/version`. Progress is broadcast on
`schoolBranch.{branch}.schoolAdmin.{admin}.examTimetable` as `examTimetable.generation`, every event with its
`version_id`.
