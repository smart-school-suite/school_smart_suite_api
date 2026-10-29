<?php

namespace App\Services\Level;

use App\Models\LevelType;

class LevelTypeService
{
    public function getLevelTypes()
    {
        return LevelType::all();
    }
}
