<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationFee extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'amount',
        'status',
        'student_id',
        'school_branch_id',
        'specialty_id',
        'school_academic_year_id',
    ];

    protected $casts = [
        'amount' => 'float'
    ];

    public $incrementing = 'false';
    public $table = 'registration_fees';
    public $keyType = 'string';

    public function registrationFeeTransactions(): HasMany
    {
        return $this->hasMany(RegistrationFeeTransactions::class, 'registrationfee_id');
    }
    /** The academic year the fee belongs to: the first one the student is billed in for the specialty. */
    public function schoolAcademicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear\SchoolAcademicYear::class, 'school_academic_year_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

}
