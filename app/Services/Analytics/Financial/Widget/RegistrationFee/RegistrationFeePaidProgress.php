<?php

namespace App\Services\Analytics\Financial\Widget\RegistrationFee;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Aggregate\RegistrationFee\RegistrationFeePaymentRateAggregate;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
class RegistrationFeePaidProgress
{
    protected RegistrationFeePaymentRateAggregate $registrationFeePaymentRateAggregate;
    public function __construct(RegistrationFeePaymentRateAggregate $registrationFeePaymentRateAggregate)
    {
        $this->registrationFeePaymentRateAggregate  = $registrationFeePaymentRateAggregate;
    }

    public function getRegistrationFeePaymentRate($currentSchool, $year){
         $targetKpi = [
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED
         ];

         $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpi);
         return $this->registrationFeePaymentRateAggregate->calculate($query);
    }
}
