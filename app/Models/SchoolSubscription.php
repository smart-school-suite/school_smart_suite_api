<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class SchoolSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'subscription_plan_id', 'total_monthly_cost', 'total_yearly_cost', 'billing_frequency',
        'status', 'subscription_start_date', 'subscription_end_date', 'subscription_renewal_date',
        'auto_renewal', 'rate_card_id', 'max_number_students', 'max_number_parents', 'max_number_school_admins',
        'max_number_teacher'
    ];

    protected $dates = ['subscription_start_date', 'subscription_end_date', 'subscription_renewal_date', 'deleted_at'];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'school_subscriptions';

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function rateCard()
    {
        return $this->hasOne(RatesCard::class);
    }
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });

    }
}
