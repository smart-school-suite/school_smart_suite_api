<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class Examtype extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
      'semester_id',
      'exam_name',
      'semester',
      'status',
      'type',
      'program_name'
    ];


    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'exam_type';

    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
   public function semesters(): BelongsTo {
      return $this->belongsTo(Semester::class, 'semester_id');
   }

   public function exams(): HasMany {
      return $this->hasMany(Exams::class, 'exam_type_id');
   }
}
