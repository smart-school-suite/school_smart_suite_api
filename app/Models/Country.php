<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'country',
        'code',
        'status',
        'currency',
        'official_language'
    ];

    public $keyType = 'string';
    public $table = 'countries';
    public $incrementing = 'false';

    public function paymentMethod(): HasMany {
         return $this->hasMany(PaymentMethod::class);
    }
    public function affiliateApplication(): HasMany
    {
        return $this->hasMany(AffiliateApplication::class);
    }
    public function affiliatePayout(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }
    public function affiliateCommission(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
    public function activationCodeUsage(): HasMany
    {
        return $this->hasMany(ActivationCodeUsage::class);
    }
    public function activationCode(): HasMany
    {
        return $this->hasMany(ActivationCode::class);
    }
    public function schoolTransaction(): HasMany
    {
        return $this->hasMany(SchoolTransaction::class);
    }
    public function plan(): HasMany
    {
        return $this->hasMany(Plan::class);
    }
    public function feature(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
    public function school(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function planFeature(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function schoolSubscription(): HasMany
    {
        return $this->hasMany(SchoolSubscription::class);
    }
}
