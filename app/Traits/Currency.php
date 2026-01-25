<?php

namespace App\Traits;
use App\Models\Schoolbranches;
trait Currency
{
    public function getBranchCurrency(): string
    {
        return Schoolbranches::where('id', $this->school_branch_id)
            ->with('school.country')
            ->first()?->school?->country?->currency ?? 'N/A';
    }

    public function formatAmount($amount): string
    {
        return $this->getBranchCurrency() . number_format($amount, 2);
    }
}
