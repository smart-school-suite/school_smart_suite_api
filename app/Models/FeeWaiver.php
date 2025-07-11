<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesUuid;
class FeeWaiver extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
         'start_date',
         'end_date',
         'description',
         'status',
         'school_branch_id',
         'specialty_id',
         'level_id',
         'student_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];


    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level()
    {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

}
