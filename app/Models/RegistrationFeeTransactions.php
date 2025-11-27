<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class RegistrationFeeTransactions extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'id',
        'registrationfee_id',
        'school_branch_id',
        'amount',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public $table = "registration_fee_transactions";
    public $incrementing = 'false';
    public $keyType = 'string';

    public function registrationFee() : BelongsTo {
        return $this->belongsTo(RegistrationFee::class, 'registrationfee_id');
    }

}
