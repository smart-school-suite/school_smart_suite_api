<?php

namespace App\Models\SchoolEvent;

use App\Models\SchoolEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolEventAuthor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'actorable_id',
        'actorable_type',
        'school_branch_id',
        'school_event_'
    ];
}
