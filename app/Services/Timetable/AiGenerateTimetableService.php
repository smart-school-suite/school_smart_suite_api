<?php

namespace App\Services\Timetable;

use App\Exceptions\AppException;
use App\Models\InstructorAvailabilitySlot;
use App\Models\SchoolSemester;
use App\Models\SemesterTimetable\SemesterTimetableDraft;
use App\Models\SemesterTimetable\SemesterTimetablePrompt;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Models\SpecialtyHall;
use App\Models\TeacherCoursePreference;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;
use App\Services\SemesterTimetableAI\GeminiIntentService;
use App\Services\SemesterTimetableAI\GeminiJsonService;
use App\Services\SemesterTimetableScheduler\PreferenceSchedulingClient;
use Carbon\Carbon;

class AiGenerateTimetableService
{
    protected PreferenceSchedulingClient $schedulingClient;
    public function __construct(
        protected GeminiIntentService $geminiIntentService,
        protected GeminiJsonService $geminiJsonService,
        PreferenceSchedulingClient $schedulingClient,
    ) {
        $this->schedulingClient = $schedulingClient;
    }

    public function generateTimetable(array $data, object $currentSchool): array
    {

        if (isset($data['draft_id'])) {
            $draft = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
                ->where('id', $data['draft_id'])
                ->firstOrFail();
        } else {
            $draft = $this->createTimetableDraft($data, $currentSchool);
        }

        $timetablePrompt = SemesterTimetablePrompt::create([
            'school_branch_id' => $currentSchool->id,
            'user_prompt' => $data['prompt'],
            'school_semester_id' => $data['school_semester_id'],
            'draft_id' => $draft->id,
        ]);
        $stringent = $this->geminiIntentService->classify($data['prompt']);

        if ($stringent['is_unrelated']) {
            return [
                'is_unrelated' => true,
                'message' => "I’m here to help specifically with semester timetables. You can ask me to create a timetable, adjust class schedules, or add scheduling constraints to fit your academic needs.",
            ];
        }

        $semester = $this->getSchoolSemester($data['school_semester_id']);
        $teachers = $this->getTeachers($currentSchool->id, $semester->specialty_id);
        $teacherIds = $teachers->pluck('teacher_id')->toArray();

        $preferred = $this->getTeacherPreferredSchedule($currentSchool->id, $semester->id, $semester->specialty_id, $teacherIds);
        if ($preferred->isEmpty()) {
            throw new AppException(
                "Teacher Prefered Teaching Slot Not Added",
                404,
                "Teacher Preferred Teaching Period Not Added",
                "Teacher Preferred Teaching for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level} please ensure that all teachers have added their preferred teaching times"
            );
        }

        $teacherCourses = $this->getTeacherCourses($currentSchool->id, $teacherIds, $semester);
        if ($teacherCourses->isEmpty()) {
            throw new AppException(
                "No Courses Assigned to teacher",
                404,
                "No Courses Assigned to teacher",
                "No Courses Assigned to this teachers found for {$semester->semester->name} {$semester->specialty->specialty_name}, {$semester->specialty->level->level}"
            );
        }

        $halls = $this->getHalls($currentSchool->id, $semester->specialty_id);

        $hallBusy = $this->getHallBusyPeriods($currentSchool->id, $halls);
        $teacherBusy = $this->getTeacherBusyPeriods($currentSchool->id, $teacherIds);

        $promptResponse = $this->geminiJsonService->generateStructuredJson(
            $data['prompt'],
            $this->buildPromptPayload($teacherCourses, $teachers, $halls)
        );


        $timetableVersion = $this->createTimetableVersion($data, $currentSchool, $draft->id, 'in_progress');
        if (isset($data['parent_version_id'])) {
            $timetablePrompt->update([
                'base_version_id' => $data['parent_version_id'],
                'result_version_id' => $timetableVersion->id,
                'scheduler_input' => $promptResponse,
            ]);
        } else {
            $timetablePrompt->update([
                'base_version_id' => null,
                'result_version_id' => $timetableVersion->id,
                'scheduler_input' => $promptResponse,
            ]);
        }
        $schedulerInput =  $this->buildBody(
            $preferred,
            $teachers,
            $teacherBusy,
            $teacherCourses,
            $halls,
            $hallBusy,
            $promptResponse
        );
        $schedulerResponse = $this->schedulingClient->scheduleWithPreferences($schedulerInput);
        return [
            'scheduler_response' => $schedulerResponse,
            'prompt_response' => $promptResponse,
        ];
        $this->createTimetableVersionSlots($timetableVersion->id, $currentSchool, $promptResponse);
    }
    private function getSchoolSemester(string $id): SchoolSemester
    {
        return SchoolSemester::with(['specialty.level', 'semester'])->findOrFail($id);
    }

