<?php

namespace App\Traits\Api;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

trait AuthorizesCustomerAccess
{
    /**
     * Authorize access to a customer-scoped API resource.
     *
     * A customer's own API token belongs to a regular User account (with the
     * 'customer' role, linked via wp_id) — not a separate identity type —
     * so it goes through the normal Gate/permission system without special
     * casing, and additionally must match the requested customer_id: the
     * 'customer' role alone does not scope access to only that user's own
     * data, so every customer-facing endpoint still needs this ownership
     * check. Any other authenticated principal (e.g. the WordPress backend's
     * staff-level token) falls back to the existing permission gate,
     * unchanged. Pass $staffGate as null for endpoints that never had a
     * permission gate to begin with — that preserves today's behavior for
     * the staff/master token exactly (no new requirement introduced) while
     * still adding the ownership check for customer-role tokens.
     */
    protected function authorizeCustomerAccess(int $customerId, ?string $staffGate): void
    {
        $user = auth()->user();

        if ($user instanceof User && $user->hasRole('customer')) {
            abort_if($user->wp_id !== $customerId, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($staffGate !== null) {
            abort_if(Gate::denies($staffGate), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }
    }
}
