<?php

namespace App\Services\RegistrationFee;

use App\Jobs\NotificationJobs\SendAdminRegistrationFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendRegistrationFeePaidNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\RegistrationFeeStatJob;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationFeeTransactions;
use Illuminate\Support\Str;
use App\Models\RegistrationFee;
use Exception;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Events\Analytics\FinancialAnalyticsEvent;
use App\Constant\Analytics\Financial\FinancialAnalyticsEvent as FinancialEventConstant;

class RegistrationFeePayment
{
    public function payRegistrationFees(array $data, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $registrationFeeData = [];
            $registrationFee = RegistrationFee::with(['student', 'specialty'])
                ->where("school_branch_id", $currentSchool->id)
                ->find($data['registration_fee_id']);
            if (!$registrationFee) {
                return ApiResponseService::error("Student Registration Fee Appears To Be Deleted", null, 404);
            }
            if ($registrationFee->status === 'paid') {
                return ApiResponseService::error("Registration Fee Already Completed", null, 409);
            }

            if ($registrationFee->amount < $data['amount']) {
                return ApiResponseService::error("Amount Paid : {$data['amount']} is Greater than the registration fee: {$registrationFee->amount}.");
            }

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            $feePaymentId = Str::uuid();
            $transaction = RegistrationFeeTransactions::create([
                'id' => $feePaymentId,
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'registrationfee_id' => $data['registration_fee_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            $registrationFee->status = 'paid';
            $registrationFee->save();
            $registrationFeeData[] = [
                'student' => $registrationFee->student,
                'amount' => $data['amount'],
            ];
            DB::commit();
            RegistrationFeeStatJob::dispatch($feePaymentId, $currentSchool->id);
            SendAdminRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            SendRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.registrationFee.pay"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "registrationFeeManagement",
                    "authAdmin" => $authAdmin,
                    "data" => [
                        "transaction" => $transaction,
                        "registration_fee" => $registrationFee
                    ],
                    "message" => "Registration Fee Paid",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$registrationFee->student_id],
                'feature'      => 'registrationFeePaid',
                'message'      => 'Registration Fees Paid',
                'data'         => $registrationFeeData,
            ]);
            event(new FinancialAnalyticsEvent(
                eventType: FinancialEventConstant::REGISTRATION_FEE_PAID,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "level_id" => $registrationFee->level_id,
                    "specialty_id" => $registrationFee->specialty->id,
                    "department_id" => $registrationFee->specialty->department_id,
                    "value" => $registrationFee->amount
                ]
            ));
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkPayRegistrationFee(array $feeDataArray, $currentSchool, $authAdmin)
    {
        try {
            DB::beginTransaction();
            $registrationFeeData = [];
            $studentIds = [];
            foreach ($feeDataArray as $feeData) {
                $registrationFee = RegistrationFee::where("school_branch_id", $currentSchool->id)
                    ->with(['student'])
                    ->find($feeData['registration_fee_id']);
                if (!$registrationFee) {
                    return ApiResponseService::error("Student Registration Fee Appears To Be Deleted", null, 404);
                }
                if ($registrationFee->status === 'paid') {
                    return ApiResponseService::error("Registration Fee Already Completed", null, 409);
                }

                if ($registrationFee->amount < $feeData['amount']) {
                    return ApiResponseService::error("Amount Paid : {$feeData['amount']} is Greater than the registration fee: {$registrationFee->amount}.");
                }

                $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

                RegistrationFeeTransactions::create([
                    'transaction_id' => $transactionId,
                    'amount' => $feeData['amount'],
                    'payment_method' => $feeData['payment_method'],
                    'registrationfee_id' => $feeData['registration_fee_id'],
                    'school_branch_id' => $currentSchool->id,
                ]);

                $registrationFee->status = 'paid';
                $registrationFee->save();
                $registrationFeeData[] = [
                    'student' => $registrationFee->student,
                    'amount' => $feeData['amount'],
                ];
                $studentIds[] =  $registrationFee->student_id;
            }
            DB::commit();
            SendAdminRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            SendRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.registrationFee.pay"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "registrationFeeManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $registrationFeeData,
                    "message" => "Registration Fee Paid",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => $studentIds,
                'feature'      => 'registrationFeePaid',
                'message'      => 'Registration Fees Paid',
                'data'         => $registrationFeeData,
            ]);
            event(new FinancialAnalyticsEvent(
                eventType: FinancialEventConstant::REGISTRATION_FEE_PAID,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "level_id" => $registrationFee->level_id,
                    "specialty_id" => $registrationFee->specialty->id,
                    "department_id" => $registrationFee->specialty->department_id,
                    "value" => $registrationFee->amount
                ]
            ));
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
