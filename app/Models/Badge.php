<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile_icon',
        'desktop_icon',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
    protected $table = 'badges';

     public function assignments()
    {
        return $this->hasMany(BadgeAssignment::class);
    }
}
