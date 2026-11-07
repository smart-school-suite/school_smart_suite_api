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

Solution paths (as in `SemesterTimetable/Solution`) are not built yet; `suggestions` is empty.

## Adding a constraint

1. Add its constraint and violation keys under `App\Constant\Constraint\ExamTimetable` and `App\Constant\Violation\ExamTimetable`.
2. Write the handler (`Constraints/Handlers`, with its `stage()`), and any validator it needs.
3. Add a diagnostic builder and the blocker builders, and register them in `DiagnosticRegistry` and `BlockerRegistry`.
4. Register the handler in `ConstraintRegistry` and read its input in `RequestContext`.
5. Add it to `GenerateExamTimetableRequest` and to `GenerateExamTimetable::buildRequestBody()`.
