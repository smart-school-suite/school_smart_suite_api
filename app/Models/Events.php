<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'shool_branches_id',
        'event_name',
        'event_date',
        'location',
        'attendance',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'events';

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function school(): HasMany {
        return $this->hasMany(School::class);
    }
    
    public function schoolbranches(): HasMany {
        return $this->hasMany(Schoolbranches::class);
    }

    public function specialties(): HasMany {
        return $this->hasMany(Specialty::class);
    }
   
    
}
