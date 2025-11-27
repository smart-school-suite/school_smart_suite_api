<?php

namespace App\Traits;
use App\Models\Schoolbranches;
trait Currency
{
    public function getBranchCurrency(): string
    {
        return Schoolbranches::where('school_branch_id', $this->school_branch_id)
            ->with('country')
            ->first()?->country?->currency ?? 'NGN';
    }

    // public function getBranchCurrencySymbol(): string
    // {
    //     return match (strtoupper($this->getBranchCurrency())) {
    //         'NGN' => '₦',
    //         'USD' => '$',
    //         'EUR' => '€',
    //         'GBP' => '£',
    //         'GHS' => 'GH₵',
    //         default => $this->getBranchCurrency(),
    //     };
    // }

    public function formatAmount($amount): string
    {
        return $this->getBranchCurrency() . number_format($amount, 2);
    }
}
