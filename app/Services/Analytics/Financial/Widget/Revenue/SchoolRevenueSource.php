<?php

namespace App\Services\Analytics\Financial\Widget\Revenue;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\Revenue\SchoolRevenueSourceAggregate;

class SchoolRevenueSource
{
    protected SchoolRevenueSourceAggregate $schoolRevenueSourceAggregate;
    public function __construct(SchoolRevenueSourceAggregate $schoolRevenueSourceAggregate)
    {
        $this->schoolRevenueSourceAggregate = $schoolRevenueSourceAggregate;
    }

    public function getSchoolRevenueSource($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID,
            FinancialAnalyticsKpi::TUITION_FEE_PAID,
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
            FinancialAnalyticsKpi::RESIT_FEE_PAID
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->schoolRevenueSourceAggregate->calculate($query);
    }
}
