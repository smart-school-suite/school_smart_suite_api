<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'promo_code',
        'commission_percentage',
        'discount_percentage',
        'account_balance',
        'status',
        'country_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'affiliates';

    public function affiliateApplication(): HasMany {
         return $this->hasMany(AffiliateApplication::class);
    }
    public function affiliatePayout(): HasMany {
         return $this->hasMany(AffiliatePayout::class);
    }
    public function affiliateCommission(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
