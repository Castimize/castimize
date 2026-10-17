<?php

namespace App\Traits\Api;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

trait AuthorizesCustomerAccess
{
    /**
     * Authorize access to a customer-scoped API resource.
     *
     * A request authenticated with a customer-scoped token (see
     * Customer::API_TOKEN_ABILITY_SELF_SERVICE) may only access its own
     * customer's data. Any other authenticated principal (e.g. the
     * WordPress backend's staff-level token) falls back to the existing
     * permission gate, unchanged.
     */
    protected function authorizeCustomerAccess(int $customerId, string $staffGate): void
    {
        $user = auth()->user();

        if ($user instanceof Customer) {
            abort_if(
                $user->wp_id !== $customerId || ! $user->tokenCan(Customer::API_TOKEN_ABILITY_SELF_SERVICE),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            return;
        }

        abort_if(Gate::denies($staffGate), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
