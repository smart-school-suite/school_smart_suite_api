<?php

namespace App\Models\ExamTimetable;

use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

class ExamTimetableRun extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'exam_timetable';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_version_id',
        'exam_id',
        'school_branch_id',
        'status',
        'timetable_slots',
        'unscheduled_courses',
        'request_payload',
        'scheduler_input',
        'raw_diagnostics',
        'parsed_diagnostics',
        'raw_suggestions',
    ];

    protected $casts = [
        'timetable_version_id' => 'string',
    ];

    public function getTable()
    {
        return 'exam_timetable';
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

    public function scopeForExam(object $query, string $examId)
    {
        return $query->where('exam_id', $examId);
    }
}
