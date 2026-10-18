<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use App\Services\Admin\UsersService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class EnsureCustomerApiUserAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function name()
    {
        return __('Ensure API user exists');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return ActionResponse::danger(__('Please select exactly one customer.'));
        }

        /** @var Customer $customer */
        $customer = $models->first();

        $user = app(UsersService::class)->findOrCreateApiUser($customer);

        return ActionResponse::visit("resources/users/{$user->id}");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
