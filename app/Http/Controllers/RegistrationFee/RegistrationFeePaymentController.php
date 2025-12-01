<?php

namespace App\Http\Controllers\RegistrationFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationFee\BulkPayRegistrationFeeRequest;
use App\Http\Requests\RegistrationFee\PayRegistrationFeeRequest;
use App\Services\ApiResponseService;
use App\Services\RegistrationFee\RegistrationFeePayment;

class RegistrationFeePaymentController extends Controller
{
    protected RegistrationFeePayment $registrationFeePayment;

    public function __construct(RegistrationFeePayment $registrationFeePayment)
    {
        $this->registrationFeePayment = $registrationFeePayment;
    }

    public function payRegistrationFee(PayRegistrationFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $payRegistrationFees = $this->registrationFeePayment->payRegistrationFees($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Registration Fees Paid Successfully", $payRegistrationFees, null, 200);
    }

    public function bulkPayRegistrationFee(BulkPayRegistrationFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkPayRegistrationFee = $this->registrationFeePayment->bulkPayRegistrationFee($request->registration_fee, $currentSchool, $authAdmin);
        return ApiResponseService::success("Fee Paid Succesfully", $bulkPayRegistrationFee, null, 200);
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
