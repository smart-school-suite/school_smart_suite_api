<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schoolbranches extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'school_id',
        'branch_name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone_one',
        'phone_two',
        'semester_count',
        'email',
        'final_semester',
        'abbrevaition',
        'max_gpa'
    ];

    public $keyType = 'string';
    public $table = 'school_branches';
    public $incrementing = 'false';

    public function studentGradDates(): HasMany
    {
        return $this->hasMany(StudentBatchGradeDates::class);
    }

    public function announcementTargetGroup(): HasMany {
        return $this->hasMany(AnnouncementTargetGroup::class, 'school_branch_id');
    }

    public function announcementTag(): HasMany {
        return $this->hasMany(AnnouncementTag::class, 'school_branch_id');
    }

    public function announcementCategory(): HasMany {
        return $this->hasMany(AnnouncementCategory::class, 'school_branch_id');
    }

    public function announcement(): HasMany {
        return $this->hasMany(Announcement::class, 'school_branch_id');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    public function schoolBranchApiKey(): BelongsTo
    {
        return $this->belongsTo(SchoolBranchApiKey::class, 'school_branch_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }

    public function department(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function schoolEventSetting(): HasMany {
        return $this->hasMany(SchoolEventSetting::class, 'school_branch_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class);
    }

    public function feepayment(): HasMany
    {
        return $this->hasMany(Feepayment::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Marks::class);
    }

    public function parents(): HasMany
    {
        return $this->hasMany(Parents::class);
    }

    public function schooladmin(): HasMany
    {
        return $this->hasMany(Schooladmin::class);
    }

    public function schoolbranches(): HasMany
    {
        return $this->hasMany(Schoolbranches::class);
    }

    public function specialty(): HasMany
    {
        return $this->hasMany(Specialty::class);
    }

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
