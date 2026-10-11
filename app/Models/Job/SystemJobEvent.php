<?php

namespace App\Models\Job;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class SystemJobEvent extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'system_job_events';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'job_id',
        'school_branch_id',
        'type',
        'stage',
        'message',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }


    public function getTable()
    {
        return 'system_job_events';
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }

    public function scopeForJob(object $query, string $jobId)
    {
        return $query->where('job_id', $jobId);
    }
    public function scopeForSchoolBranch(object $query, string $schoolBranchId)
    {
        return $query->where('school_branch_id', $schoolBranchId);
    }
}
