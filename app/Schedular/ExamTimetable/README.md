# Exam timetable scheduler

Generates an exam timetable for one exam (one specialty), following the same flow as the semester timetable.

## Flow

```
POST /api/v1/exam-timetable/generate
  → ExamTimetableGenerationController → ExamTimetableGenerationDispatcher
      → next ExamTimetableVersion (status: generating) + a SystemJob
      → GenerateExamTimetable job
          1. gather data; setup problems a user must fix (no candidates, no exam halls,
             a joint course without a time...) are stored as ExamTimetableError and stop the run
          2. build the scheduler input (see Context/RequestContext.php for its shape)
          3. SchedularEngine::run()
          4. store an ExamTimetable document (Mongo): slots, input, raw diagnostics
          5. update the version status and broadcast examTimetable.generation events
  → GET /api/v1/exam-timetable/version/{versionId}/status | slots | payload | raw-diagnostics | errors
```

## Engine stages

| Stage | Class | Does |
|---|---|---|
| 1 | `Grid/DayBuilder` | Checks the hard constraints (exam period, opening hours, durations), builds one day per exam date and places joint course exams at their fixed time. A failure stops the run. |
| 2 | `ConstraintRegistry` (before placement) | Places `requested_assignment` and `course_requested_slot` requests when nothing is in the way. |
| 3 | `Placement/Engine/PlacementEngine` | Gives every other course a date, a start time, halls and invigilators. |
| 4 | `ConstraintRegistry` (after placement) | Checks `hall_requested_slot`, `invigilator_requested_slot` and `student_daily_load_range`. |
| 5 | `Builders/ResponseBuilder` | Status (`optimal`, `partial`, `error`), timetable by date, diagnostics. |

An exam course is placed exactly once. That is why requests that fix a course in place run before placement,
instead of overwriting the result afterwards as the semester timetable does.

## Rules the placement always keeps

- Exams of the same students never overlap.
- Every candidate gets a seat. Halls are filled using as few halls as possible: the smallest hall that seats
  everyone, otherwise the largest halls until everyone is seated. A hall another exam uses at exactly the same
  time can share its free seats; any other overlap makes the hall unavailable.
- Every hall gets its own invigilators: one, or one per `candidates_per_invigilator` candidates.
- **An invigilator never invigilates a course they teach** (to prevent exam malpractice), whatever is requested.
  Courses taught come from `teacher_course_preferences`.
- Nobody and no hall is in two places at once, including commitments in other exams' active timetables
  (`Services/ExamTimetable/Support/ExamActiveCommitments`).

## How it chooses

- **Courses**: hardest first: the more invigilators teach a course, the earlier it is placed; then the longest exams.
- **Dates** (`ExamDateScoring`): days below the daily minimum first, then empty days furthest from other exam days
  (spreading exams over the period), then the least busy days. Days at the daily maximum are only used when
  nothing else works, and that is reported.
- **Times** (`ExamSlotScore`): 15 minute steps inside the opening hours, the one leaving the longest break to the
  other exams of the day first.
- **Invigilators**: requested ones first, then whoever has invigilated least that day and overall.

## Diagnostics

Each failed constraint becomes `{constraint_failed, blockers}`. `DiagnosticRegistry` and `BlockerRegistry` turn them
into DTOs with stable ids. A course that cannot be placed anywhere is a hard `course_placement` failure; its blockers
say what was missing: seats (`hall_unavailable`), invigilators (`invigilator_unavailable`, plus
`invigilator_teaches_course` when the free invigilators all teach it), or time (`operational_period_violation`).

## Solutions

`Solution/` works out ways to fix what a run could not satisfy, like `SemesterTimetable/Solution`:

```
diagnostics -> Issue[] -> PathPlanner -> SolutionPath[] -> grouped by date ("general" for issues without one)
```

