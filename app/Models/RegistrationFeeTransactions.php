<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationFeeTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_fee_id',
        'school_branch_id',
        'amount',
        'payment_method',
        'transaction_id',
    ];

    public $table = "registration_fee_transactions";
    public $incrementing = 'false';
    public $keyType = 'string';

    public function registrationFee() : BelongsTo {
        return $this->belongsTo(RegistrationFee::class, 'registrationfee_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }
}
