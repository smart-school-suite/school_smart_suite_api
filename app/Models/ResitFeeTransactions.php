<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitFeeTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'amount',
        'payment_method',
        'resitfee_id',
        'school_branch_id',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'resit_fee_transactions';

    public function studentResit() : BelongsTo {
         return $this->belongsTo(Studentresit::class, 'resitfee_id');
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
