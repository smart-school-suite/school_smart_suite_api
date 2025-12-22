<?php

namespace App\Models\Analytics\Finance;

use MongoDB\Laravel\Eloquent\Model;

class FinanceAnalyticEvent extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'financial_events';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'event_type',
        'school_branch_id',
        'amount',
        'payload',
        'occurred_at',
        'version',
        'event_hash'
    ];
    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
    public function getTable()
    {
        return 'financial_events';
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
