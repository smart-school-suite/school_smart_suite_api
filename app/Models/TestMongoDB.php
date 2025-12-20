<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TestMongoDB extends Model
{
    protected $connection = 'mongodb';

    /**
     * Force the MongoDB collection name to 'blog_posts'
     * This overrides any default table inference (including from BaseMongoModel)
     */
    protected $table = 'blog_posts';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'description',
        'time_series',
    ];

    /**
     * Override getTable() to ensure the correct collection is always used,
     * even if a parent class (like BaseMongoModel) tries to change it.
     */
    public function getTable()
    {
        return 'blog_posts';
    }

    /**
     * Optional: Ensure the underlying query builder always uses the correct collection
     */
    public function newEloquentBuilder($query)
    {
        return new \MongoDB\Laravel\Eloquent\Builder($query);
    }

    /**
     * Ensure the model instance always returns the correct table/collection name
     */
    public function qualifyColumn($column)
    {
        return $column;
    }
}
