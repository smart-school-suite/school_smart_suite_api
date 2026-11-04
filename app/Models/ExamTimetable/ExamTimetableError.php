<?php

namespace App\Models\ExamTimetable;

use MongoDB\Laravel\Eloquent\Builder;
use MongoDB\Laravel\Eloquent\Model;

class ExamTimetableError extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'exam_timetable_error';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_version_id',
        'exam_id',
        'school_branch_id',
        'id',
        'status',
        'request_payload',
        'errors',
    ];

    protected $casts = [
        'timetable_version_id' => 'string',
    ];

    public function getTable()
    {
        return 'exam_timetable_error';
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
