<?php

namespace App\Services\Analytics\Financial\Widget\Revenue;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\Revenue\SchoolRevenueAggregate;

class SchoolRevenue
{
    protected SchoolRevenueAggregate $schoolRevenueAggregate;
    public function __construct(SchoolRevenueAggregate $schoolRevenueAggregate)
    {
        $this->schoolRevenueAggregate = $schoolRevenueAggregate;
    }

    public function getSchoolRevenue($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID,
            FinancialAnalyticsKpi::TUITION_FEE_PAID,
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
            FinancialAnalyticsKpi::RESIT_FEE_PAID,
            FinancialAnalyticsKpi::EXPENSE_INCURRED
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->schoolRevenueAggregate->calculate($query);
    }
}
