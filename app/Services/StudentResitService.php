<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\FinancialJobs\ResitFeeStatJob;
use App\Models\Grades;
use App\Models\Marks;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitFeeTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResitPayment;
use Exception;
use App\Models\Studentresit;

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
    public function getStudentResits($currentSchool)
    {
        $getStudentResits = Studentresit::where('school_branch_id', $currentSchool->id)
            ->with(['courses', 'level', 'specialty', 'student', 'exam.examtype'])
            ->paginate(100);
        return $getStudentResits;
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
        $getResitPaymentTransactions = ResitFeeTransactions::where("school_branch_id", $currentSchool->id)->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level'])->get();
        return $getResitPaymentTransactions;
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
            ->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level'])
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
        $resitExam = ResitExam::find($resitExamId);
        $resitCandidate = ResitCandidates::with('student')->find($candidateId);
        $resitCoursesIds = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->where("resit_exam_id", $resitExamId)
            ->pluck('course_id');
        $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)
                ->whereIn("course_id", $resitCoursesIds)
                ->where("student_id", $resitCandidate->student_id)
                ->with(['courses', 'level', 'specialty', 'student'])
                ->get();
        $examGrading = Grades::where("school_branch_id", $currentSchool->id)
                               ->where("grades_category_id", $resitExam->grades_category_id)
                                ->with('lettergrade')
                                ->get();
        return [
             'course_data' => $studentResits->pluck('courses'),
             'student_data' => $resitCandidate->student,
             'exam_grading' => $examGrading
        ];
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
    public function bulkPayStudentResit($paymentData, $studentResitIds, $currentSchool)
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

                if ($studentResit->resit_fee < $paymentData['amount']) {
                    return ApiResponseService::error("The Amount paid is greater than the cost of resit", null, 409);
                }
                $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

                ResitFeeTransactions::create([
                    'amount' => $paymentData['amount'],
                    'payment_method' => $paymentData['payment_method'],
                    'resitfee_id' => $studentResitId,
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
