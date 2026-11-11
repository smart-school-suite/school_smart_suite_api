<?php

namespace App\Jobs\Exam;

use App\Jobs\ExamJointCourse\CreateExamJointCourseJob;
use App\Models\Exam\Exam;
use App\Models\Examtype;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolSemester;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * "Automatically Create Exams Each Semester" (exam.auto_create).
 *
 * When on, a newly created school semester gets its Continuous Assessment
 * and its Exam, with the semester's dates and the default maximum scores
 * (exam.ca_default_max_score, exam.exam_default_max_score). As with an exam
 * created by hand, the Exam's maximum includes the CA's, and nothing is
 * created for a specialty without students. Resit exams are created later
 * from exam scores, as before. Existing exams are never duplicated.
 */
class AutoCreateSemesterExamsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public const AUTO_CREATE = 'exam.auto_create';
    public const CA_MAX_SCORE = 'exam.ca_default_max_score';
    public const EXAM_MAX_SCORE = 'exam.exam_default_max_score';

    private const DEFAULT_CA_MAX_SCORE = 30;
    private const DEFAULT_EXAM_MAX_SCORE = 70;

    public function __construct(
        protected string $schoolBranchId,
        protected string $schoolSemesterId,
    ) {}

    public function handle(): void
    {
        $settings = $this->settings();
        if (!filter_var($settings[self::AUTO_CREATE] ?? true, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $semester = SchoolSemester::where('school_branch_id', $this->schoolBranchId)
            ->with('schoolYear')
            ->find($this->schoolSemesterId);

        $specialtyId = $semester?->schoolYear?->specialty_id;
        if (!$specialtyId) {
            Log::warning("School semester {$this->schoolSemesterId} or its school year was not found; no exams auto created.");
            return;
        }

        $hasStudents = Student::where('school_branch_id', $this->schoolBranchId)
            ->where('specialty_id', $specialtyId)
            ->exists();
        if (!$hasStudents) {
            return;
        }

        $examTypes = Examtype::where('semester_id', $semester->semester_id)
            ->whereIn('type', ['ca', 'exam'])
            ->get()
            ->keyBy(fn($type) => strtolower($type->type));

        $caType = $examTypes->get('ca');
        $examType = $examTypes->get('exam');
        if (!$caType) {
            // The Exam needs the CA to exist first (its maximum includes the CA's).
            return;
        }

        $caMax = $this->score($settings[self::CA_MAX_SCORE] ?? null, self::DEFAULT_CA_MAX_SCORE);
        $examMax = $this->score($settings[self::EXAM_MAX_SCORE] ?? null, self::DEFAULT_EXAM_MAX_SCORE);

        $created = DB::transaction(function () use ($semester, $caType, $examType, $caMax, $examMax) {
            $created = [];

            $ca = $this->existing($semester, $caType->id);
            if (!$ca) {
                $ca = $created[] = $this->createExam($semester, $caType->id, $caMax);
            }

            if ($examType && !$this->existing($semester, $examType->id)) {
                $created[] = $this->createExam($semester, $examType->id, $examMax + (float) $ca->max_score);
            }

            return $created;
        });

        foreach ($created as $exam) {
            CreateExamCandidateJob::dispatch($exam->id, $this->schoolBranchId);
            CreateExamJointCourseJob::dispatch($exam->id, $this->schoolBranchId);
        }
    }

    private function existing(SchoolSemester $semester, string $examTypeId): ?Exam
    {
        return Exam::where('school_branch_id', $this->schoolBranchId)
            ->where('exam_type_id', $examTypeId)
            ->where('school_year_id', $semester->school_year_id)
            ->first();
    }

    private function createExam(SchoolSemester $semester, string $examTypeId, float $maxScore): Exam
    {
        $exam = new Exam();
        $exam->school_branch_id = $this->schoolBranchId;
        $exam->start_date = $semester->start_date;
        $exam->end_date = $semester->end_date;
        $exam->max_score = $maxScore;
        $exam->exam_type_id = $examTypeId;
        $exam->school_year_id = $semester->school_year_id;
        $exam->save();

        return $exam;
    }

    private function score(mixed $value, float $default): float
    {
        return is_numeric($value) && (float) $value > 0 ? (float) $value : $default;
    }

    private function settings(): array
    {
        return SchoolBranchSetting::where('school_branch_id', $this->schoolBranchId)
            ->with('settingDefination:id,key')
            ->whereHas('settingDefination', fn($query) => $query->whereIn('key', [self::AUTO_CREATE, self::CA_MAX_SCORE, self::EXAM_MAX_SCORE]))
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->settingDefination->key => $setting->value])
            ->all();
    }
}