    private function getTeachers(string $branchId, string $specialtyId)
    {
        $q = TeacherSpecailtyPreference::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('teacher')
            ->get();

        if ($q->isEmpty()) {
            throw new AppException(
                "No Teachers Found",
                404,
                "No Teachers Found",
                "No Teachers Found for specialty {$specialtyId} — please make sure teachers have been assigned to this specialty before creating the timetable"
            );
        }

        return $q;
    }

    private function getTeacherPreferredSchedule(string $branchId, string $semesterId, string $specialtyId, array $teacherIds)
    {
        return InstructorAvailabilitySlot::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->where('school_semester_id', $semesterId)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }

    private function getTeacherCourses(string $branchId, array $teacherIds, SchoolSemester $semester)
    {
        return TeacherCoursePreference::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->whereHas('course', fn($q) => $q->where('semester_id', $semester->semester_id)
                ->where('specialty_id', $semester->specialty_id))
            ->with(['course.types', 'teacher'])
            ->get();
    }

    private function getHalls(string $branchId, string $specialtyId)
    {
        $halls = SpecialtyHall::where('school_branch_id', $branchId)
            ->where('specialty_id', $specialtyId)
            ->with('hall.types')
            ->get();

        if ($halls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned to this specialty",
                404,
                "No Halls Found For this specialty",
                "No Halls Found for specialty {$specialtyId} — please ensure that halls have been assigned to this specialty before creating timetable"
            );
        }

