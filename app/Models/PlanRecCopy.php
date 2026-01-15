<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanRecCopy extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'title',
        'description',
        'cta_text',
        'plan_rec_cond_id'
    ];

    public $table = "plan_rec_copies";
    public $incrementing = false;
    public $keyType = 'string';

    public function planRecCondition(): BelongsTo
    {
        return $this->belongsTo(PlanRecCondition::class, 'plan_rec_cond_id');
    }
}
