<?php

namespace App\Models\SemesterTimetable;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SemesterTimetablePrompt extends Model
{
    use GeneratesUuid, HasFactory;
    protected $fillable = [
        'user_prompt',
        'scheduler_input',
        'scheduler_output',
        'ai_output',
        'draft_id',
        'result_version_id',
        'base_version_id',
        'school_branch_id',
        'school_semester_id'
    ];

    public $incrementing = false;
    public $table = 'timetable_prompts';
    public $keyType = 'string';

    protected $casts = [
         'ai_output' => "json",
         'scheduler_input' => "json",
         'scheduler_output' => 'json'
    ];
    public function resultVersion(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'result_version_id');
    }
    public function baseVersion(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableVersion::class, 'base_version_id');
    }
    public function draft(): BelongsTo
    {
        return $this->belongsTo(SemesterTimetableDraft::class, 'draft_id');
    }

}
