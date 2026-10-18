<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use App\Services\Admin\CustomersService;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\UsersService;
use App\Traits\Api\AuthorizesCustomerAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CustomersApiController extends ApiController
{
    use AuthorizesCustomerAccess;

    public function __construct(
        private CustomersService $customersService,
        private UsersService $usersService,
    ) {}

    public function show(Customer $customer): CustomerResource
    {
        $this->authorizeCustomerAccess($customer->wp_id, 'viewCustomer');

        $response = new CustomerResource($customer);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    /**
     * Issue a short-lived API token for use in WordPress' "My account" pages,
     * so the browser never sees the shared backend token. The token belongs
     * to this customer's own User account (role: customer), so it only works
     * for this one customer's data once past the ownership checks on each
     * customer-facing endpoint.
     */
    public function issueApiToken(int $customerId): JsonResponse
    {
        // This mints a fresh token for the given customer, so it must stay
        // staff-only: a customer-scoped token must never be able to use it
        // to impersonate another customer, even though the 'customer' role
        // is itself granted 'viewCustomer' (so customers can view their own
        // record elsewhere).
        $caller = auth()->user();
        abort_if($caller instanceof User && $caller->hasRole('customer'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $customer = Customer::where('wp_id', $customerId)->first();
        if (! $customer) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $user = $this->usersService->findOrCreateApiUser($customer);

        $user->tokens()->where('name', 'wordpress-self-service')->delete();

        $token = $user->createToken('wordpress-self-service', ['*'], now()->addDay());

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }

    public function showCustomerWp(ShowCustomerWpRequest $request): CustomerResource
    {
        $customer = Customer::with(['orders'])
            ->withCount('orders')
            ->where('wp_id', $request->wp_id)->first();
        if ($customer === null) {
            LogRequestService::addResponse($request, [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $response = new CustomerResource($customer);
        LogRequestService::addResponse($request, $response);

        return $response;
    }

    public function storeCustomerWp(Request $request): JsonResponse
    {
        $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($request->id);
        $customer = $this->customersService->storeCustomerFromWpCustomer($wpCustomer);

        $response = new CustomerResource($customer);
        LogRequestService::addResponse($request, $response);

        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function updateCustomerWp(Request $request): JsonResponse
    {
        return response()->json($request->all());
    }

    public function deleteCustomerWp(Request $request): Response
    {
        $customer = Customer::where('wp_id', $request->id)->first();
        if ($customer === null) {
            LogRequestService::addResponse($request, [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $customer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
