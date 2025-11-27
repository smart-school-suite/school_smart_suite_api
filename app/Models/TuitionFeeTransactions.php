<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TuitionFeeTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'transaction_id',
        'amount',
        'payment_method',
        'tuition_id',
        'school_branch_id'
    ];

    public $casts = [
         'amount' => 'decimal:2'
    ];
    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'tuition_fee_transactions';

    public function tuition(){
        return $this->belongsTo(TuitionFees::class, 'tuition_id');
    }

}
