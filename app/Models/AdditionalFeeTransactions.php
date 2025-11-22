<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalFeeTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'transaction_id',
        'amount',
        'payment_method',
        'fee_id',
        'school_branch_id'
    ];

    protected $cast = [
         'amount' => 'decimal:2'
    ];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'additional_fee_transactions';

    public function additionFee() : BelongsTo {
         return $this->belongsTo(AdditionalFees::class, 'fee_id');
    }
}
