<?php

namespace App\Http\Controllers\Ably;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ably\AblyService;

class AblyController extends Controller
{
    protected AblyService $ablyService;

    public function __construct(AblyService $ablyService)
    {
        $this->ablyService = $ablyService;
    }

    public function getAuthAblyToken(Request $request)
    {
        $authUser = $this->resolveUser();
        $token = $this->ablyService->getAblyToken($authUser);
        return response()->json($token);
    }

    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
