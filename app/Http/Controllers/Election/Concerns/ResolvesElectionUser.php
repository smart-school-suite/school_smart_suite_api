<?php

namespace App\Http\Controllers\Election\Concerns;

use App\Exceptions\AppException;
use App\Models\Schooladmin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ResolvesElectionUser
{
    protected function currentSchool(Request $request): object
    {
        return $request->attributes->get('currentSchool');
    }

    protected function authUser(): Model
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user instanceof Model) {
                return $user;
            }
        }
        $user = request()->user();
        if ($user instanceof Model) {
            return $user;
        }
        throw new AppException("Unauthenticated", 401, "Unauthenticated", "You must be signed in to perform this action.", null);
    }

    protected function adminUser(): Schooladmin
    {
        $user = $this->authUser();
        if (!$user instanceof Schooladmin) {
            throw new AppException("Not allowed", 403, "Administrators Only", "Only school administrators can perform this action.", null);
        }
        return $user;
    }

    protected function ids(Request $request, string $listKey, string $itemKey): array
    {
        return collect($request->input($listKey, []))->pluck($itemKey)->filter()->values()->all();
    }
}
