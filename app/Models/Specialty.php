<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'specialty_name',
        'registration_fee',
        'school_fee',
        'level_id',
        'status',
        'description',
        'school_branch_id',
    ];

    public $keyType = 'string';
    public $table = 'specialty';
    public $incrementing = 'false';

    public function studentGradDates(): HasMany
    {
        return $this->hasMany(StudentBatchGradeDates::class);
    }

    public function feeSchedule(): HasMany {
        return $this->hasMany(FeeSchedule::class);
    }

    public function electionParticipants(): HasMany
    {
        return $this->hasMany(ElectionParticipants::class);
    }
    public function feeWaiver(): HasMany
    {
        return $this->hasMany(FeeWaiver::class, 'specialty_id');
    }
    public function resitResults(): HasMany
    {
        return $this->hasMany(ResitResults::class);
    }
    public function studentFeeSchedule(): HasMany {
        return $this->hasMany(StudentFeeSchedule::class);
    }
    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    public function resitmarks(): HasMany
    {
        return $this->hasMany(ResitMarks::class, 'specialty_id');
    }
    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResults::class, 'specialty_id');
    }
    public function additionalFees(): HasMany
    {
        return $this->hasMany(AdditionalFees::class, 'specialty_id');
    }
    public function feePaymentSchedule(): HasMany
    {
        return $this->hasMany(FeePaymentSchedule::class, 'specialty_id');
    }
    public function hos()
    {
        return $this->hasMany(HOS::class, 'specialty_id');
    }
    public function registrationFee(): HasMany
    {
        return $this->hasMany(RegistrationFee::class, 'specialty_id');
    }
    public function tuitionFees(): HasMany
    {
        return $this->hasMany(TuitionFees::class, 'student_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function level(): BelongsTo
    {
        return $this->belongsTo(Educationlevels::class);
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }
    public function exams(): HasMany
    {
        return $this->hasMany(Exams::class);
    }
    public function TeacherSpecailtyPreference(): HasMany
    {
        return $this->hasMany(TeacherSpecailtyPreference::class);
    }
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
    public function schoolbranches(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class);
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
    public function marks(): HasMany
    {
        return $this->hasMany(Marks::class, 'specialty_id');
    }
    public function studentresit(): HasMany
    {
        return $this->hasMany(Studentresit::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }
}
