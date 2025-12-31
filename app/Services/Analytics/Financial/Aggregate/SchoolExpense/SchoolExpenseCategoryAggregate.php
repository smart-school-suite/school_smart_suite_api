<?php

namespace App\Services\Analytics\Financial\Aggregate\SchoolExpense;

use App\Models\Schoolexpensescategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class SchoolExpenseCategoryAggregate
{
    public function calculate(Collection $query, $schoolBranchId)
    {
        $categories = Schoolexpensescategory::where("school_branch_id", $schoolBranchId)->all();
        $categories->map(function ($category) use ($query) {
            $expenseTotal = $query->where("category_id", $category->id)->sum("value");
            return [
                "category_id" => $category->id,
                "category_name" => $category->name,
                "total_expense" => $expenseTotal ?? 0
            ];
        });
    }
}
