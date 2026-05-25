<?php

namespace App\Models\ExamTimetable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invigilator extends Model
{
    use HasUuids;

    protected $fillable = [
        'invigilatable_type',
        'invigilatable_id',
        'school_branch_id',
    ];

    public $table = "invigilators";
    public $incrementing = false;
    public $keyType = 'string';

    public function invigilatable()
    {
        return $this->morphTo();
    }

    public function examInvigilator(): HasMany
    {
        return $this->hasMany(ExamInvigilator::class);
    }

    public function examSessionInvigilator(): HasMany
    {
        return $this->hasMany(ExamSessionInvigilator::class);
    }
}
