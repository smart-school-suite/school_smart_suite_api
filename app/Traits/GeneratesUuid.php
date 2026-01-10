<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesUuid
{
    protected static function bootGeneratesUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }
}
