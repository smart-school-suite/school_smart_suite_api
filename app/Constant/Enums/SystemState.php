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
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
