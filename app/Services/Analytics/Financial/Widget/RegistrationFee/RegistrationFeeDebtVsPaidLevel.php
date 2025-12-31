<?php

namespace App\Services\Analytics\Financial\Widget\RegistrationFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Aggregate\RegistrationFee\RegistrationFeePaidVsUnpaidLevel;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;

class RegistrationFeeDebtVsPaidLevel
{
    protected RegistrationFeePaidVsUnpaidLevel $registrationFeePaidVsUnpaidLevel;
    public function __construct(RegistrationFeePaidVsUnpaidLevel $registrationFeePaidVsUnpaidLevel)
    {
        $this->registrationFeePaidVsUnpaidLevel = $registrationFeePaidVsUnpaidLevel;
    }

    public function getRegistrationFeePaidUnpaidLevel($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED,
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->registrationFeePaidVsUnpaidLevel->calculate($query);
    }
}
