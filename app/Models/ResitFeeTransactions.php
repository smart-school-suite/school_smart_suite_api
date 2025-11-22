<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class ResitFeeTransactions extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'transaction_id',
        'amount',
        'payment_method',
        'resitfee_id',
        'school_branch_id',
    ];

    protected $cast = [
         'amount' => 'decimal:2'
    ];
    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'resit_fee_transactions';

    public function studentResit() : BelongsTo {
         return $this->belongsTo(Studentresit::class, 'resitfee_id');
    }
}
