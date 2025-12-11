<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePayout extends Model
{
    protected $fillable = [
        'affiliate_id',
        'country_id',
        'transaction_id',
        'amount',
        'status',
        'payment_method',
        'payment_ref'
    ];

    public $incrementing = 'false';
    public $table = 'affiliate_payouts';
    public $keyType = 'string';
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
