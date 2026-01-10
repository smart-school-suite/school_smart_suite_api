<?php

namespace App\Constant\Analytics\Financial;

class FinancialAnalyticsEvent
{
    public const REGISTRATION_FEE_PAID = 'finance.registration_fee.paid';
    public const REGISTRATION_FEE_REVERSED = 'finance.registration_fee.reversed';
    public const REGISTRATION_FEE_INCURRED = 'finance.registration_fee.incurred';

    public const TUITION_FEE_PAID = 'finance.tuition_fee.paid';
    public const TUITION_FEE_REVERSED = 'finance.tuition_fee.reversed';
    public const TUITION_FEE_INCURRED = 'finance.tuition_fee.incurred';

    public const ADDITIONAL_FEE_PAID = 'finance.additional_fee.paid';
    public const ADDITIONAL_FEE_REVERSED = 'finance.additional_fee.reversed';
    public const ADDITIONAL_FEE_INCURRED = 'finance.additional_fee.incurred';
    public const ADDITIONAL_FEE_UPDATED = "finance.additional_fee.updated";

    public const RESIT_FEE_PAID = 'finance.resit_fee.paid';
    public const RESIT_FEE_REVERSED = 'finance.resit_fee.reversed';
    public const RESIT_FEE_INCURRED = 'finance.resit_fee.incurred';

    public const EXPENSE_INCURRED = 'finance.expense.incurred';
    public const EXPENSE_UPDATED = 'finance.expense.updated';
    public const EXPENSE_DELETED = 'finance.expense.deleted';
}
