<?php

namespace App\Models\Graduation;

use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GraduationBatch extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'name',
        'description',
        'graduation_start_date',
        'graduation_end_date',
        'location',
        'school_year_id',
        'school_branch_id'
    ];

    protected $casts = [
        'graduation_start_date' => 'datetime',
        'graduation_end_date' => 'datetime'
    ];

    public $incrementing = false;
    public $table = 'graduation_batches';
    public $keyType = 'string';

    public function graduationRecord(): HasMany
    {
        return $this->hasMany(GraduationRecord::class, 'batch_id');
    }
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'school_year_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
