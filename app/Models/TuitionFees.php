<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuitionFees extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'student_id',
        'school_branch_id',
        'specialty_id',
        'amount_paid',
        'tution_fee_total'
    ];

    protected $casts = [
        'amount_paid' => "float",
        'tuition_fee_total' => "float"
    ];
    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'tuition_fees';

    public function studentFeeSchedule(): HasMany {
         return $this->hasMany(StudentFeeSchedule::class);
    }
    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty(){
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level() {
        return $this->belongsTo(Educationlevels::class , 'level_id');
    }

    public function tuitionFeeTransactions(): HasMany {
         return $this->hasMany(TuitionFeeTransactions::class);
    }

}
