<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    protected $fillable = [
         'affiliate_id',
         'school_branch_id',
         'school_transaction_id',
         'country_id',
         'percentage',
         'amount'
    ];

    public function affiliate(): BelongsTo {
         return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }

    public function schoolBranch(): BelongsTo {
         return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function schoolTransaction(): BelongsTo {
         return $this->belongsTo(SchoolTransaction::class, 'school_transaction_id');
    }

    public function country(): BelongsTo {
         return $this->belongsTo(Country::class, 'country_id');
    }
}
