<?php

namespace App\Services\Resit;

use App\Models\Grades;
use App\Models\ResitMarks;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Services\ApiResponseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Throwable;
use App\Models\Studentresit;
use App\Exceptions\AppException;

class ResitService
{
    public function updateStudentResit(array $data, object $currentSchool, string $studentResitId, object $authAdmin)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentResitExists->update($filteredData);
        return $studentResitExists;
    }
    public function deleteStudentResit(string $studentResitId, object $currentSchool, object $authAdmin)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $studentResitExists->delete();
        return $studentResitExists;
    }
    public function getResitableCourses(object $currentSchool)
    {
        try {
            $resitableCourses = Studentresit::where('school_branch_id', $currentSchool->id)
                ->whereNull('deleted_at')
                ->with([
                    'exam.schoolYear.specialty.level',
                    'courses',
                    'student',
                    'exam.examType.semesters',
                    'exam.schoolYear.systemAcademicYear'
                ])
                ->get();

            return $resitableCourses;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving the list of resitable courses.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the resitable courses from being retrieved successfully.",
                null
            );
        }
    }
    /**
     * A student's resit for the details drawer: the student, the course and
     * the exam they are resitting it from, the fee with what has been paid,
     * and the latest resit score.
     */
    public function getStudentResitDetails($currentSchool, $studentResitId)
    {
        // The resit has no level or specialty of its own; both come from the exam's academic year.
        $resit = Studentresit::where('school_branch_id', $currentSchool->id)
            ->with([
                'courses',
                'student',
                'exam.examType.semesters',
                'exam.schoolYear.specialty.level',
                'exam.schoolYear.systemAcademicYear',
                'studentResitTransactions',
                'resitMark.grade.grade',
            ])
            ->find($studentResitId);

        if (!$resit) {
            throw new AppException(
                "Student resit not found.",
                404,
                "Resit Not Found",
                "The requested resit could not be found or does not belong to the current school branch.",
                null
            );
        }

        $specialty = $resit->exam?->schoolYear?->specialty;
        $transactions = $resit->studentResitTransactions;
        $paid = round((float) $transactions->sum('amount'), 2);
        $fee = (float) $resit->fee;
        $mark = $resit->resitMark->sortByDesc('created_at')->first();

        return [
            'id' => $resit->id,
            'student_name' => $resit->student?->name,
            'student_username' => $resit->student?->username,
            'course_title' => $resit->courses?->course_title,
            'course_code' => $resit->courses?->course_code,
            'course_credit' => $resit->courses ? (float) $resit->courses->credit : null,
            'exam_name' => $resit->exam?->examType?->exam_name,
            'semester' => $resit->exam?->examType?->semesters?->name,
            'academic_year' => $resit->exam?->schoolYear?->systemAcademicYear?->name,
            'specialty_name' => $specialty?->specialty_name,
            'level_name' => $specialty?->level?->name,
            'level_number' => $specialty?->level?->level,
            'resit_fee' => $fee,
            'amount_paid' => $paid,
            'balance' => max(round($fee - $paid, 2), 0),
            'payment_status' => $resit->payment_status,
            'transactions' => $transactions->count(),
            'last_paid_at' => $transactions->max('created_at'),
            'attempts' => (int) $resit->attempts,
            'is_carry_over' => (bool) $resit->iscarry_over,
            'resit_score' => $mark ? (float) $mark->score : null,
            'resit_grade' => $mark?->grade?->grade?->letter_grade,
            'resit_grade_status' => $mark?->grade?->grade_status,
            'created_at' => $resit->created_at,
            'updated_at' => $resit->updated_at,
        ];
    }
    public function getMyResits($currentSchool, $studentId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->where("student_id", $studentId)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->get();
        return $getResitData;
    }
    public function getResitEvaluationHelperData($currentSchool, $resitExamId, $candidateId)
    {
        try {
            $resitExam = ResitExam::find($resitExamId);
            if (!$resitExam) {
                throw new AppException(
                    "Resit Exam ID '{$resitExamId}' not found.",
                    404,
                    "Exam Not Found 🔎",
                    "The specified resit exam record could not be found.",
                    null
                );
            }

            $resitCandidate = ResitCandidates::with('student')->find($candidateId);
            if (!$resitCandidate) {
                throw new AppException(
                    "Resit Candidate ID '{$candidateId}' not found.",
                    404,
                    "Candidate Not Found 👤",
                    "The specified resit candidate record could not be found.",
                    null
                );
            }

            $resitCoursesIds = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
                ->where("resit_exam_id", $resitExamId)
                ->pluck('course_id');

            if ($resitCoursesIds->isEmpty()) {
                throw new AppException(
                    "The resit exam timetable has not been created.",
                    404,
                    "Timetable Missing 📅",
                    "The resit exam schedule is empty. Please create the resit timetable first.",
                    null
                );
            }

            if (is_null($resitExam->grades_category_id)) {
                throw new AppException(
                    "The grading scale category has not been linked to this resit exam.",
                    400,
                    "Grading Scale Category Missing 🏷️",
                    "Please add the grading scale category ID to the resit exam configuration.",
                    null
                );
            }

            $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)
                ->whereIn("course_id", $resitCoursesIds)
                ->where("student_id", $resitCandidate->student_id)
                ->with(['courses', 'level', 'specialty', 'student'])
                ->get();

            if ($studentResits->isEmpty()) {
                throw new AppException(
                    "No eligible resit records found for student ID '{$resitCandidate->student_id}' in the current exam scope.",
                    404,
                    "Student Resits Missing 📄",
                    "The student has no courses eligible for resit evaluation within the scope of this exam.",
                    null
                );
            }

            $examGrading = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $resitExam->grades_category_id)
                ->with('lettergrade')
                ->get();

            if ($examGrading->isEmpty()) {
                throw new AppException(
                    "The grade scale has not been configured.",
                    404,
                    "Grading Configuration Missing ⚙️",
                    "The grading scale associated with the exam category is empty. Please configure the grade scale.",
                    null
                );
            }

            return [
                'course_data' => $studentResits->pluck('courses')->unique('id')->values(),
                'student_data' => $resitCandidate->student,
                'exam_grading' => $examGrading
            ];
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "An unexpected system error occurred while fetching resit evaluation data. Error: " . $e->getMessage(),
                500,
                "Data Fetching Failed 🛑",
                "An unknown system error prevented the retrieval of the resit evaluation data. Please contact support.",
                null
            );
        }
    }
    public function getResitScoresByCandidate($currentSchool, $candidateId)
    {
        try {
            $resitCandidate = ResitCandidates::with('student')->find($candidateId);
            $resitExam = ResitExam::find($resitCandidate->resit_exam_id);

            $examGrading = Grades::where("school_branch_id", $currentSchool->id)
                ->where("grades_category_id", $resitExam->grades_category_id)
                ->with('lettergrade')
                ->get();

            $courses = ResitMarks::where("school_branch_id", $currentSchool->id)
                ->where("resit_exam_id", $resitExam->id)
                ->where("student_id", $resitCandidate->student_id)
                //   ->where("level_id", $resitCandidate->level_id)
                // ->where("specialty_id", $resitCandidate->specialty_id)
                ->with(['course'])
                ->get();

            return [
                'marks_data' => $courses,
                'student_data' => $resitCandidate->student,
                'exam_grading' => $examGrading,
            ];
        } catch (Exception $e) {
        }
    }
    public function bulkDeleteStudentResit($studentResitIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentResitIds as $studentResitId) {
                $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)->findOrFail($studentResitId['resit_id']);
                $studentResit->delete();
                $result[] = $studentResit;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateStudentResit($updateStudentResitList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateStudentResitList as $updateStudentResit) {
                $studentResit = StudentResit::where("school_branch_id", $currentSchool->id)->findOrFail($updateStudentResit->id);
                if ($studentResit) {
                    $cleanedData = array_filter($updateStudentResit, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $studentResit->update($cleanedData);
                    }
                }
                $result[] = [
                    $studentResit
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getAllEligableStudents($currentSchool, $resitExamId)
    {
        $timetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->where("resit_exam_id", $resitExamId)
            ->pluck('course_id')
            ->toArray();

        if (empty($timetableEntries)) {
            return [];
        }

        $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)
            ->whereIn("course_id", $timetableEntries)
            ->with(['student', 'course'])
            ->get();

        $result = $studentResits->unique('student_id')->values()->all();

        return $result;
    }
    public function getEligableResitExamByStudent($currentSchool, $studentId)
    {
        $eligibleResitExams = collect();

        $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $studentId)
            ->pluck('course_id')
            ->toArray();

        if (empty($studentResits)) {
            return $eligibleResitExams;
        }

        $resitTimetableEntries = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->whereIn("course_id", $studentResits)
            ->with(['resitExam' => function ($query) {
                $query->whereIn("status", ["active", "pending"]);
            }])
            ->get();

        foreach ($resitTimetableEntries as $timetableEntry) {
            if ($timetableEntry->resitExam) {
                $eligibleResitExams->push($timetableEntry->resitExam);
            }
        }

        return $eligibleResitExams->unique('id')->values();
    }
    public function getResitStudentId($currentSchool, $studentId)
    {
        $studentResits = StudentResit::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $studentId)
            ->with([
                'courses',
                'level',
                'specialty',
                'exam.examtype',
                'exam.semester'
            ])
            ->get();

        if ($studentResits->isEmpty()) {
            return [];
        }

        $carryOverResits = $studentResits->where('iscarry_over', true);
        $regularResits   = $studentResits->where('iscarry_over', '!=', true);

        $result = collect();

        if ($carryOverResits->isNotEmpty()) {
            $result->push([
                "type"    => "Carry Over",
                "typeId"  => Str::uuid(),
                "resits"  => $carryOverResits->map(function ($resit) {
                    return [
                        "id"              => $resit->id,
                        "course_title"    => $resit->courses?->course_title ?? "N/A",
                        "course_code"   => $resit->courses?->course_code ?? "N/A",
                        "amount"          => (int) $resit->resit_fee,
                        "course_credit"   => (int) $resit->courses?->credit ?? 0,
                        "specialty_name"  => $resit->specialty?->specialty_name ?? "N/A",
                        "level_name"      => $resit->level?->name ?? "N/A",
                        "attempts"        => (int) $resit->attempt_number,
                        "exam_name"       => $resit->exam?->examtype?->exam_name ?? "Resit Exam",
                        "payment_status"  => $resit->paid_status
                    ];
                })->values()
            ]);
        }

        $groupedBySemester = $regularResits->groupBy(function ($resit) {
            return $resit->exam?->semester?->name ?? 'Unknown Semester';
        });

        foreach ($groupedBySemester as $semesterName => $resits) {
            $result->push([
                "type"   => $semesterName . " Resit",
                "typeId" => Str::uuid(),
                "resits" => $resits->map(function ($resit) {
                    return [
                        "id"              => $resit->id,
                        "course_title"    => $resit->courses?->course_title ?? "N/A",
                        "course_code"   => $resit->courses?->course_code ?? "N/A",
                        "amount"          => (int) $resit->resit_fee,
                        "course_credit"   => (int) $resit->courses?->credit ?? 0,
                        "specialty_name"  => $resit->specialty?->specialty_name ?? "N/A",
                        "level_name"      => $resit->level?->name ?? "N/A",
                        "attempts"        => (int) $resit->attempt_number,
                        "exam_name"       => $resit->exam?->examtype?->exam_name ?? "Resit Exam",
                        "payment_status"  => $resit->paid_status
                    ];
                })->values()
            ]);
        }

        return $result->values()->all();
    }
    public function getResitStudentIdSemesterId($currentSchool, $studentId, $semesterId)
    {
        $studentResits = StudentResit::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $studentId)
            ->whereHas('exam', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->with([
                'courses',
                'level',
                'specialty',
                'exam.examtype',
                'exam.semester'
            ])
            ->get();

        if ($studentResits->isEmpty()) {
            return [];
        }

        $semesterName = $studentResits->first()->exam?->semester?->name ?? "Semester";

        return [
            "type"   => $semesterName . " Resit",
            "typeId" => Str::uuid(),
            "resits" => $studentResits->map(function ($resit) {
                return [
                    "id"              => $resit->id,
                    "course_title"    => $resit->courses?->course_title ?? "Unknown Course",
                    "amount"          => (int) $resit->resit_fee,
                    "course_code"   => $resit->courses?->course_code ?? "N/A",
                    "course_credit"   => (int) $resit->courses?->credit ?? 0,
                    "specialty_name"  => $resit->specialty?->specialty_name ?? "N/A",
                    "level_name"      => $resit->level?->name ?? "N/A",
                    "attempts"        => (int) $resit->attempt_number,
                    "exam_name"       => $resit->exam?->examtype?->exam_name ?? "Resit Exam",
                    "payment_status"  => $resit->paid_status
                ];
            })->values()->all()
        ];
    }
    public function getResitStudentIdCarryOver($currentSchool, $studentId)
    {
        $carryOverResits = StudentResit::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $studentId)
            ->where('iscarry_over', true)
            ->with([
                'courses',
                'level',
                'specialty',
                'exam.examtype',
                'exam.semester'
            ])
            ->get();


        $grouped = $carryOverResits->groupBy(function ($resit) {
            return $resit->exam?->semester?->name ?? 'Unknown Semester';
        });

        $result = $grouped->map(function ($resits, $semesterName) {
            return [
                "type"   => $semesterName . " Carry Over",
                "typeId" => Str::uuid(),
                "resits" => $resits->map(function ($resit) {
                    return [
                        "id"              => $resit->id,
                        "course_title"    => $resit->courses?->course_title ?? "N/A",
                        "amount"          => (int) $resit->resit_fee,
                        "course_code"   => $resit->courses?->course_code ?? "N/A",
                        "course_credit"   => (int) $resit->courses?->credit ?? 0,
                        "specialty_name"  => $resit->specialty?->specialty_name ?? "N/A",
                        "level_name"      => $resit->level?->name ?? "N/A",
                        "attempts"        => (int) $resit->attempt_number,
                        "exam_name"       => $resit->exam?->examtype?->exam_name ?? "Carry Over Exam",
                        "payment_status"  => $resit->paid_status
                    ];
                })->values()->all()
            ];
        })->values();

        return $result;
    }
}
