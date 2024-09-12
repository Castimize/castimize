<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class UsersApiController extends ApiController
{
    /**
     * @return UserResource
     */
    public function show(): UserResource
    {
        abort_if(Gate::denies('viewUser'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UserResource(auth()->user());
    }
}
