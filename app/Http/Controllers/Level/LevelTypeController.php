<?php

namespace App\Http\Controllers\Level;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Level\LevelTypeService;
use Illuminate\Http\Request;

class LevelTypeController extends Controller
{
    protected LevelTypeService $levelTypeService;
    public function __construct(LevelTypeService $levelTypeService)
    {
        $this->levelTypeService = $levelTypeService;
    }

    public function getLevelTypes(Request $request)
    {
        $levelTypes = $this->levelTypeService->getLevelTypes();
        return ApiResponseService::success("Level Types Fetched Successfully", $levelTypes, null, 200);
    }
}
