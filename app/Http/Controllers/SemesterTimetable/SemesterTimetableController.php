<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\GenerateSemesterTimetableRequest;
use App\Jobs\SemesterTimetable\GenerateFixedSemesterTimetable;
use App\Jobs\SemesterTimetable\GeneratePreferenceSemesterTimetable;
use App\Models\Job\SystemJob;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\Services\ApiResponseService;
use App\Services\SemesterTimetable\CreateActiveSemesterTimetableService;
use App\Services\SemesterTimetable\GeneratePreferenceSemesterTimetableService;
use App\Services\SemesterTimetable\GenerateFixedSemesterTimetableService;
use App\Services\SemesterTimetable\SemesterTimetableService;
use Illuminate\Http\Request;

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

    public function generateTimetable(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableSettings = $this->getTimetableSetting($currentSchool);
        if (!empty($timetableSettings['timetable.ignore_teacher_preference'] ?? null)) {
            return $this->generateFixedTimetable($currentSchool, $request);
        } else {
            return $this->generatePreferenceTimetable($currentSchool, $request);
        }
    }

    protected function generatePreferenceTimetable($currentSchool, $request)
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
   protected function generateFixedTimetable($currentSchool, $request)
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
        GenerateFixedSemesterTimetable::dispatch(
            $currentSchool,
            $request->validated(),
            $systemJob->id,
        );
        return ApiResponseService::success("Timetable generation initiated successfully", null, null, 200);
    }

    public function generateTimetableWithPreference(GenerateSemesterTimetableRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $engineService = app(GeneratePreferenceSemesterTimetableService::class);
        $response = $engineService->generateTimetable($request->validated(), $currentSchool);
        return ApiResponseService::success("Timetable generated successfully", $response, null, 200);
    }

    public function getParsedTimetableDiagnostics(string $timetableVersionId)
    {
        $parsedDiagnostics = $this->semesterTimetableService->getTimetableParsedDiagnostics($timetableVersionId);
        return ApiResponseService::success("Timetable diagnostics retrieved successfully", $parsedDiagnostics, null, 200);
    }

    public function createActiveSemesterTimetable(Request $request, CreateActiveSemesterTimetableService $createActiveSemesterTimetable)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $data = $request->validate([
            'school_semester_id' => 'required|string',
        ]);
        $activeTimetable = $createActiveSemesterTimetable->createActiveSemesterTimetable($currentSchool, $data);
        return ApiResponseService::success("Active Semester Timetable Created Successfully", $activeTimetable, null, 201);
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

    private function getTimetableSetting($currentSchool){
         $settings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->whereIn('key', ['timetable.ignore_teacher_preference', 'timetable.respect_teacher_preference']);
            })
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->settingDefination->key => $setting->value];
            });
        return $settings;
    }
}
