<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\FinancialJobs\ResitFeeStatJob;
use App\Models\Grades;
use App\Models\ResitMarks;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitFeeTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResitPayment;
use Exception;
use Throwable;
use App\Models\Studentresit;
use App\Exceptions\AppException;

class StudentResitService
{
    // Implement your logic here
    public function updateStudentResit(array $data, $currentSchool, $studentResitId)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $filteredData = array_filter($data);
        $studentResitExists->update($filteredData);
        return $studentResitExists;
    }
    public function deleteStudentResit($studentResitId, $currentSchool)
    {
        $studentResitExists = Studentresit::where("school_branch_id", $currentSchool->id)->find($studentResitId);
        if (!$studentResitExists) {
            return ApiResponseService::error("Student Resit Not found", null, 404);
        }
        $studentResitExists->delete();
        return $studentResitExists;
    }

    public function getResitableCourses($currentSchool)
    {
        try {
            $resitableCourses = Studentresit::where('school_branch_id', $currentSchool->id)
                ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
                ->get();

            if ($resitableCourses->isEmpty()) {
                throw new AppException(
                    "No resitable courses were found for this school branch.",
                    404,
                    "No Resitable Courses Found",
                    "The system could not find any courses available for resitting. This may be because no students have failed a relevant exam yet, which automatically creates a resit record.",
                    null
                );
            }

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
    public function getStudentResitDetails($currentSchool, $studentResitId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->find($studentResitId);
        return $getResitData;
    }
    public function getMyResits($currentSchool, $studentId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->where("student_id", $studentId)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->get();
        return $getResitData;
    }
    public function payResit($studentResitData, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
                ->with(['courses', 'student'])
                ->find($studentResitData['student_resit_id']);

            if (!$studentResit) {
                return ApiResponseService::error("Student Resit Not found", null, 404);
            }

            if ($studentResit->resit_fee < $studentResitData['amount']) {
                return ApiResponseService::error("The Amount paid is greater than the cost of resit", null, 409);
            }
            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

            $transactionId = Str::uuid();
            ResitFeeTransactions::create([
                'id' => $transactionId,
                'amount' => $studentResitData['amount'],
                'payment_method' => $studentResitData['payment_method'],
                'resitfee_id' => $studentResitData['student_resit_id'],
                'school_branch_id' => $currentSchool->id,
                'transaction_id' => $transactionId
            ]);

            $studentResit->paid_status = "Paid";
            $studentResit->save();
            DB::commit();
            ResitFeeStatJob::dispatch($transactionId, $currentSchool->id);
            $paymentDetails = [
                'amount' => $studentResit->resit_fee,
                'transactionRef' => $transactionId,
                'courseName' => $studentResit->courses->course_title
            ];
            $studentResit->student->notify(new ResitPayment($paymentDetails));
            return $studentResit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getResitPaymentTransactions($currentSchool)
    {
        try {
            $getResitPaymentTransactions = ResitFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level', 'studentResit.courses'])
                ->get();

            if ($getResitPaymentTransactions->isEmpty()) {
                throw new AppException(
                    "No resit payment transactions were found for this school branch.",
                    404,
                    "No Transactions Found",
                    "There are currently no resit fee transaction records available in the system for your school branch.",
                    null
                );
            }

            return $getResitPaymentTransactions;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving resit payment transactions.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of transactions from being retrieved successfully.",
                null
            );
        }
    }
    public function deleteResitFeeTransaction($currentSchool, string $transactionId)
    {
        $resitTransaction = ResitFeeTransactions::where("school_branch_id", $currentSchool->id)->find($transactionId);
        if (!$resitTransaction) {
            return ApiResponseService::error("Resit Transaction Not Found", null, 200);
        }
        $resitTransaction->delete();
        return $resitTransaction;
    }
    public function getTransactionDetails($currentSchool, string $transactionId)
    {
        return ResitFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level', 'studentResit.courses'])
            ->find($transactionId);
    }
    public function reverseResitTransaction($transactionId, $currentSchool)
    {
        DB::beginTransaction();
        try {

            $transaction = ResitFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->find($transactionId);

            if (!$transaction) {
                return ApiResponseService::error("Transaction Not found", null, 404);
            }

            $studentResit = Studentresit::where('school_branch_id', $currentSchool->id)
                ->find($transaction->resitfee_id);

            if (!$studentResit) {
                return ApiResponseService::error("Student Resit Not found", null, 404);
            }

            if ($studentResit->paid_status !== "Paid") {
                return ApiResponseService::error("The resit fee is not currently marked as paid", null, 409);
            }

            $transaction->delete();

            $studentResit->paid_status = "unpaid";
            $studentResit->save();

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getResitEvaluationHelperData($currentSchool, $resitExamId, $candidateId)
{
    try {
        // --- 1. Find Resit Exam ---
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

        // --- 2. Find Resit Candidate ---
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
                "No courses found in the resit exam timetable for Resit Exam ID '{$resitExamId}'.",
                404,
                "No Courses Scheduled 📅",
                "The resit exam schedule does not contain any courses to evaluate.",
                null
            );
        }

        // --- 4. Get Student Resit Records ---
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
    public function bulkDeleteStudentResit($studentResitIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentResitIds as $studentResitId) {
                $studentResit = Studentresit::findOrFail($studentResitId['resit_id']);
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
    public function bulkUpdateStudentResit($updateStudentResitList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateStudentResitList as $updateStudentResit) {
                $studentResit = StudentResit::findOrFail($updateStudentResit->id);
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
    public function bulkPayStudentResit($studentResitIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentResitIds as $studentResitId) {
                $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
                    ->find($studentResitId['resit_id']);

                if (!$studentResit) {
                    return ApiResponseService::error("Student Resit Not found", null, 404);
                }
                $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

                ResitFeeTransactions::create([
                    'amount' => $studentResitId['amount'],
                    'payment_method' => $studentResitId['payment_method'],
                    'resitfee_id' => $studentResitId['resit_id'],
                    'school_branch_id' => $currentSchool->id,
                    'transaction_id' => $transactionId
                ]);

                $studentResit->paid_status = "Paid";
                $studentResit->save();
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
    public function bulkDeleteTransaction($transactionIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = ResitFeeTransactions::findOrFail($transactionId['transaction_id']);
                $transaction->delete();
                $result[] = $transaction;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReverseResitTransaction($transactionIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = ResitFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->find($transactionId['transaction_id']);

                if (!$transaction) {
                    return ApiResponseService::error("Transaction Not found", null, 404);
                }

                $studentResit = Studentresit::where('school_branch_id', $currentSchool->id)
                    ->find($transaction->resitfee_id);

                if (!$studentResit) {
                    return ApiResponseService::error("Student Resit Not found", null, 404);
                }

                if ($studentResit->paid_status !== "Paid") {
                    return ApiResponseService::error("The resit fee is not currently marked as paid", null, 409);
                }

                $transaction->delete();

                $studentResit->paid_status = "unpaid";
                $studentResit->save();
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
}
