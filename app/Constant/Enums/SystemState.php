<?php

namespace App\Constant\Enums;


enum SystemState: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case ONGOING = 'ongoing';
    case ENDED = 'ended';
    case UPCOMING = 'upcoming';
    case FINISHED = 'finished';
    case AVAILABLE = 'available';
    case UNAVAILABLE = 'unavailable';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
