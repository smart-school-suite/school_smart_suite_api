<?php

namespace App\Models\ResitTimetable;

use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

class ResitTimetable extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'resit_timetable';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_version_id',
        'resit_exam_id',
        'school_branch_id',
        'status',
        'timetable_slots',
        'request_payload',
        'scheduler_input',
        'raw_diagnostics',
        'parsed_diagnostics',
        'raw_suggestions',
        'parsed_suggestions',
    ];

    protected $casts = [
        'timetable_version_id' => 'string',
    ];

    public function getTable()
    {
        return 'resit_timetable';
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }

    public function scopeForVersion(object $query, string $versionId)
    {
        return $query->where('timetable_version_id', $versionId);
    }

    public function scopeForResitExam(object $query, string $resitExamId)
    {
        return $query->where('resit_exam_id', $resitExamId);
    }
}
