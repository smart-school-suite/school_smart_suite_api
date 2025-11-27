<?php

namespace App\Services\TuitionFee;

use App\Jobs\NotificationJobs\SendAdminTuitionFeePaidNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\TuitionFeePaymentStatJob;
use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use App\Models\TuitionFeeTransactions;
use App\Notifications\TuitionFeePaid;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\RegistrationFeeTransactions;
use App\Models\AdditionalFeeTransactions;
use App\Exceptions\AppException;
use Throwable;

class TuitionFeePaymentService
{
    public function payStudentFees(array $data, object $currentSchool): TuitionFees
    {
        DB::beginTransaction();

        try {
            $studentTuitionFees = TuitionFees::findOrFail($data['tuition_id']);
            $tuitionId = $studentTuitionFees->id;

            $student = Student::where('school_branch_id', $currentSchool->id)
                ->where('id', $studentTuitionFees->student_id)
                ->first();

            if (!$student) {
                throw new AppException(
                    "Student ID '{$studentTuitionFees->student_id}' not found for the given tuition record or school branch ID '{$currentSchool->id}'.",
                    404,
                    "Student Not Found 🧑‍🎓",
                    "The student associated with this tuition record could not be found at your school branch.",
                    null
                );
            }

            $paymentAmount = $data['amount'];

            if ($paymentAmount <= 0) {
                throw new AppException(
                    'Payment amount must be greater than zero.',
                    400,
                    "Invalid Amount 💲",
                    "The amount entered for payment must be a positive number.",
                    null
                );
            }

            if ($paymentAmount > $studentTuitionFees->amount_left) {
                throw new AppException(
                    "Payment amount ({$paymentAmount}) exceeds the remaining fee debt ({$studentTuitionFees->amount_left}).",
                    409,
                    "Overpayment Conflict ❌",
                    "The amount you entered exceeds the balance currently owed on this tuition fee.",
                    null
                );
            }

            if ($student->payment_format === "one-time" && $studentTuitionFees->tution_fee_total != $paymentAmount) {
                throw new AppException(
                    "Student payment format is 'one-time', requiring the full amount ({$studentTuitionFees->tution_fee_total}) to be paid.",
                    400,
                    "Full Payment Required 💳",
                    "This student's payment plan requires the full tuition amount to be paid in a single transaction.",
                    null
                );
            }

            $studentTuitionFees->amount_paid += $paymentAmount;
            $studentTuitionFees->amount_left -= $paymentAmount;

            if ($studentTuitionFees->amount_left <= 0) {
                $studentTuitionFees->status = "completed";
                $studentTuitionFees->amount_left = 0;
            }

            $studentTuitionFees->save();

            $paymentId = Str::uuid();
            $transactionId = 'TXN-' . strtoupper(Str::random(10));

            TuitionFeeTransactions::create([
                'id' => $paymentId,
                'transaction_id' => $transactionId,
                'amount' => $paymentAmount,
                'payment_method' => $data['payment_method'],
                'tuition_id' => $tuitionId,
                'school_branch_id' => $currentSchool->id,
            ]);

            DB::commit();

            $paymentDetails = [
                'amountPaid' => $paymentAmount,
                'balanceLeft' => $studentTuitionFees->amount_left,
                'paymentDate' => now()
            ];

            TuitionFeePaymentStatJob::dispatch($paymentId, $currentSchool->id);
            SendAdminTuitionFeePaidNotificationJob::dispatch($currentSchool->id, $student, $paymentDetails);
            $student->notify(new TuitionFeePaid($paymentAmount, $studentTuitionFees->amount_left, now()));

            return $studentTuitionFees;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "Tuition fee record ID '{$data['tuition_id']}' not found.",
                404,
                "Tuition Record Not Found 🔎",
                "The specific tuition record you are attempting to pay could not be located.",
                null
            );
        } catch (QueryException $e) {
            DB::rollBack();
            throw new AppException(
                'A database error occurred during the payment process. Please try again.',
                500,
                "Database Error 🚨",
                "We encountered a critical error while recording the payment. The transaction has been cancelled.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred during the fee payment process: " . $e->getMessage(),
                500,
                "Payment System Failure 🛑",
                "An unknown system error prevented the payment from being processed. Please contact support.",
                null
            );
        }
    }
    public function getStudentFinancialTransactions($currentSchool, $student)
    {
        $schoolBranchId = $currentSchool->id;
        $studentId      = $student->id;
        $studentName    = $student->name;

        // 1. Tuition Fee Transactions
        $tuitionTransactions = TuitionFeeTransactions::where('school_branch_id', $schoolBranchId)
            ->whereHas('tuition', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->with('tuition')
            ->get()
            ->map(function ($txn) use ($studentName) {
                return [
                    'id'              => $txn->id,
                    'payer_name'      => $studentName,
                    'transaction_id'  => $txn->transaction_id ?? 'N/A',
                    'payment_date'    => $txn->created_at,
                    'payment_title'   => 'Tuition Fee',
                    'payment_method'  => $txn->payment_method ?? 'Unknown',
                    'amount'          => $txn->amount,
                ];
            });
        $additionalTransactions = AdditionalFeeTransactions::where('school_branch_id', $schoolBranchId)
            ->whereHas('additionFee', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->with('additionFee.feeCategory')
            ->get()
            ->map(function ($txn) use ($studentName) {
                $categoryTitle = $txn->additionFee?->feeCategory?->title ?? 'Additional Fee';
                return [
                    'id'              => $txn->id,
                    'payer_name'      => $studentName,
                    'transaction_id'  => $txn->transaction_id ?? 'N/A',
                    'payment_date'    => $txn->created_at,
                    'payment_title'   => $categoryTitle,
                    'payment_method'  => $txn->payment_method ?? 'Unknown',
                    'amount'          =>  $txn->amount,
                ];
            });
        $registrationTransactions = RegistrationFeeTransactions::where('school_branch_id', $schoolBranchId)
            ->whereHas('registrationFee', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->with('registrationFee')
            ->get()
            ->map(function ($txn) use ($studentName) {
                return [
                    'id'              => $txn->id,
                    'payer_name'      => $studentName,
                    'transaction_id'  => $txn->transaction_id ?? 'N/A',
                    'payment_date'    => $txn->created_at,
                    'payment_title'   => 'Registration Fee',
                    'payment_method'  => $txn->payment_method ?? 'Unknown',
                    'amount'          => $txn->amount,
                ];
            });
        $allTransactions = collect()
            ->merge($tuitionTransactions)
            ->merge($additionalTransactions)
            ->merge($registrationTransactions)
            ->sortByDesc('payment_date')
            ->values();

        return $allTransactions;
    }
}
