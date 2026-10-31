# Semester timetable solution paths

When the scheduler cannot satisfy a constraint, this module works out concrete ways to fix it.
It runs for **hard** failures (the grid could not be built) and **soft** failures (a request could not be honoured).
Its output is stored in Mongo as `raw_suggestions` on the `SemesterTimetable` document.

If any hard constraint failed, generation failed, so only the hard issues get solution paths and soft issues are ignored.
Soft issues are planned only when every hard constraint passed.

## Flow

```
State violations -> DiagnosticRegistry -> DiagnosticDTO[] -> Issue[] -> PathPlanner -> SolutionPath[] -> grouped by day
```

- **Issue**: one failed constraint (the *request*) plus the things in its way (its *obstacles*).
- **Remedy**: one concrete change to a request or an obstacle, with a cost. Lower is better.
- **SolutionPath**: an ordered set of remedies that, applied together, resolves one or more issues.

For each day the planner groups issues that block each other or share an obstacle, then builds paths with four strategies:

| Strategy | Meaning |
|---|---|
| `adjust_request` | Change what was asked (move, resize or remove the failed constraint). |
| `clear_obstacles` | Keep the request and change everything in its way. |
| `keep_one` | In a group of competing requests, keep one and adjust the others. |
| `shared_fix` | One change to an obstacle shared by several issues fixes all of them. |

Combinations are searched best-first with a cap, so they cannot explode. Remedies that touch the same target, or
the same teacher, hall or exclusive time slot, are never combined. Paths are ranked by issues resolved, then by cost,
then by step count. A path that only adds steps to a cheaper path fixing the same issues is dropped.

## Steps build on each other

Remedies that change hard constraints carry an `apply` closure that patches the request payload (a day exception for
the operational period, period duration or break, or a no-break day). While a path is assembled, choices are taken in
`priority()` order (operational period, then period duration, then break, then the rest), and each later choice is
computed against the payload as patched by the steps before it. Every hard path is then re-checked by applying all of
its patches and running the grid validation again; `verified` is `true` when its issues are gone. When a group has
verified paths, unverified ones are dropped. Soft paths have `verified: null`.

## Locked constraints

A definition can return `true` from `isLocked()`. The planner then never moves, resizes or removes that constraint:

- When a locked constraint fails, only the things around it are changed (`clear_obstacles`).
- `keep_one` never keeps another request if that would require changing the locked one.
- When a locked constraint blocks two or more requests, a `keep_one` path keeps it and moves all of them.
  That path's `decision.target_*` points at the locked constraint.

`required_joint_course_periods` is locked. Joint courses may span several whole periods; they must start and end
on period boundaries.

## Availability

A move is only offered where the teacher and hall are free of real commitments: busy periods from other
specialties, joint courses, and other requests in the payload. Classes the placement engine put in the grid are
not commitments, because the timetable is generated again after a fix is applied. When several moved requests land
in the same slot, there must be a free hall for each of them.

## Output format

```json
{
  "monday": [
    {
      "id": "stable hash",
      "day": "monday",
      "severity": "hard | soft",
      "strategy": "adjust_request | clear_obstacles | keep_one | shared_fix",
      "verified": "true | false for hard paths, null for soft paths",
      "issue_ids": ["..."],
      "decision": { "type": "fix | keep", "target_id": "...", "target_type": "constraint key", "target_details": {} },
      "steps": [
        {
          "step": 1,
          "id": "stable hash, use it as the option id",
          "action": "move | modify | replace | remove | relax | review",
          "label": "Human readable instruction",
          "target": { "id": "...", "type": "constraint or violation key", "role": "request | obstacle" },
          "changes": [{ "field": "start_time", "from": "11:30", "to": "11:00" }],
          "result": { "day": "monday", "start_time": "11:00", "end_time": "12:00" },
          "cost": 52
        }
      ],
      "step_count": 1,
      "cost": 52,
      "summary": "Move the joint course on Monday from 11:30-13:30 to 11:00-12:00"
    }
  ]
}
```

Ids are hashes of the change itself, so the same fix keeps the same id between runs.

## Adding a constraint

1. Add its constraint and violation key constants under `App\Constant\Constraint` and `App\Constant\Violation`.
2. Write the validator, the diagnostic builder and the blocker builder, as for existing constraints.
3. Create one definition class in `Definitions/<Area>/`. Extend the base that fits:
   - `AbstractRequestDefinition` for "put X at this time" requests. Implement `accepts()`; moving and removing are inherited.
   - `AbstractLimitDefinition` for "at most N per day" limits. Implement `unit()`; relaxing the limit is inherited.
   - `AbstractResourceDefinition` for "this teacher or hall is taken" obstacles. Implement the candidate lookup.
   - `AbstractDefinition` for anything else. Implement `adjustRequest()` and/or `clearObstacle()`.
4. Return its keys and builders from `constraintKey()`, `violationKey()`, `diagnosticBuilder()` and `blockerBuilder()`.
5. Add the class to `Catalog/ConstraintCatalog.php`.

The diagnostic and blocker registries read their maps from the catalog, so step 5 registers everything.
A key the catalog does not know falls back to `GenericDefinition`, which suggests removing the request or a manual review,
so an unhandled constraint never stops path generation.

Shared helpers live in `Support/SolutionContext.php`: operational window, periods, break, joint courses,
teacher and hall availability, and nearest free windows. Use them instead of reading the payload directly.
Costs live in `Support/Cost.php`.

## Reading results through the API

`TimetableInsightService` turns a version's stored diagnostics and solution paths into plain language. Names come
from the `scheduler_input` stored with each run (or, for older runs, from the database).

| Endpoint | Returns |
|---|---|
| `GET /api/v1/semester-timetable/version/{versionId}/issues` | `version`, `summary`, `issues` |
| `GET /api/v1/semester-timetable/version/{versionId}/solutions` | `version`, `summary`, `problems` with their options |
| `GET /api/v1/semester-timetable/version/{versionId}/issues/{issueId}/solutions` | the problem containing that issue, with only the options that fix it |

- `summary`: `headline` and `message` for the version's `scheduler_status` (pending, generating, optimal, partial,
  failed, error), plus `blocking_count`, `warning_count` and `days_affected`.
- `issues[]`: `id`, `severity` (`blocking` for hard, `warning` for soft), `severity_label`, `day_label`, `time`,
  `title`, `reasons[]`, `affected[]` (course, teacher, hall with names), `can_be_changed`, `solution_count`.
  Setup problems from a `failed` version also appear here, with `fix_in` pointing at the page that fixes them.
- `problems[]`: issues that are fixed together, with `title`, `description`, `issues[]` and ranked `options[]`.
- `options[]`: `rank`, `recommended`, `title`, `summary`, `fixes` (`count`, `of`, `all`, `label`), `effort`
  (`small`, `medium`, `large`), `checked` (the hard path was re-verified), and `steps[]` with `action_label`, `text`
  and `changes[]` (`label`, `from`, `to`, with names and times already formatted).

Each definition's `title()` and `reason()` provide the wording, so a new constraint only needs those two methods to
read well in the API.
