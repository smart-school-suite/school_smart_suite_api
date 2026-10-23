<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolTransaction extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'type',
        'amount',
        'payment_ref',
        'transaction_id',
        'status',
        'payment_method_id',
        'country_id',
        'school_branch_id',
    ];


    public $table = 'school_transactions';
    public $keyType = 'string';
    public $incrementing = false;

    public function paymentMethod(): BelongsTo {
         return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
    public function affiliateCommission(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
