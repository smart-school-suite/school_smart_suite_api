<?php

namespace App\Models\Analytics;

use MongoDB\Laravel\Eloquent\Model;

class OperationalAnalyticSnapshot extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'operational_snapshots';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'snapshot_type',
        'school_branch_id',
        'payload',
        'snapshot_at'
    ];
    protected $casts = [
        'payload' => 'array',
        'snapshot_at' => 'datetime',
    ];
    public function getTable()
    {
        return 'operational_snapshots';
    }

    public function newEloquentBuilder($query)
    {
        return new \MongoDB\Laravel\Eloquent\Builder($query);
    }
    public function qualifyColumn($column)
    {
        return $column;
    }
}
