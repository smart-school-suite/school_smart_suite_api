<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class AdditionalFees extends Model
{
    use HasFactory;

    protected $fillable = [
       'title',
       'reason',
       'amount',
       'status',
       'school_branch_id',
       'specialty_id',
       'level_id',
       'student_id'
    ];

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
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
