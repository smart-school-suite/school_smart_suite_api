<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'school_subscription_id', 'payment_date', 'amount', 'payment_method',
        'payment_status', 'transaction_id', 'currency', 'description', 'school_id'
    ];


    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'payments';

    public function schoolSubscription()
    {
        return $this->belongsTo(SchoolSubscription::class);
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 15);
         });

    }
}
