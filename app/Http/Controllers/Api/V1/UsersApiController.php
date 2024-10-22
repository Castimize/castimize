<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\UsersService;
use Exception;
use Illuminate\Http\JsonResponse;
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

        $response = new UserResource(auth()->user());
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    /**
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function storeUserWp(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = (new UsersService())->storeUserFromApi($request);
        } catch (Exception $e) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, '422 Unable to add user');
        }

        $response = new UserResource($user);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param DeleteUserRequest $request
     * @return Response
     */
    public function deleteUserWp(DeleteUserRequest $request): Response
    {
        $user = User::where('wp_id', $request->wp_id)->first();
        if ($user === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $user->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
