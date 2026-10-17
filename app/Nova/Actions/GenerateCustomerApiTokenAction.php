<?php

namespace App\Nova\Actions;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class GenerateCustomerApiTokenAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public const TOKEN_NAME = 'admin-issued';

    public function name()
    {
        return __('Generate API token');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() !== 1) {
            return ActionResponse::danger(__('Please select exactly one customer to generate a token for.'));
        }

        /** @var Customer $customer */
        $customer = $models->first();

        // Regenerating replaces the previous admin-issued token, so an old,
        // possibly-shared key can't keep working once a new one is handed out.
        $customer->tokens()->where('name', self::TOKEN_NAME)->delete();

        $token = $customer->createToken(
            self::TOKEN_NAME,
            [Customer::API_TOKEN_ABILITY_SELF_SERVICE],
        );

        return ActionResponse::message(
            __('Token for :name (copy it now, it won\'t be shown again): :token', [
                'name' => $customer->name,
                'token' => $token->plainTextToken,
            ])
        );
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
