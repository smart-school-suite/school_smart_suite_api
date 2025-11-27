<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Hall extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'type',
        'location',
        'school_branch_id',
        'is_exam_hall'
    ];

    protected $cast = [
        'capacity' => 'integer'
    ];

    public $keyType = 'string';
    public $table = 'halls';
    public $incrementing = 'false';


    public function specialtyHall(): HasMany {
         return $this->hasMany(SpecialtyHall::class);
    }
    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }
}
