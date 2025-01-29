<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grades extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'letter_grade_id',
        'grade_points',
        'exam_id',
        'minimum_score',
        'grade_status',
        'maximum_score',
        'determinant'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'grades';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

    public function exam() : BelongsTo {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function lettergrade(): BelongsTo {
        return $this->belongsTo(LetterGrade::class, 'letter_grade_id');
    }
}
