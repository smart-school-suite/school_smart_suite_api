<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class TuitionFees extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_branch_id',
        'specialty_id',
        'level_id',
        'amount_paid',
        'amount_left',
        'tution_fee_total',
        'status'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'tuition_fees';

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function specialty(){
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function level() {
        return $this->belongsTo(Educationlevels::class , 'level_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 30);
         });

    }
}
