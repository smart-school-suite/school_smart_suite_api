<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Educationlevels extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'status'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'education_levels';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
        });
    }

    public function electionParticipants(): HasMany
    {
        return $this->hasMany(ElectionParticipants::class);
    }
    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    public function resitResults(): HasMany
    {
        return $this->hasMany(ResitResults::class);
    }
    public function studentGradDates(): HasMany
    {
        return $this->hasMany(StudentBatchGradeDates::class);
    }

    public function resitmarks(): HasMany
    {
        return $this->hasMany(ResitMarks::class, 'level_id');
    }
    public function feeWaiver(): HasMany
    {
        return $this->hasMany(FeeWaiver::class, 'level_id');
    }
    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResults::class, 'level_id');
    }
    public function additionalFees(): HasMany
    {
        return $this->hasMany(AdditionalFees::class, 'level_id');
    }
    public function feePaymentSchedule(): HasMany
    {
        return $this->hasMany(FeePaymentSchedule::class, 'specialty_id');
    }
    public function specialty(): HasMany
    {
        return $this->hasMany(Specialty::class);
    }
    public function registrationFee(): HasMany
    {
        return $this->hasMany(RegistrationFee::class, 'level_id');
    }
    public function mark(): HasMany
    {
        return $this->hasMany(Marks::class, 'level_id');
    }

    public function tuitionFees(): HasMany
    {
        return $this->hasMany(TuitionFees::class);
    }
    public function student(): HasMany
    {
        return $this->hasMany(Student::class, 'level_id');
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Courses::class);
    }
    public function timetable(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }
    public function exam(): HasMany
    {
        return $this->hasMany(Exams::class);
    }
    public function studentresit(): HasMany
    {
        return $this->hasMany(Studentresit::class);
    }
}
