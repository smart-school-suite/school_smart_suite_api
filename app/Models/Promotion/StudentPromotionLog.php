<?php

namespace App\Models\Promotion;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class StudentPromotionLog extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'student_promotion_run_logs';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'promotion_run_id',
        'student_id',
        'school_branch_id',
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
        return 'student_promotion_run_logs';
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }
}
