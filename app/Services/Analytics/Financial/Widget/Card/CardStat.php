<?php

namespace App\Services\Analytics\Financial\Widget\Card;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\AdditionalFee\AdditionalFeePaidAggregate;
use App\Services\Analytics\Financial\Aggregate\RegistrationFee\RegistrationFeePaidAggregator;
use App\Services\Analytics\Financial\Aggregate\SchoolExpense\SchoolExpenseAggregate;

class CardStat
{
    protected AdditionalFeePaidAggregate $additionalFeePaidAggregate;
    protected RegistrationFeePaidAggregator $registrationFeePaidAggregator;
    protected SchoolExpenseAggregate $schoolExpenseAggregate;
    public function __construct(
        AdditionalFeePaidAggregate $additionalFeePaidAggregate,
        RegistrationFeePaidAggregator $registrationFeePaidAggregator,
        SchoolExpenseAggregate $schoolExpenseAggregate
    ) {
        $this->additionalFeePaidAggregate = $additionalFeePaidAggregate;
        $this->registrationFeePaidAggregator = $registrationFeePaidAggregator;
        $this->schoolExpenseAggregate = $schoolExpenseAggregate;
    }
    public function getCardData($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID,
            FinancialAnalyticsKpi::REGISTRATION_FEE_PAID,
            FinancialAnalyticsKpi::EXPENSE_INCURRED
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);

        return [
            "additional_fee" => $this->additionalFeePaidAggregate->calculate($query),
            "registration_fee" =>  $this->registrationFeePaidAggregator->calculate($query),
            "school_expense" =>  $this->schoolExpenseAggregate->calculate($query)
        ];
    }
}
