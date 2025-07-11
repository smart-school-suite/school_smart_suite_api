<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'school_subscription_id', 'payment_date', 'amount', 'payment_method',
        'payment_status', 'transaction_id', 'currency', 'description', 'school_branch_id'
    ];


    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'payments';

    public function schoolSubscription()
    {
        return $this->belongsTo(SchoolSubscription::class);
    }


}
