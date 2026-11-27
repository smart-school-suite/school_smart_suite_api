<?php

namespace App\Services\Exam;

use App\Jobs\Exam\CreateExamCandidateJob;
use App\Jobs\ExamJointCourse\CreateExamJointCourseJob;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamScore;
use App\Models\ExamJointCourse\ExamJointCourseRef;
use App\Models\ExamTimetable\ExamTimetable;
use App\Models\GradeScale\Grade;
use Illuminate\Support\Str;
use App\Models\SchoolGradesConfig;
use App\Models\GradeScale\SchoolGradeScaleCategory;
use App\Models\AccessedStudent;
use App\Models\Examtype;
use App\Models\Semester;
use App\Models\Student;
use App\Models\AcademicYear\SchoolAcademicYear;
use Carbon\Carbon;
use Exception;
use App\Exceptions\AppException;
use App\Models\ResitExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Grades;
use App\Models\StudentResults;

class ExamService
{
    /**
     * Creates an exam for a school semester: the academic year is the
     * semester's and the exam type one of its (see ExamCreationPolicy).
     * A semester exam's max score adds the CA's to the exam's own part.
     *
     * With "Automatically Create Exams for Matching Semesters" on, the exam
     * is also created for the other specialties whose semester has the same
     * dates (ExamAutoCreationService). The response lists those copies
     * (auto_created_exams) and the specialties skipped, with the reason
     * (auto_creation_skipped).
     */
    public function createExam(array $data, object $currentSchool, object $authAdmin): Exam
    {
        try {
            $allowed = app(ExamCreationPolicy::class)->validated($currentSchool, $data);
            $examType = Examtype::find($allowed['exam_type_id']);
            $examPart = (float) $data["max_score"];

            $calculatedMaxScore = $examPart;
            if ($allowed['ca']) {
                $calculatedMaxScore += (float) $allowed['ca']->max_score;
            }

            // The exam and its automatically created copies commit or roll back together.
            [$exam, $auto] = DB::transaction(function () use ($data, $allowed, $examType, $calculatedMaxScore, $examPart, $currentSchool) {
                $exam = new Exam();
                $exam->school_branch_id = $currentSchool->id;
                $exam->start_date = $data["start_date"];
                $exam->end_date = $data["end_date"];
                $exam->max_score = $calculatedMaxScore;
                $exam->exam_type_id = $examType->id;
                $exam->school_year_id = $allowed['school_year_id'];
                $exam->save();

                $auto = app(ExamAutoCreationService::class)->createFor($exam, $allowed['semester'], $examPart, $currentSchool);

                return [$exam, $auto];
            });

            foreach (collect([$exam])->concat($auto['created']) as $created) {
                CreateExamCandidateJob::dispatch($created->id, $currentSchool->id);
                CreateExamJointCourseJob::dispatch($created->id, $currentSchool->id);
            }

            $exam->setRelation('autoCreatedExams', $auto['created']);
            $exam->setAttribute('auto_creation_skipped', $auto['skipped']);

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                $e->getMessage(),
                500,
                "Unable to Complete Request",
                "An unexpected technical error occurred while trying to create the exam. Please verify your information and try again. If the issue persists, contact support."
            );
        }
    }

    public function deleteExam(string $examId, Object $currentSchool, object $authAdmin)
    {
        try {

            $exam = Exam::where("school_branch_id", $currentSchool->id)
                ->with('examType')
                ->findorFail($examId);

            if ($this->examIdsWithScores($currentSchool, [$exam->id])->isNotEmpty()) {
                $examName = $exam->examType->exam_name ?? 'This exam';
                throw new AppException(
                    "The exam cannot be deleted because scores have already been recorded for it.",
                    409,
                    "Exam Has Recorded Scores",
                    "{$examName} already has student scores recorded. Delete those scores first if you really want to remove this exam."
                );
            }

            $dependentExams = $this->dependentSemesterExams($currentSchool, collect([$exam]));
            if ($dependentExams->isNotEmpty()) {
                $examName = $exam->examType->exam_name ?? 'This CA exam';
                $dependentNames = $dependentExams->map(fn($dependent) => $dependent->examType->exam_name ?? $dependent->id)->implode(', ');
                throw new AppException(
                    "The CA exam cannot be deleted because a semester exam's max score includes it.",
                    409,
                    "CA Exam In Use",
                    "{$examName} is combined into the max score of: {$dependentNames}. Delete that exam first (or delete them together), then try again."
                );
            }

            DB::transaction(function () use ($exam, $examId, $currentSchool) {
                $this->deleteExamCandidate($examId, $currentSchool);
                $exam->delete();
            });
            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The exam you are trying to delete was not found. Please verify the exam ID.",
                404,
                "Exam Not Found",
                "We could not find the exam associated with the provided ID. It may have already been deleted.",
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while deleting the exam. Please try again later.",
                500,
                "Deletion Error",
                "We encountered a server-side issue while attempting to delete the exam.",
                $e->getMessage()
            );
        }
    }
    /**
     * Semester (non-CA) exams whose stored max score includes one of the given CA exams,
     * excluding exams that are themselves being deleted.
     */
    private function dependentSemesterExams(object $currentSchool, $exams, array $excludeIds = [])
    {
        $caExams = $exams->filter(fn($exam) => strtolower($exam->examType->type ?? '') === 'ca');
        if ($caExams->isEmpty()) {
            return collect();
        }

        return Exam::where('school_branch_id', $currentSchool->id)
            ->whereNotIn('id', array_merge($excludeIds, $caExams->pluck('id')->all()))
            ->where(function ($query) use ($caExams) {
                foreach ($caExams as $caExam) {
                    $query->orWhere(function ($inner) use ($caExam) {
                        $inner->where('school_year_id', $caExam->school_year_id)
                            ->whereHas('examType', function ($typeQuery) use ($caExam) {
                                $typeQuery->where('semester', $caExam->examType->semester)
                                    ->where('type', '!=', 'ca');
                            });
                    });
                }
            })
            ->with('examType')
            ->get();
    }
    /**
     * Exams (from the given ids) that already have evaluated candidates,
     * i.e. recorded scores or computed results.
     */
    private function examIdsWithScores(object $currentSchool, array $examIds)
    {
        $scored = ExamScore::where('school_branch_id', $currentSchool->id)
            ->whereIn('exam_id', $examIds)
            ->distinct()
            ->pluck('exam_id');
        $withResults = StudentResults::where('school_branch_id', $currentSchool->id)
            ->whereIn('exam_id', $examIds)
            ->distinct()
            ->pluck('exam_id');

        return $scored->merge($withResults)->unique()->values();
    }

    private function semesterExamsUsingCa(object $currentSchool, Exam $caExam)
    {
        return Exam::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $caExam->school_year_id)
            ->where('id', '!=', $caExam->id)
            ->whereHas('examType', function ($query) use ($caExam) {
                $query->where('semester', $caExam->examType->semester)
                    ->where('type', '!=', 'ca');
            })
            ->with(['examType', 'examGradeScale'])
            ->get();
    }

    private function assertExamNotEvaluated(object $currentSchool, Exam $exam, string $action): void
    {
        if ($this->examIdsWithScores($currentSchool, [$exam->id])->isNotEmpty()) {
            $examName = $exam->examType->exam_name ?? 'This exam';
            throw new AppException(
                "The {$action} cannot be changed because candidates have already been evaluated for this exam.",
                409,
                "Exam Already Evaluated",
                "{$examName} already has recorded scores. The {$action} can only be changed before any candidate is evaluated. You can still update the exam dates."
            );
        }
    }

    /** Detach a grade scale whose max score no longer matches the exam's. */
    private function detachMismatchedGradeScale(Exam $exam): void
    {
        if (!$exam->grades_category_id) {
            return;
        }
        $category = SchoolGradeScaleCategory::find($exam->grades_category_id);
        if (!$category || abs((float) $category->max_score - (float) $exam->max_score) > 0.0001) {
            $exam->grades_category_id = null;
            $exam->save();
        }
    }
    private function deleteExamCandidate(string $examId, object $currentSchool)
    {
        AccessedStudent::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->delete();
    }
    public function bulkDeleteExam(array $examIds, object $currentSchool, object $authAdmin): array
    {
        $deletedExams = [];

        $requestedIds = collect($examIds)->pluck('exam_id')->filter()->unique()->values();
        $examsWithScores = $this->examIdsWithScores($currentSchool, $requestedIds->all());
        if ($examsWithScores->isNotEmpty()) {
            $examNames = Exam::where("school_branch_id", $currentSchool->id)
                ->whereIn('id', $examsWithScores)
                ->with('examType')
                ->get()
                ->map(fn($exam) => $exam->examType->exam_name ?? $exam->id)
                ->implode(', ');
            throw new AppException(
                "Some of the selected exams cannot be deleted because scores have already been recorded for them.",
                409,
                "Exams Have Recorded Scores",
                "These exams already have student scores recorded: {$examNames}. Deselect them, or delete their scores first, then try again. No exams were deleted."
            );
        }

        $requestedExams = Exam::where("school_branch_id", $currentSchool->id)
            ->whereIn('id', $requestedIds)
            ->with('examType')
            ->get();
        $dependentExams = $this->dependentSemesterExams($currentSchool, $requestedExams, $requestedIds->all());
        if ($dependentExams->isNotEmpty()) {
            $dependentNames = $dependentExams->map(fn($dependent) => $dependent->examType->exam_name ?? $dependent->id)->implode(', ');
            throw new AppException(
                "Some of the selected CA exams cannot be deleted because semester exams' max scores include them.",
                409,
                "CA Exams In Use",
                "The selected CA exams are combined into the max score of: {$dependentNames}. Select those exams too, or delete them first, then try again. No exams were deleted."
            );
        }

        try {
            DB::beginTransaction();
            $specialtyIds = [];
            foreach ($examIds as $examIdItem) {
                $examId = $examIdItem['exam_id'] ?? null;
                $exam = Exam::where("school_branch_id", $currentSchool->id)
                    ->findOrFail($examId);
                $specialtyIds[] = $exam->specialty_id;
                $this->deleteExamCandidate($examId, $currentSchool);
                $exam->delete();
                $deletedExams[] = $exam;
            }

            DB::commit();
            return $deletedExams;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "One or more exams you tried to delete were not found. Please verify the IDs and try again.",
                404,
                "Exams Not Found",
                "We could not find one or more exams associated with the provided IDs. They may have already been deleted.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while deleting the exams. Please try again.",
                500,
                "Deletion Error",
                "We encountered an issue while trying to delete the exams due to a server-side problem.",
                null
            );
        }
    }
    public function updateExam(string $examId, object $currentSchool, array $data, ?object $authAdmin, bool $allowNoChanges = false)
    {
        try {
            $exam = Exam::where("school_branch_id", $currentSchool->id)
                ->with(['examType'])
                ->find($examId);

            if (!$exam) {
                throw new AppException(
                    "The exam you are trying to update was not found.",
                    404,
                    "Exam Not Found",
                    "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            // Keep legitimate falsy values such as a max score of 0; only drop empty input.
            $data = array_filter($data, fn($value) => !is_null($value) && $value !== '');

            // Status, date window, timetable, exam type and academic year rules (ExamUpdatePolicy).
            if (!app(ExamUpdatePolicy::class)->validatedChanges($exam, $data, $allowNoChanges)) {
                return $exam->fresh(['examType.semesters', 'schoolYear.specialty.level', 'schoolYear.systemAcademicYear', 'examGradeScale']);
            }

            $newExamTypeId = $data['exam_type_id'] ?? $exam->exam_type_id;
            $newSchoolYearId = $data['school_year_id'] ?? $exam->school_year_id;
            $examTypeChanged = $newExamTypeId !== $exam->exam_type_id;
            $schoolYearChanged = $newSchoolYearId !== $exam->school_year_id;

            $newExamType = $examTypeChanged ? Examtype::find($newExamTypeId) : $exam->examType;
            if (!$newExamType) {
                throw new AppException(
                    "Exam Type Not Found",
                    404,
                    "Exam Type Not Found",
                    "The selected exam type could not be found. Please ensure the exam type exists and has not been deleted before proceeding."
                );
            }

            $startDate = Carbon::parse($data['start_date'] ?? $exam->start_date)->startOfDay();
            $endDate = Carbon::parse($data['end_date'] ?? $exam->end_date)->startOfDay();
            if ($endDate->lt($startDate)) {
                throw new AppException(
                    "The exam end date cannot be before its start date.",
                    422,
                    "Invalid Exam Dates",
                    "The end date ({$endDate->toDateString()}) must be on or after the start date ({$startDate->toDateString()}). Please adjust the dates and try again."
                );
            }

            if ($examTypeChanged || $schoolYearChanged) {
                $this->assertExamTypeOrSchoolYearCanChange($exam, $currentSchool, $newExamTypeId, $newSchoolYearId);
            }

            $isCaExam = strtolower($exam->examType->type) === 'ca';
            $willBeCaExam = strtolower($newExamType->type) === 'ca';

            // Non-CA exams store their max score combined with the related CA's max score
            // (see createExam), so the user-entered value is only the exam's own portion.
            $currentCaMaxScore = $isCaExam
                ? 0
                : (float) ($this->findRelatedCaExam($currentSchool, $exam->school_year_id, $exam->examType)?->max_score ?? 0);
            $examPortion = array_key_exists('max_score', $data)
                ? (float) $data['max_score']
                : (float) $exam->max_score - $currentCaMaxScore;

            if ($willBeCaExam) {
                $newMaxScore = $examPortion;
            } else {
                $relatedCa = $this->findRelatedCaExam($currentSchool, $newSchoolYearId, $newExamType);
                if (!$relatedCa) {
                    throw new AppException(
                        "Missing CA Exam Prerequisite",
                        422,
                        "Continuous Assessment Required",
                        "You must create a Continuous Assessment (CA) exam for semester '{$newExamType->semester}' before assigning this exam type."
                    );
                }
                $newMaxScore = $examPortion + (float) $relatedCa->max_score;
            }

            $oldMaxScore = (float) $exam->max_score;
            $maxScoreChanging = abs($newMaxScore - $oldMaxScore) > 0.0001;

            $dependentExams = collect();
            if ($maxScoreChanging) {
                $this->assertExamNotEvaluated($currentSchool, $exam, 'max score');

                if ($isCaExam) {
                    // The CA's max score is part of its semester exams' totals.
                    $dependentExams = $this->semesterExamsUsingCa($currentSchool, $exam);
                    $evaluatedDependents = $this->examIdsWithScores($currentSchool, $dependentExams->pluck('id')->all());
                    if ($evaluatedDependents->isNotEmpty()) {
                        $names = $dependentExams->whereIn('id', $evaluatedDependents)
                            ->map(fn($dependent) => $dependent->examType->exam_name ?? $dependent->id)
                            ->implode(', ');
                        throw new AppException(
                            "The CA max score cannot be changed because a semester exam that includes it has already been evaluated.",
                            409,
                            "Semester Exam Already Evaluated",
                            "This CA's max score is part of the total for: {$names}, which already has recorded scores. The CA max score can only be changed before those candidates are evaluated."
                        );
                    }
                }
            }

            DB::transaction(function () use ($exam, $data, $newExamTypeId, $newSchoolYearId, $newMaxScore, $oldMaxScore, $isCaExam, $currentSchool, $dependentExams) {
                $exam->fill(collect($data)->only(['start_date', 'end_date'])->toArray());
                $exam->exam_type_id = $newExamTypeId;
                $exam->school_year_id = $newSchoolYearId;
                $exam->max_score = $newMaxScore;
                $exam->save();

                // A CA's max score is folded into its semester exams' max scores, so keep them in sync.
                $delta = $newMaxScore - $oldMaxScore;
                if ($isCaExam && abs($delta) > 0.0001) {
                    $exam->load('examType');
                    Exam::where('school_branch_id', $currentSchool->id)
                        ->where('school_year_id', $exam->school_year_id)
                        ->where('id', '!=', $exam->id)
                        ->whereHas('examType', function ($query) use ($exam) {
                            $query->where('semester', $exam->examType->semester)
                                ->where('type', '!=', 'ca');
                        })
                        ->increment('max_score', $delta);

                    // Their grade scales were matched to the old totals.
                    foreach ($dependentExams as $dependent) {
                        $this->detachMismatchedGradeScale($dependent->fresh());
                    }
                }

                // A grade scale can only be attached when its max score matches the exam's.
                $this->detachMismatchedGradeScale($exam);
            });

            if ($examTypeChanged || $schoolYearChanged) {
                // Candidates and joint-course links depend on the exam type and school year.
                ExamCandidate::where('school_branch_id', $currentSchool->id)
                    ->where('exam_id', $exam->id)
                    ->delete();
                ExamJointCourseRef::where('school_branch_id', $currentSchool->id)
                    ->where('exam_id', $exam->id)
                    ->delete();
                CreateExamCandidateJob::dispatch($exam->id, $currentSchool->id)->afterCommit();
                CreateExamJointCourseJob::dispatch($exam->id, $currentSchool->id)->afterCommit();
            }

            return $exam->fresh([
                'examType.semesters',
                'schoolYear.specialty.level',
                'schoolYear.systemAcademicYear',
                'examGradeScale',
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                "An unexpected error occurred while updating the exam. Please try again later.",
                500,
                "Update Error",
                "We encountered a server-side issue while attempting to update the exam. " . $e->getMessage(),
                null
            );
        }
    }

    private function findRelatedCaExam(object $currentSchool, string $schoolYearId, Examtype $examType): ?Exam
    {
        return Exam::where("school_branch_id", $currentSchool->id)
            ->where("school_year_id", $schoolYearId)
            ->whereHas('examType', function ($query) use ($examType) {
                $query->where('semester', $examType->semester)
                    ->where('type', 'ca');
            })
            ->first();
    }

    private function assertExamTypeOrSchoolYearCanChange(Exam $exam, object $currentSchool, string $newExamTypeId, string $newSchoolYearId): void
    {
        $hasScores = ExamScore::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->exists();
        $hasTimetable = ExamTimetable::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $exam->id)
            ->exists();

        if ($hasScores || $hasTimetable) {
            throw new AppException(
                "The exam type or academic year cannot be changed for this exam.",
                409,
                "Exam Already In Use",
                "Scores or a timetable have already been recorded for this exam. You can still update its dates and max score, but to change the exam type or academic year, delete and recreate the exam."
            );
        }

        if (strtolower($exam->examType->type) === 'ca') {
            $hasDependentExams = Exam::where('school_branch_id', $currentSchool->id)
                ->where('school_year_id', $exam->school_year_id)
                ->where('id', '!=', $exam->id)
                ->whereHas('examType', function ($query) use ($exam) {
                    $query->where('semester', $exam->examType->semester)
                        ->where('type', '!=', 'ca');
                })
                ->exists();

            if ($hasDependentExams) {
                throw new AppException(
                    "This CA exam is linked to a semester exam.",
                    409,
                    "CA Exam In Use",
                    "A semester exam's max score depends on this Continuous Assessment. Update or remove that exam before changing this CA's exam type or academic year."
                );
            }
        }

        $schoolYearExists = SchoolAcademicYear::where("school_branch_id", $currentSchool->id)
            ->where("id", $newSchoolYearId)
            ->exists();
        if (!$schoolYearExists) {
            throw new AppException(
                "Academic Year Not Found",
                404,
                "Academic Year Not Found",
                "The selected academic year or term could not be found within your school branch. Please verify the academic year and try again."
            );
        }

        $duplicate = Exam::where("school_branch_id", $currentSchool->id)
            ->where("exam_type_id", $newExamTypeId)
            ->where("school_year_id", $newSchoolYearId)
            ->where("id", '!=', $exam->id)
            ->exists();
        if ($duplicate) {
            throw new AppException(
                "Duplicate Exam Details",
                409,
                "Exam Already Exists",
                "An exam with this exact type and academic year already exists for this school branch. Please review your selection or update the existing exam."
            );
        }
    }
    public function bulkUpdateExam(array $examUpdateList, object $currentSchool, ?object $authAdmin)
    {
        // Each exam goes through the same rules as a single update (date order, CA-combined
        // max scores, type/year change checks); any failure rolls the whole batch back.
        return DB::transaction(function () use ($examUpdateList, $currentSchool, $authAdmin) {
            $result = [];
            foreach ($examUpdateList as $examUpdate) {
                $examId = $examUpdate['exam_id'];
                unset($examUpdate['exam_id']);
                try {
                    $result[] = $this->updateExam($examId, $currentSchool, $examUpdate, $authAdmin, true);
                } catch (AppException $e) {
                    $name = Exam::with('examType')->find($examId)?->examType?->exam_name ?? 'An exam';
                    throw new AppException($e->getMessage(), $e->getCode(), $e->getTitle(), "{$name}: {$e->getDescription()}");
                }
            }
            return $result;
        });
    }
    /** What can change on the exam, why the rest can't, and the allowed ranges. */
    /** The school semesters an exam can be created for, with their exam types and limits. */
    public function getCreationOptions(object $currentSchool): array
    {
        return app(ExamCreationPolicy::class)->options($currentSchool);
    }

    public function getUpdateRules(object $currentSchool, string $examId): array
    {
        $exam = Exam::where('school_branch_id', $currentSchool->id)->find($examId);
        if (!$exam) {
            throw new AppException(
                "The exam was not found.",
                404,
                "Exam Not Found",
                "We could not find the exam with the provided ID for this school. Please verify the ID and try again."
            );
        }

        return app(ExamUpdatePolicy::class)->describe($exam);
    }
    public function getExams(object $currentSchool)
    {
        $exams = Exam::where('school_branch_id', $currentSchool->id)
            ->with([
                'examType.semesters',
                'schoolYear.specialty.level',
                'examGradeScale',
                'schoolYear.systemAcademicYear'
            ])
            ->get();

        return $exams;
    }
    public function examDetails(object $currentSchool, string $examId): Exam
    {
        try {
            $exam = Exam::where("school_branch_id", $currentSchool->id)
                ->with(['examType.semesters', 'schoolYear.specialty.level', 'schoolYear.systemAcademicYear', 'examGradeScale'])
                ->find($examId);

            if (!$exam) {
                throw new AppException(
                    "The exam you are looking for was not found.",
                    404,
                    "Exam Not Found",
                    "We could not find an exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            $now = Carbon::now();
            $startDate = Carbon::parse($exam->start_date)->startOfDay();
            $endDate = Carbon::parse($exam->end_date)->endOfDay();

            if ($now->lt($startDate)) {
                $status = 'upcoming';
            } elseif ($now->between($startDate, $endDate)) {
                $status = 'active';
            } else {
                $status = 'finished';
            }

            $exam->status = $status;
            // Max score and grade scale can only change before any candidate is evaluated.
            $exam->has_evaluated_candidates = $this->examIdsWithScores($currentSchool, [$exam->id])->isNotEmpty();

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                $e->getMessage(),
                500,
                "Server Error",
                "An unexpected error occurred while fetching the exam details. Please try again later.",
                null
            );
        }
    }
    public function getAssociateWeightedMarkLetterGrades(string $examId, object $currentSchool)
    {
        $results = [];

        $exam = Exam::where("school_branch_id", $currentSchool->id)
            ->with(["examtype"])
            ->find($examId);

        if (!$exam) {
            throw new AppException(
                "The exam you are looking for was not found.",
                404,
                "Exam Not Found",
                "We could not find an exam with the provided ID for this school. Please verify the ID and try again.",
                null
            );
        }

        $letterGrades = Grade::all();

        if ($letterGrades->isEmpty()) {
            throw new AppException(
                "No letter grades have been configured for the system.",
                500,
                "Grades Configuration Missing",
                "The system requires letter grades to be configured before you can associate them with exam marks. Please contact support.",
                null
            );
        }

        foreach ($letterGrades as $letterGrade) {
            $results[] = [
                "letter_grade" => $letterGrade,
                "exam" => $exam,
            ];
        }

        return $results;
    }
    public function addExamGradeScale(string $examId, object $currentSchool, string $gradeScaleCategoryId, object $authAdmin): Exam
    {
        try {
            $gradeScaleCategory = SchoolGradeScaleCategory::where("school_branch_id", $currentSchool->id)
                ->with(['schoolGradeScale', 'systemGradeCategory'])
                ->find($gradeScaleCategoryId);

            if (!$gradeScaleCategory) {
                throw new AppException(
                    "The selected grading configuration was not found.",
                    404,
                    "Grading Configuration Not Found",
                    "We could not find the specified grading configuration for this school. Please verify the ID and try again.",
                    null
                );
            }

            if ($gradeScaleCategory->schoolGradeScale->isEmpty()) {
                throw new AppException(
                    "The selected grading configuration has not been set up completely.",
                    400,
                    "Incomplete Configuration",
                    "You cannot apply an incomplete grading configuration to an exam. Please complete the setup and try again.",
                    null
                );
            }

            $exam = Exam::where("school_branch_id", $currentSchool->id)
                ->with(['examType'])
                ->find($examId);

            if (!$exam) {
                throw new AppException(
                    "The exam you are trying to grade was not found.",
                    404,
                    "Exam Not Found",
                    "We could not find the exam with the provided ID for this school. Please verify the ID and try again.",
                    null
                );
            }

            $this->assertExamNotEvaluated($currentSchool, $exam, 'grade scale');

            if (strtolower($exam->examType->type) !== strtolower($gradeScaleCategory->systemGradeCategory->exam_type)) {
                throw new AppException(
                    "Mismatched Exam Type and Grading Category",
                    422,
                    "Incompatible Grading Category",
                    "The selected grading configuration type does not match the type of this exam ({$exam->examType->type}).",
                    null
                );
            }

            if ((float) $exam->max_score !== (float) $gradeScaleCategory->max_score) {
                throw new AppException(
                    "Mismatched Maximum Score",
                    422,
                    "Score Limit Mismatch",
                    "The maximum score of the exam ({$exam->max_score}) does not match the maximum score configured for this grading scale ({$gradeScaleCategory->max_score}).",
                    null
                );
            }

            $exam->grades_category_id = $gradeScaleCategory->id;
            $exam->save();

            return $exam;
        } catch (AppException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AppException(
                $e->getMessage(),
                500,
                "Server Error",
                "An unexpected error occurred while attaching the grading configuration to the exam. Please try again later.",
                null
            );
        }
    }
    /**
     * Attaches a grade scale to several exams, each under the same checks as
     * a single attach (addExamGradeScale): a complete configuration of the
     * exam's type and max score, before any candidate is evaluated. All or
     * nothing. grades_config_Id is the school's grade scale category id
     * (school_grade_scale_categories.id), which is what exams reference.
     */
    public function bulkAddExamGrading(array $examGradingList, object $currentSchool, object $authAdmin)
    {
        return DB::transaction(function () use ($examGradingList, $currentSchool, $authAdmin) {
            $result = [];
            foreach ($examGradingList as $examGrading) {
                $gradeScaleCategoryId = $examGrading['grades_config_Id'] ?? null;
                $examId = $examGrading['exam_id'] ?? null;

                $exam = $this->addExamGradeScale($examId, $currentSchool, (string) $gradeScaleCategoryId, $authAdmin);
                $result[] = [
                    'grades_config' => SchoolGradeScaleCategory::find($gradeScaleCategoryId),
                    'exam' => $exam,
                ];
            }
            return $result;
        });
    }
    public function getExamsByStudentIdSemesterId(object $currentSchool, string $studentId, string $semesterId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->findOrFail($studentId);
        $examQueryConstraints = [
            ['school_branch_id', $currentSchool->id],
            ['specialty_id', $student->specialty_id],
            ['level_id', $student->level_id],
            ['semester_id', $semesterId],
        ];

        $relationshipsToLoad = ['semester', 'examtype', 'specialty', 'level'];

        $exams = Exam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $resitExams = ResitExam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $allExams = $exams->merge($resitExams);

        $examItems = $allExams->map(function ($exam) {
            $item = [
                "exam_name"         => $exam->examtype->exam_name ?? null,
                "exam_id"           => $exam->id,
                "level_id"          => $exam->level_id,
                "level_name"        => $exam->level?->name ?? null,
                "specialty_id"      => $exam->specialty_id,
                "specialty_name"    => $exam->specialty?->specialty_name ?? null,
                "description"       => $exam->examtype->description ?? null,
                "exam_type_id"      => $exam->exam_type_id,
                "student_batch_id"  => $exam->student_batch_id,
                "timetable_created" => (bool) $exam->timetable_published,
                "result_released"   => (bool) $exam->result_released,
                "start_date"        => $exam->start_date?->toDateString(),
                "end_date"          => $exam->end_date?->toDateString(),
                "result_message"    => null,
            ];

            if ($exam->result_released == true) {
                $item["result_message"] = [
                    "id"    => (string) Str::uuid(),
                    "title" => ($exam->examtype->exam_name ?? 'Exam') . " Results Are Out!",
                    "body"  => "Well done on completing the exams! Your results are ready.",
                ];
            }

            return $item;
        });

        $semester = Semester::find($semesterId);

        $result = [
            "semester"   => $semester?->name ?? "Semester",
            "semesterId" => $semesterId,
            "exams"      => $examItems->values()->all()
        ];

        return $result;
    }
    public function getExamGradeScale(string $examId,  object $currentSchool)
    {
        $exam = Exam::where('school_branch_id', $currentSchool->id)
            ->with([
                'examtype',
                'semester',
                'level',
                'specialty'
            ])
            ->findOrFail($examId);

        $grades = Grades::where('school_branch_id', $currentSchool->id)
            ->where('grades_category_id', $exam->grades_category_id)
            ->with('lettergrade')
            ->orderBy('minimum_score', 'desc')
            ->get();

        $gradeScale = $grades->map(function ($grade) {
            return [
                "id"             => $grade->id,
                "minimum_score"  => (float) $grade->minimum_score,
                "maximum_score"  => (float) $grade->maximum_score,
                "grade_status"   => $grade->grade_status ?? "N/A",
                "grade"          => $grade->lettergrade?->letter_grade ?? "N/A",
                "determinant"    => $grade->determinant ?? "N/A",
                "grade_points"  => (float) $grade->grade_points,
            ];
        })->values();

        $result = [
            "exam" => [
                "exam_id"         => $exam->id,
                "exam_name"       => $exam->examtype?->exam_name ?? "Unnamed Exam",
                "exam_type"       => $exam->examtype?->type ?? "Unnamed Type",
                "semester"        => $exam->semester?->name ?? "Unknown Semester",
                "semester_id"     => $exam->semester_id,
                "level_name"      => $exam->level?->name ?? "N/A",
                "specialty_name"  => $exam->specialty?->specialty_name ?? "N/A",
            ],
            "grade_scale" => $gradeScale
        ];

        return $result;
    }
    public function getUpcomingExams(object $currentSchool, object $student)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($student->id);

        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Not Found",
                "Student Not Found. Please check to ensure that the student has not been accidentally deleted or dropped."
            );
        }

        $now = Carbon::now();


        $regularExams = Exam::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('end_date', '>=', $now)
            ->with(['examtype', 'semester'])
            ->select('id', 'exam_type_id', 'start_date', 'end_date', 'timetable_published', 'semester_id')
            ->get();

        $resitExams = ResitExam::where('school_branch_id', $currentSchool->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('end_date', '>=', $now)
            ->with(['examtype', 'semester'])
            ->select('id', 'exam_type_id', 'start_date', 'end_date', 'timetable_published', 'semester_id')
            ->get();

        $allUpcoming = collect();

        $regularExams->each(function ($exam) use ($allUpcoming) {
            $allUpcoming->push([
                "exam_id"            => $exam->id,
                "exam_name"          => $exam->examtype?->exam_name ?? "Upcoming Exam",
                'description'        => $exam->examtype?->description ?? null,
                "semester"           => $exam->semester?->name ?? "Unknown Semester",
                "start_date"         => $exam->start_date?->toDateString(),
                "end_date"           => $exam->end_date?->toDateString(),
                "timetable_published" => (bool) $exam->timetable_published,
            ]);
        });

        $resitExams->each(function ($exam) use ($allUpcoming) {
            $allUpcoming->push([
                "exam_id"            => $exam->id,
                "exam_name"          => $exam->examtype?->exam_name ?? "Resit Exam",
                'description'        => $exam->examtype?->description ?? null,
                "semester"           => $exam->semester?->name ?? "Unknown Semester",
                "start_date"         => $exam->start_date?->toDateString(),
                "end_date"           => $exam->end_date?->toDateString(),
                "timetable_published" => (bool) $exam->timetable_published,
            ]);
        });

        $sorted = $allUpcoming->sortBy('start_date')->values();
        return $sorted;
    }
    public function getAllExamsByStudentId(object $currentSchool, string $studentId)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->findOrFail($studentId);

        $examQueryConstraints = [
            ['school_branch_id', $currentSchool->id],
            ['specialty_id', $student->specialty_id],
            ['level_id', $student->level_id],
        ];

        $relationshipsToLoad = ['semester', 'examtype', 'specialty', 'level'];

        $exams = Exam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $resitExams = ResitExam::where($examQueryConstraints)
            ->with($relationshipsToLoad)
            ->get();

        $allExams = $exams->merge($resitExams);

        $grouped = $allExams->groupBy('semester_id');

        $result = $grouped->map(function ($examsInSemester) {
            $firstExam = $examsInSemester->first();
            $semester = $firstExam->semester;

            $semesterExams = $examsInSemester->map(function ($exam) {
                $item = [
                    "exam_name"         => $exam->examtype->exam_name ?? null,
                    "exam_id"           => $exam->id,
                    "level_id"          => $exam->level_id,
                    "level_name"        => $exam->level?->name ?? null,
                    "specialty_id"      => $exam->specialty_id,
                    "specialty_name"    => $exam->specialty?->specialty_name ?? null,
                    "description"       => $exam->examtype->description ?? null,
                    "exam_type_id"      => $exam->exam_type_id,
                    "student_batch_id"  => $exam->student_batch_id,
                    "timetable_created" => (bool) $exam->timetable_published,
                    "result_released"   => (bool) $exam->result_released,
                    "start_date"        => $exam->start_date?->toDateString(),
                    "end_date"          => $exam->end_date?->toDateString(),
                    "result_message"    => null,
                ];

                if ($exam->result_released == true) {
                    $item["result_message"] = [
                        "id"    => (string) Str::uuid(),
                        "title" => ($exam->examtype->exam_name ?? 'Exam') . " Results Are Out",
                        "body"  => "Well done on completing the exams! Your results are ready.",
                    ];
                }

                return $item;
            });


            return [
                "semester"   => $semester?->name ?? 'Unknown Semester',
                "semesterId" => $semester?->id ?? $firstExam->semester_id,
                "exams"      => $semesterExams->values()->all()
            ];
        })->values()->all();

        return $result;
    }
    public function getRelatedCaExam(object $currentSchool, string $examTypeId, string $schoolYearId): ?Exam
    {
        $schoolYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with('specialty')
            ->find($schoolYearId);

        if (!$schoolYear) {
            throw new AppException(
                'Academic Year Not Found',
                404,
                'Academic Year Not Found',
                'The requested school academic year could not be found for this branch. Please verify the selected year.'
            );
        }

        $examType = Examtype::find($examTypeId);

        if (!$examType) {
            throw new AppException(
                'Exam Type Not Found',
                404,
                'Exam Type Not Found',
                'The specified exam type does not exist. Please check your selection and try again.'
            );
        }

        $caExam = Exam::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $schoolYear->id)
            ->whereHas('examType', function ($query) use ($examType) {
                $query->where('semester', $examType->semester)
                    ->where('type', 'ca');
            })
            ->with('examType')
            ->first();

        if (!$caExam) {
            throw new AppException(
                'CA Exam Not Found',
                404,
                'CA Exam Not Found',
                'No Continuous Assessment (CA) exam was found for the specified semester and academic year.'
            );
        }

        return $caExam;
    }
}
