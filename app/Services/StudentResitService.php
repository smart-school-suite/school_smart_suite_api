<?php

namespace App\Services;

use App\Jobs\CreateResitCandidates;
use App\Models\Marks;
use App\Models\ResitCandidates;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitFeeTransactions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
    public function getMyResits($currentSchool, $examId, $studentId)
    {
        $getResitData = Studentresit::where('school_branch_id', $currentSchool->id)
            ->where("exam_id", $examId)
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
                ->find($studentResitData['student_resit_id']);

            if (!$studentResit) {
                return ApiResponseService::error("Student Resit Not found", null, 404);
            }

            if ($studentResit->resit_fee < $studentResitData['amount']) {
                return ApiResponseService::error("The Amount paid is greater than the cost of resit", null, 409);
            }
            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

            ResitFeeTransactions::create([
                'amount' => $studentResitData['amount'],
                'payment_method' => $studentResitData['payment_method'],
                'resitfee_id' => $studentResitData['student_resit_id'],
                'school_branch_id' => $currentSchool->id,
                'transaction_id' => $transactionId
            ]);

            $studentResit->paid_status = "Paid";
            $studentResit->save();
            DB::commit();

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
    public function prepareResitScoresData($currentSchool, $examId, $studentId)
    {
        $results = [];
        $resitCoursesIds = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->plunk();

        foreach ($resitCoursesIds as $resitCourseId) {
            $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
                ->where("course_id", $resitCourseId)
                ->where("student_id", $studentId)
                ->with(['courses', 'level', 'specialty', 'student'])
                ->where("exam_status", "pending")
                ->first();
            if ($studentResit) {
                $studentScore = Marks::where("school_branch_id", $currentSchool->id)
                    ->where("student_id", $studentResit->student_id)
                    ->where("exam_id", $studentResit->exam_id)
                    ->where("specialty_id", $studentResit->specialty_id)
                    ->first();
                $results[] = [
                    'student_id' => $studentScore->student_id,
                    'student_name' => $studentResit->student->name,
                    'exam_id' => $studentScore->exam_id,
                    'course_id' => $studentScore->course_id,
                    'course_title' => $studentResit->title,
                    'score' => $studentScore->score,
                    'grade' => $studentScore->grade,
                    'grade_status' => $studentScore->grade_status
                ];
            }
        }

        return $results;
    }
    public function bulkDeleteStudentResit($studentResitIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentResitIds as $studentResitId) {
                $studentResit = Studentresit::findOrFail($studentResitId);
                $studentResit->delete();
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
                    ->find($studentResitId);

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
                $transaction = ResitFeeTransactions::findOrFail($transactionId['id']);
                $transaction->delete();
                $result[] = [
                    $transaction
                ];
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
    public function setResitDates($resitDates, $currentSchool, $resitId)
    {
        DB::beginTransaction();
        try {
            $resit = ResitExam::where("school_branch_id", $currentSchool->id)
                ->find($resitId);

            if (!$resit) {
                return ApiResponseService::error("Resit Not found", null, 404);
            }

            $resit->update($resitDates);
            dispatch(new CreateResitCandidates($resit));
            DB::commit();
            return $resit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function prepareEvaluationData($resitCandidateId, $resitId, $currentSchool)
    {
        $resitCandidate = ResitCandidates::findOrFail($resitCandidateId);
        $results = [];
        $resitExam = ResitExam::findOrFail($resitId);
        $studentResits = Studentresit::where('school_branch_id', $currentSchool->id)
            ->where('student_id', $resitCandidate->student_id)
            ->where('course_id', $resitExam->level_id)
            ->where('specialty_id', $resitExam->specialty_id)
            ->with(['courses', 'exam' => function ($query) use ($resitExam) {  // Use the 'use' statement to pass the variable
                $query->where('semester_id', $resitExam->semester_id);
            }])
            ->get();
        $resitTimetable = Resitexamtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $resitExam->id)
            ->with(['course'])
            ->get();
        foreach ($resitTimetable as $resitTimetableEntries) {
            foreach ($studentResits as $studentResitEntries) {
                if ($resitTimetableEntries->course_id == $studentResitEntries->course_id) {
                    $marks = Marks::where("school_branch_id", $currentSchool->id)
                        ->where("student_id", $resitCandidate->student_id)
                        ->where("course_id", $resitTimetableEntries->course_id)
                        ->where("level_id", $studentResitEntries->level_id)
                        ->where("specialty_id", $resitExam->specialty_id)
                        ->with(['exam' => function($query) use ($resitExam) {
                            $query->where('semester_id', $resitExam->semester_id);
                        }, 'exam.examtype', 'course'])
                        ->first();
                    $results[] = [
                        'score' => $marks,
                    ];
                }
            }
        }
        return $results;
    }
}
