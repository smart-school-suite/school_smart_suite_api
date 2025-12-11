<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'status',
        'start_date',
        'end_date',
        'country_id',
        'school_branch_id',
        'plan_id'
    ];

    protected $dates = ['start_date', 'end_date'];

    public $keyType = 'string';
    public $incrementing = false;
    public $table = 'school_subscriptions';

    public function subscriptionUsage(): HasMany {
         return $this->hasMany(SchoolSubscription::class);
    }
    public function country(): BelongsTo {
         return $this->belongsTo(Country::class, 'country_id');
    }

    public function schoolBranch(): BelongsTo {
         return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function plan(): BelongsTo {
         return $this->belongsTo(Plan::class, 'plan_id');
    }
}
