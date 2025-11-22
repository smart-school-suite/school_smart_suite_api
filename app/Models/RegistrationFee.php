<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'amount',
        'status',
        'student_id',
        'school_branch_id',
        'specialty_id',
        'level_id'
    ];

    protected $cast = [
        'amount' => 'decimal:2'
    ];

    public $incrementing = 'false';
    public $table = 'registration_fees';
    public $keyType = 'string';

    public function registrationFeeTransactions(): HasMany
    {
        return $this->hasMany(RegistrationFeeTransactions::class, 'registrationfee_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level()
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
}
