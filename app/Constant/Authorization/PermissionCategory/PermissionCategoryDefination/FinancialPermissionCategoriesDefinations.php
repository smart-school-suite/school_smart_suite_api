<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\FinancePermissionCategories;

class FinancialPermissionCategoriesDefinations
{
 public static function all(): array {
    return [
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::ADDITIONAL_FEE_MANAGER,
            "Additional Fee Manager",
            "Allows management of non-tuition costs such as lab fees, library fines, or extracurricular charges."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::ADDITIONAL_FEE_CATEGORY_MANAGER,
            "Additional Fee Category Manager",
            "Enables the organization of miscellaneous fees into groups like 'Academic', 'Social', or 'Administrative'."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::ADDITIONAL_FEE_TRANSACTION_MANAGER,
            "Additional Fee Transaction Manager",
            "Provides authority to process, track, and refund payments made for additional (non-tuition) fees."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::TUITION_FEE_MANAGER,
            "Tuition Fee Manager",
            "Grants control over the base tuition rates for different programs, departments, or student types."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::TUITION_FEE_SCHEDULE_MANAGER,
            "Tuition Fee Schedule Manager",
            "Used to define the academic year's payment calendar and global billing deadlines."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::TUITION_FEE_SCHEDULE_SLOT_MANAGER,
            "Tuition Fee Schedule Slot Manager",
            "Enables fine-grained control over specific billing windows or priority groups within a schedule."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::TUITION_FEE_TRANSACTION_MANAGER,
            "Tuition Fee Transaction Manager",
            "Allows staff to record tuition payments, view student ledgers, and manage payment receipts."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::TUITION_FEE_WAIVER_MANAGER,
            "Tuition Fee Waiver Manager",
            "Provides authority to grant scholarships, discounts, or financial exemptions to specific students."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::REGISTRATION_FEE_MANAGER,
            "Registration Fee Manager",
            "Manages the costs associated with the initial enrollment or application process for students."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::REGISTRATION_FEE_TRANSACTION_MANAGER,
            "Registration Fee Transaction Manager",
            "Used to track and verify payments specifically made during the registration or intake phase."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::RESIT_PAYMENT_MANAGER,
            "Resit Payment Manager",
            "Allows management of fees and payment verification for students retaking examinations."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::SCHOOL_SUBSCRIPTION_MANAGER,
            "School Subscription Manager",
            "Controls institutional subscription levels, service tiers, and recurring platform access fees."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::SCHOOL_SUBSCRIPTION_TRANSACTION_MANAGER,
            "School Subscription Transaction Manager",
            "Provides access to the payment history and invoicing records for the school's platform subscriptions."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::SCHOOL_EXPENSE_MANAGER,
            "School Expense Manager",
            "Allows for the recording and tracking of outgoing funds, such as utility bills, staff salaries, and supplies."
        ),
        PermissionCategoryBuilder::make(
            FinancePermissionCategories::SCHOOL_EXPENSE_CATEGORY_MANAGER,
            "School Expense Category Manager",
            "Enables the categorization of spending into groups like 'Maintenance', 'Payroll', or 'Infrastructure'."
        )
    ];
}
}
