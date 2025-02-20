<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeWaiver extends Model
{
    use HasFactory;

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

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 30);
         });

    }
}
