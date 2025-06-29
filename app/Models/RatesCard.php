<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class RatesCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'min_students', 'max_students', 'max_school_admins', 'max_parents',
        'monthly_rate_per_student', 'yearly_rate_per_student', 'is_active', 'subscription_plan_id'
    ];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'rate_cards';


    public function schoolSubscriptions()
    {
        return $this->hasMany(SchoolSubscription::class);
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
