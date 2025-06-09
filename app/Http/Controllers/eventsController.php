<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Http\Requests\Event\CreateEventRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Services\CreateEventService;
use Throwable;

class EventsController extends Controller
{
    protected CreateEventService $createEventService;

    public function __construct(CreateEventService $createEventService)
    {
        $this->createEventService = $createEventService;
    }

    public function createSchoolEvent(CreateEventRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $authenticatedUser = $this->getAuthenticatedUser();
            $createSchoolEvent = $this->createEventService->createEvent($request->validated(), $currentSchool, $authenticatedUser);
            return ApiResponseService::success("School Event Created Successfully", $createSchoolEvent, null, 201);
        } catch (Throwable $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }

    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
        ];
    }
}