- Only hard issues get paths when any hard constraint failed; otherwise the soft ones do.
- Each blocker and constraint key has a definition in `Solution/Definitions` (registered in `Catalog/ConstraintCatalog`)
  that explains it (`title()`, `reason()`) and proposes remedies: move or remove a request, swap a hall or
  invigilator, add halls to an assignment, open a date longer, allow an excluded date, change an exam length,
  raise the daily limit on one date, or a manual step.
- An invigilator who teaches a course is never offered for its exam. When nobody else is free, the remedy is a
  manual step: assign another invigilator.
- Joint course exams are locked: their time is never changed.
- **Every path without manual steps is verified** by applying it to the scheduler input
  (`Support/RequestShape`) and running the scheduler again. It is verified when its issues are gone and no new
  problem appeared; paths that fail are never offered. Paths with manual steps have `verified: null`.
- `applyToRequest()` of each definition changes the stored generation request for one step;
  `ExamApplySolutionService` applies the chosen paths and generates a new version.

| Endpoint | Does |
|---|---|
| `GET /api/v1/exam-timetable/version/{versionId}/solutions` | problems and their options, grouped by date |
| `POST /api/v1/exam-timetable/version/{versionId}/solutions/apply` | applies `solution_ids[]` and generates a new version |

## Activation

`ActivateExamTimetableService` makes a generated version the one in use for its exam:

| Endpoint | Does |
|---|---|
| `GET /api/v1/exam-timetable/version/{versionId}/activation-check` | `can_activate`, the conflicts, the version it replaces |
| `POST /api/v1/exam-timetable/version/{versionId}/activate` | activates it (422 with the conflicts when it clashes) |
| `GET /api/v1/exam-timetable/exam/{examId}/active-timetable` | the active version of an exam |

Only `optimal` and `partial` versions of exams that have not ended can be activated. Activation checks again
for clashes with other exams' active timetables (a hall at an overlapping time, unless shared at exactly the same
time within its capacity; a person invigilating two exams at once), since others may have been activated since
generation. It then writes `exam_timetable_slots`, `exam_session_halls` and `exam_session_invigs` (each
invigilator linked to their hall), replaces the exam's previous active version, and from then on those halls and
invigilators are busy for other exams' generation.

## Adding a constraint

1. Add its constraint and violation keys under `App\Constant\Constraint\ExamTimetable` and `App\Constant\Violation\ExamTimetable`.
2. Write the handler (`Constraints/Handlers`, with its `stage()`), and any validator it needs.
3. Add a diagnostic builder and the blocker builders, and register them in `DiagnosticRegistry` and `BlockerRegistry`.
4. Register the handler in `ConstraintRegistry` and read its input in `RequestContext`.
5. Add it to `GenerateExamTimetableRequest` and to `GenerateExamTimetable::buildRequestBody()`.

## Joint exams

A course taken by several specialties is examined once, at the same time for all of them. Its time is set before
the specialties' exam timetables are generated, like the semester joint course timetable
(`Services/ExamJointCourse`):

1. When an exam is created, `CreateExamJointCourseJob` links it to one joint exam (`exam_jcs`, per academic year
   and exam type) for each of its courses another specialty also takes. `POST /exam/{examId}/sync` does the same
   for exams created earlier.
2. `POST /{examJointCourseId}/generate` (with the exam `duration`) lists the free times, by date: inside every linked
   exam period, when no linked student sits another exam (active timetables and other joint exams), with enough free
   exam hall seats for all candidates together and enough free invigilators who do not teach the course.
3. The user picks one: `POST /{examJointCourseId}/proposals/{proposalId}/confirm` with its `option_id`
   (or `PUT /{examJointCourseId}/slot` with a date and times). It is checked again and saved as the joint exam's
   single time (`exam_jc_slots`), replacing any earlier one.
4. Every linked exam's timetable then places the course at that time (`required_joint_course_periods`).

Endpoints are under `/api/v1/exam-joint-course-timetable`; `GET /terms` and `GET /term/{schoolYearId}/{examTypeId}`
list the joint exams and which still need a time.
