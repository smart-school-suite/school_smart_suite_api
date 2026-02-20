<?php

namespace App\Models;

use App\Models\Course\JointCourseSlot;
use App\Models\SemesterTimetable\SemesterTimetableSlot;
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
        'location',
        'school_branch_id',
        'num_assigned_specialties',
        'assignment_status'
    ];

    protected $cast = [
        'capacity' => 'integer'
    ];
    public $keyType = 'string';
    public $table = 'halls';
    public $incrementing = false;

    public function jointCourseSlot(): HasMany
    {
        return $this->hasMany(JointCourseSlot::class);
    }
    public function specialtyHall(): HasMany
    {
        return $this->hasMany(SpecialtyHall::class);
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(SchoolBranches::class, 'school_branch_id');
    }
    public function semesterTimetableSlot(): HasMany
    {
        return $this->hasMany(SemesterTimetableSlot::class);
    }
    public function types()
    {
        return $this->belongsToMany(HallType::class, 'school_hall_types')
            ->using(SchoolHallType::class)
            ->withPivot(['id', 'school_branch_id'])
            ->withTimestamps();
    }
    public function syncTypes(array $typeIds)
    {
        return $this->types()->sync(
            collect($typeIds)->mapWithKeys(fn($id) => [
                $id => ['school_branch_id' => $this->school_branch_id],
            ])->toArray()
        );
    }
}
