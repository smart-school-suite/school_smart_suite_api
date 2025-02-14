<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherSpecailtyPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'teacher_id',
        'specialty_id'
    ];

    public $keyType = 'string';
    public $table = 'teacher_specailty_preference';
    public $incrementing = 'false';

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function specailty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 25);
         });

    }
}
