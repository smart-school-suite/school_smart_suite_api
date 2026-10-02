<?php

namespace App\Models\Announcement;

use App\Models\Announcement as AnnouncementModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AnnouncementRecipient extends Model
{
    use HasUuids;
    protected $fillable = [
        'school_branch_id',
        'recipient_id',
        'recipient_type',
        'announcement_id',
        'seen_at'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "announcement_recipients";

    public function recipient(): MorphTo
    {
        return $this->morphTo(
            name: 'recipient',
            type: 'recipient_type',
            id: 'recipient_id'
        );
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(AnnouncementModel::class, 'announcement_id');
    }
}
