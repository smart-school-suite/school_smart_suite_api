<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class Educationlevels extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'level',
        'status'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'levels';


    public function specialtyHall(): HasMany {
         return $this->hasMany(SpecialtyHall::class);
    }
    public function examCandidate(): HasMany {
         return $this->hasMany(AccessedStudent::class);
    }
     public function instructorAvailability(): HasMany {
        return $this->hasMany(InstructorAvailability::class);
    }
    public function feeSchedule(): HasMany {
        return $this->hasMany(FeeSchedule::class);
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

    public function studentFeeSchedule(): HasMany {
        return $this->hasMany(StudentFeeSchedule::class);
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