        return $halls;
    }

    private function getHallBusyPeriods(string $branchId, $halls)
    {
        $hallIds = $halls->pluck('hall_id')->toArray();
        return Timetable::where('school_branch_id', $branchId)
            ->whereIn('hall_id', $hallIds)
            ->with('hall')
            ->get();
    }

    private function getTeacherBusyPeriods(string $branchId, array $teacherIds)
    {
        return Timetable::where('school_branch_id', $branchId)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->get();
    }

    private function buildPromptPayload($teacherCourses, $teachers, $halls): array
    {
        return [
            'courses' => $teacherCourses->map(fn($c) => [
                'course_id' => $c->course->id,
                'course_title' => $c->course->course_title,
                'course_type' => $c->course->types->pluck('name')->toArray(),
                'credit' => $c->course->credit,
                'course_code' => $c->course->course_code,
            ]),
            'teachers' => $teachers->map(fn($t) => [
                'teacher_id' => $t->teacher->id,
                'teacher_name' => $t->teacher->name,
            ]),
            'halls' => $halls->map(fn($h) => [
                'hall_id' => $h->hall->id,
                'hall_name' => $h->hall->name,
                'capacity' => $h->hall->capacity,
                'type' => $h->hall->types->pluck('name')->toArray(),
            ]),
        ];
    }

    private function buildBody($preferred, $teachers, $teacherBusy, $teacherCourses, $halls, $hallBusy, $promptResponse): array
    {
        return [
            'teacher_prefered_teaching_period' => $preferred->map(fn($s) => [
                'start_time' => Carbon::createFromFormat('H:i:s', $s->start_time)->format('H:i'),
                'end_time' => Carbon::createFromFormat('H:i:s', $s->end_time)->format('H:i'),
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teachers' => $teachers->map(fn($t) => [
                'teacher_id' => $t->teacher->id,
                'name' => $t->teacher->name,
            ]),
            'teacher_busy_period' => $teacherBusy->map(fn($s) => [
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
                'teacher_id' => $s->teacher_id,
                'teacher_name' => $s->teacher->name,
            ]),
            'teacher_courses' => $teacherCourses->map(fn($c) => [
                'course_id' => $c->course->id,
                'course_title' => $c->course->course_title,
                'course_credit' => $c->course->credit,
                'course_type' => "theoretical",
                'course_hours' => 45,
                'teacher_id' => $c->teacher->id,
                'teacher_name' => $c->teacher->name,
            ]),
            'halls' => $halls->map(fn($h) => [
                'hall_name' => $h->hall->name,
                'hall_id' => $h->hall->id,
                'hall_capacity' => $h->hall->capacity,
                'hall_type' => "lecture",
            ]),
            'hall_busy_periods' => $hallBusy->map(fn($s) => [
                'hall_id' => $s->hall->id,
                'hall_name' => $s->hall->name,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'day' => $s->day_of_week,
            ]),
            'break_period' => collect($promptResponse['hard_constraints'])->get('break_period'),
            'operational_period' => collect($promptResponse['hard_constraints'])->get('operational_period'),
            'periods' => collect($promptResponse['hard_constraints'])->get('periods'),
            'soft_constrains' => collect($promptResponse['soft_constraints']),
        ];
    }
    private function createTimetableDraft(array $data, object $currentSchool)
    {
        $semesterId = (string) $data['school_semester_id'];

        $existingCount = SemesterTimetableDraft::where('school_branch_id', $currentSchool->id)
            ->where('school_semester_id', $semesterId)
            ->count();

        if ($existingCount > 0) {
            throw new AppException(
                "You already have existing timetable draft(s) for this semester. Please select an existing draft to continue editing.",
                409,
                "Existing Drafts Found",
                "Please select an existing draft to continue."
            );
        }

        $timetableDraft = SemesterTimetableDraft::create([
            'name'               => 'Draft 1',
            'school_semester_id' => $semesterId,
            'school_branch_id'   => $currentSchool->id,
            'draft_count'        => 1,
        ]);

        return $timetableDraft;
    }
    private function createTimetableVersion(array $data, object $currentSchool, string $draftId, $schedulerStatus)
    {
        $timetableVersions = SemesterTimetableVersion::where("school_branch_id", $currentSchool->id)
            ->where("draft_id", $draftId)
            ->count();
        $versionNumber = $timetableVersions + 1;
        $timetableVersion = SemesterTimetableVersion::create([
            'name'               => "version {$versionNumber}",
            'parent_version_id' => $data['parent_version_id'] ?? null,
            'version_number'      => $versionNumber,
            'draft_id'           => $draftId,
            'school_branch_id'   => $currentSchool->id,
            'version_count'      => $versionNumber,
            'scheduler_status' => $schedulerStatus
        ]);
        return $timetableVersion;
    }

    private function createTimetableVersionSlots(string $timetableVersionId, object $currentSchool, $schedulerResponse)
    {
        $generatedSlots = $schedulerResponse->timetable;
        foreach ($generatedSlots as $slot) {
            Timetable::create([
                'school_branch_id' => $currentSchool->id,
                'teacher_id' => $slot->teacher_id ?? null,
                'course_id' => $slot->course_id ?? null,
                'hall_id' => $slot->hall_id ?? null,
                'day_of_week' => $slot->day,
                'break' => $slot->break,
                'duration' => $slot->duration,
                'start_time' => Carbon::createFromFormat('H:i', $slot->start_time)->format('H:i:s'),
                'end_time' => Carbon::createFromFormat('H:i', $slot->end_time)->format('H:i:s'),
                'timetable_version_id' => $timetableVersionId,
            ]);
        }
    }

    private static function  partialSchedulerResponse()
    {
        $response = '{
    "status": "PARTIAL",
    "timetable": [
        {
            "day": "Monday",
            "slots": [
                {
                    "day": "Monday",
                    "start_time": "10:00",
                    "end_time": "11:00",
                    "break": false,
                    "duration": "1hr",
                    "teacher_id": "abababab-cdcd-efef-0101-232323232323",
                    "teacher_name": "Dr. Noor",
                    "course_id": "aabbccdd-eeff-0011-2233-445566778899",
                    "course_name": "Security in Software Systems",
                    "hall_id": "70707070-8080-9090-a0a0-b0b0b0b0b0b0",
                    "hall_name": "Security Lab"
                },
                {
                    "day": "Monday",
                    "start_time": "11:00",
                    "end_time": "11:15",
                    "break": true,
                    "duration": null
                },
                {
                    "day": "Monday",
                    "start_time": "11:15",
                    "end_time": "12:15",
                    "break": false,
                    "duration": "1hr",
                    "teacher_id": "12121212-3434-5656-7878-909090909090",
                    "teacher_name": "Ms. Patel",
                    "course_id": "22334455-6677-8899-aabb-ccddeeff0011",
                    "course_name": "User Experience",
                    "hall_id": "80808080-9090-a0a0-b0b0-c0c0c0c0c0c0",
                    "hall_name": "Design Lab"
                }
            ]
        },
        {
            "day": "Friday",
            "slots": [
                {
                    "day": "Friday",
                    "start_time": "15:00",
                    "end_time": "15:45",
                    "break": false,
                    "duration": "45min",
                    "teacher_id": "13131313-2424-3535-4646-575757575757",
                    "teacher_name": "Dr. Beck",
                    "course_id": "33445566-7788-99aa-bbcc-ddeeff112233",
                    "course_name": "Parallel Computing",
                    "hall_id": "90909090-a0a0-b0b0-c0c0-d0d0d0d0d0d0",
                    "hall_name": "High Performance Lab"
                },
                {
                    "day": "Friday",
                    "start_time": "15:45",
                    "end_time": "16:00",
                    "break": true,
                    "duration": null
                },
                {
                    "day": "Friday",
                    "start_time": "16:00",
                    "end_time": "17:30",
                    "break": false,
                    "duration": "1hr30min",
                    "teacher_id": "14141414-2525-3636-4747-585858585858",
                    "teacher_name": "Prof. Ortiz",
                    "course_id": "44556677-8899-aabb-ccdd-eeff33445566",
                    "course_name": "Machine Learning Foundations",
                    "hall_id": "a0a0a0a0-b0b0-c0c0-d0d0-e0e0e0e0e0e0",
                    "hall_name": "AI Lecture Theatre"
                }
            ]
        }
    ],
    "diagnostics": {
        "constraints": {
            "hard": {
                "status": "PASSED",
                "failed": []
            },
            "soft": {
                "status": "PARTIAL",
                "failed": [
                    {
                        "title": "Late Friday classes scheduled",
                        "description": "One course was scheduled after 4pm on Friday."
                    }
                ]
            }
        },
        "summary": {
            "message": "Timetable generated, but some preferences could not be met.",
            "hard_constraints_met": true,
            "soft_constraints_met": false,
            "failed_soft_constraints_count": 1
        }
    },
    "metadata": {
        "solve_time_seconds": 0.11
    }
}';
        return json_decode($response, true);
    }
    private static function optimalSchedulerResponse()
    {
        $response = '{
    "status": "OPTIMAL",
    "timetable": [
        {
            "day": "Monday",
            "slots": [
                {
                    "day": "Monday",
                    "start_time": "08:30",
                    "end_time": "09:30",
                    "break": false,
                    "duration": "1hr",
                    "teacher_id": "11112222-3333-4444-5555-666677778888",
                    "teacher_name": "Dr. Perez",
                    "course_id": "aa11bb22-cc33-dd44-ee55-ff6677889900",
                    "course_name": "Intro to Programming",
                    "hall_id": "aaaabbbb-cccc-dddd-eeee-ffffffff1111",
                    "hall_name": "Small Lecture Room 1"
                },
                {
                    "day": "Monday",
                    "start_time": "09:30",
                    "end_time": "10:30",
                    "break": false,
                    "duration": "1hr",
                    "teacher_id": "22223333-4444-5555-6666-777788889999",
                    "teacher_name": "Ms. Green",
                    "course_id": "00112233-4455-6677-8899-aabbccddeeff",
                    "course_name": "Web Development Basics",
                    "hall_id": "bbbbaaaa-cccc-dddd-eeee-111122223333",
                    "hall_name": "Computer Lab 2"
                }
            ]
        },
        {
            "day": "Tuesday",
            "slots": [
                {
                    "day": "Tuesday",
                    "start_time": "08:30",
                    "end_time": "10:00",
                    "break": false,
                    "duration": "1hr30min",
                    "teacher_id": "33334444-5555-6666-7777-88889999aaaa",
                    "teacher_name": "Dr. Roland",
                    "course_id": "01a2b3c4-d5e6-7f8g-9h0i-1j2k3l4m5n6p",
                    "course_name": "Discrete Mathematics for Software Engineers",
                    "hall_id": "f5e4d3c2-b1a0-9876-5432-1fedcba98765",
                    "hall_name": "Medium Lecture Hall B"
                }
            ]
        },
        {
            "day": "Wednesday",
            "slots": [
                {
                    "day": "Wednesday",
                    "start_time": "10:00",
                    "end_time": "11:30",
                    "break": false,
                    "duration": "1hr30min",
                    "teacher_id": "44445555-6666-7777-8888-9999aaaabbbb",
                    "teacher_name": "Prof. Singh",
                    "course_id": "bbccddeeff-0011-2233-4455-66778899aabb",
                    "course_name": "Operating Systems",
                    "hall_id": "ccccdddd-eeee-ffff-0000-111122223333",
                    "hall_name": "Main Lecture Theatre C"
                }
            ]
        },
        {
            "day": "Thursday",
            "slots": [
                {
                    "day": "Thursday",
                    "start_time": "13:00",
                    "end_time": "15:00",
                    "break": false,
                    "duration": "2hr",
                    "teacher_id": "55556666-7777-8888-9999-aaaabbbbcccc",
                    "teacher_name": "Dr. Kim",
                    "course_id": "ccddeeff-1122-3344-5566-77889900aabb",
                    "course_name": "Database Systems",
                    "hall_id": "ddddcccc-bbbb-aaaa-9999-888877776666",
                    "hall_name": "Data Lab"
                }
            ]
        },
        {
            "day": "Friday",
            "slots": [
                {
                    "day": "Friday",
                    "start_time": "09:00",
                    "end_time": "10:30",
                    "break": false,
                    "duration": "1hr30min",
                    "teacher_id": "66667777-8888-9999-aaaa-bbbbccccdddd",
                    "teacher_name": "Ms Davis",
                    "course_id": "a7b8c9d0-e1f2-3g4h-5i6j-7k8l9m0n1p2q",
                    "course_name": "Advanced Data Structures and Algorithms",
                    "hall_id": "c1d2e3f4-a5b6-7c8d-9e0f-1a2b3c4d5e6f",
                    "hall_name": "Main Lecture Theatre A"
                }
            ]
        }
    ],
    "diagnostics": {
        "constraints": {
            "hard": {
                "status": "PASSED",
                "failed": []
            },
            "soft": {
                "status": "PASSED",
                "failed": []
            }
        },
        "summary": {
            "message": "All constraints satisfied.",
            "hard_constraints_met": true,
            "soft_constraints_met": true,
            "failed_soft_constraints_count": 0
        }
    },
    "metadata": {
        "solve_time_seconds": 0.12
    }
}';
        return json_decode($response, true);
    }
    private static function errorSchedulerResponse()
    {
        $response = '{
  "status": "ERROR",
  "timetable": {
    "timetable": [
      {
        "day": "Saturday",
        "slots": [
          {
            "day": "Saturday",
            "start_time": "09:00",
            "end_time": "10:30",
            "break": false,
            "duration": "1hr30min",
            "teacher_id": "cccceeee-ffff-0000-1111-222233334444",
            "teacher_name": "Mr. ONeil",
            "course_id": "55667788-99aa-bbcc-ddee-ff0011223344",
            "course_name": "Human-Computer Interaction",
            "hall_id": "40404040-5050-6060-7070-808080808080",
            "hall_name": "Design Studio"
          },
          {
            "day": "Saturday",
            "start_time": "10:30",
            "end_time": "11:30",
            "break": true,
            "duration": null
          },
          {
            "day": "Saturday",
            "start_time": "11:30",
            "end_time": "13:00",
            "break": false,
            "duration": "1hr30min",
            "teacher_id": "ddddeeee-ffff-1111-2222-333344445555",
            "teacher_name": "Dr. Grant",
            "course_id": "66778899-aabb-ccdd-eeff-001122334455",
            "course_name": "AI Ethics",
            "hall_id": "50505050-6060-7070-8080-909090909090",
            "hall_name": "Seminar Room 2"
          }
        ]
      }
    ],
    "diagnostics": {
      "constraints": {
        "hard": {
          "status": "FAILED",
          "failed": [
            {
              "title": "No feasible timetable",
              "description": "Teacher availability conflicts prevent scheduling all courses."
            }
          ]
        },
        "soft": {
          "status": "FAILED",
          "failed": []
        }
      },
      "summary": {
        "message": "Unable to generate a valid timetable.",
        "hard_constraints_met": false,
        "soft_constraints_met": false,
        "failed_soft_constraints_count": 100,
        "failed_hard_constraints_count": 25
      }
    },
    "metadata": {
      "solve_time_seconds": 0.09
    }
  }
}';
        return json_decode($response, true);
    }
}
