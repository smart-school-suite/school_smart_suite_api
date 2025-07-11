<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class LetterGrade extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'letter_grade',
        'status'
    ];

    public $incrementing = 'false';
    public $table = 'letter_grade';
    public $keyType = 'string';

    public function grades(): HasMany {
        return $this->hasMany(Grades::class, 'letter_grade_id');
    }
    public function exams(): HasMany {
        return $this->hasMany(Exams::class, 'letter_grade_id');
    }
}
