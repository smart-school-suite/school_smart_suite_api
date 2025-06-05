<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementAuthor extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'authorable_id',
        'authorable_type',
        'announcement_id'
    ];

    public $keyType = 'string';
    public $table = 'announcement_author';
    public $incrementing = false;

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }
    public function authorable(){

        return $this->morphTo();
    }
}
