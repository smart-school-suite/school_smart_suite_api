<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class TuitionFeeTransactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'amount',
        'payment_method',
        'tuition_id',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'tuition_fee_transactions';

    public function tuition(){
        return $this->belongsTo(TuitionFees::class, 'tuition_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 30);
         });

    }
}
