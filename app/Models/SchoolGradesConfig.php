<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolGradesConfig extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'isgrades_configured',
        'max_score',
        'grades_category_id'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'school_grades_config';

    public function gradesCategory(): BelongsTo {
         return $this->belongsTo(GradesCategory::class, 'grades_category_id');
    }
}
