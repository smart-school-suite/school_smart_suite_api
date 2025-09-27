<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialtyHall extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
         'specialty_id',
         'level_id',
         'hall_id',
         'school_branch_id'
    ];


    public function specialty(): BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level(): BelongsTo {
         return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function hall(): BelongsTo {
         return $this->belongsTo(Hall::class, 'hall_id');
    }

    public function schoolBranch(): BelongsTo {
         return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }
}
