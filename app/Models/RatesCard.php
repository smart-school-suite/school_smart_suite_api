<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatesCard extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'min_students',
        'max_students',
        'max_school_admins',
        'max_parents',
        'monthly_rate_per_student',
        'yearly_rate_per_student',
        'is_active',
        'subscription_plan_id'
    ];

    protected $cast = [
        'min_students' => 'integer',
        'max_students' => 'integer',
        'max_school_admins' => 'integer',
        'max_parents' => 'integer',
        'monthly_rate_per_student' => 'decimal|2',
        'yearly_rate_per_student' => 'decimal|2',
    ];
    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'rate_cards';


    public function schoolSubscriptions()
    {
        return $this->hasMany(SchoolSubscription::class);
    }
}
