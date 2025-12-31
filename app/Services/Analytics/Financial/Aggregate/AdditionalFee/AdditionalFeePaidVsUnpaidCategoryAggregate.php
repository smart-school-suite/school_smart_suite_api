<?php

namespace App\Services\Analytics\Financial\Aggregate\AdditionalFee;

use App\Models\AdditionalFeesCategory;
use Illuminate\Support\Collection;

class AdditionalFeePaidVsUnpaidCategoryAggregate
{
    public function calculate(Collection $query, $schoolBranchId)
    {
        $categories = AdditionalFeesCategory::where("school_branch_id", $schoolBranchId)
            ->get();
      return   $categories->map(function ($category) use ($query) {
            $paid = $query->where("category_id", $category->id)->sum("value");
            $unpaid = $query->where("category_id", $category->id)->sum("value");
            return [
                "paid" => $paid,
                "unpaid" => $unpaid,
                "payment_rate" => round($paid / $unpaid * 100, 2),
                "category_id" => $category->id,
                "category_name" => $category->title ?? "unknown"
            ];
        });
    }
}
