<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesUuid
{
    /**
     * Boot the trait.
     * This method will be called automatically by Laravel's Model boot method.
     *
     * @return void
     */
    protected static function bootGeneratesUuid()
    {
        static::creating(function ($model) {
            // Only generate a UUID if the primary key is empty
            // This allows for manually setting IDs if needed.
            if (empty($model->{$model->getKeyName()})) {
                // Generate a full UUID (type 4, random)
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

}
