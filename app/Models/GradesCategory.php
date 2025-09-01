<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class GradesCategory extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'title',
        'status',
        'exam_type'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'grades_category';

    public function schoolGradesConfig() : HasMany {
         return $this->hasMany(SchoolGradesConfig::class);
    }
    public function examResit(): HasMany {
        return $this->hasMany(ResitExam::class);
    }

}
