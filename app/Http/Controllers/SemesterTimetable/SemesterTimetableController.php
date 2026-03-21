<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Jobs\SemesterTimetable\GenerateFixedSemesterTimetable;
use App\Jobs\SemesterTimetable\GeneratePreferenceSemesterTimetable;
use App\Models\Job\SystemJob;
use App\Models\SchoolSemester;
use App\Services\ApiResponseService;
// use Illuminate\Http\Request;
use App\Services\SemesterTimetable\GeneratePreferenceSemesterTimetableService;
use App\Services\SemesterTimetable\GenerateFixedSemesterTimetableService;
use App\Services\SemesterTimetable\SemesterTimetableService;

class SemesterTimetableController extends Controller
{
    protected GeneratePreferenceSemesterTimetableService $preferenceTimetableService;
    protected GenerateFixedSemesterTimetableService $fixedTimetableService;
    protected SemesterTimetableService $semesterTimetableService;

    public function __construct(
        GeneratePreferenceSemesterTimetableService $preferenceTimetableService,
        GenerateFixedSemesterTimetableService $fixedTimetableService,
        SemesterTimetableService $semesterTimetableService
    ) {
        $this->preferenceTimetableService = $preferenceTimetableService;
        $this->fixedTimetableService = $fixedTimetableService;
        $this->semesterTimetableService = $semesterTimetableService;
    }

    public function generatePreferenceTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $systemJob = SystemJob::create([
            'type' => "Semester Timetable Generation",
            'context_type' => SchoolSemester::class,
            'stage' => "Processing",
            'context_id' => $request->validated()['school_semester_id'],
            'initiated_by_id' => $this->resolveUser()->id,
            'initiated_by_type' => $this->resolveUser()::class,
            'queue' => "database",
            'started_at' => now(),
            'payload' => $request->validated(),
        ]);
        GeneratePreferenceSemesterTimetable::dispatch(
            $currentSchool,
            $request->validated(),
            $systemJob->id,
        );
        return ApiResponseService::success("Timetable generation initiated successfully", null, null, 200);
    }
    public function generateFixedTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $systemJob = SystemJob::create([
            'type' => "Semester Timetable Generation",
            'context_type' => SchoolSemester::class,
            'context_id' => $request->validated()['school_semester_id'],
            'initiated_by_id' => $this->resolveUser()->id,
            'initiated_by_type' => $this->resolveUser()::class,
            'queue' => "database",
            'payload' => $request->validated(),
        ]);
        GenerateFixedSemesterTimetable::dispatch(
            $currentSchool,
            $request->validated(),
            $systemJob->id,
        );
        return ApiResponseService::success("Timetable generation initiated successfully", null, null, 200);
    }

    public function getParsedTimetableDiagnostics(string $timetableVersionId){
        $parsedDiagnostics = $this->semesterTimetableService->getTimetableParsedDiagnostics($timetableVersionId);
        return ApiResponseService::success("Timetable diagnostics retrieved successfully", $parsedDiagnostics, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
