<?php

namespace App\Nova\Actions;

use App\Services\Etsy\EtsyService;
use Etsy\OAuth\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class EtsyAuthorizationUrlAction extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Authorize with Etsy');
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $shop = $models->first();
        $shop->shop_oauth = [
                'client_id' => $fields->oauthKey,
                'client_secret' => Crypt::encryptString($fields->oathSecret),
            ];

        $url = (new EtsyService())->getAuthorizationUrl($shop);

        return ActionResponse::openInNewTab($url);
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Oath key'), 'oauthKey')
                ->default(config('services.shops.etsy.client_id')),

            Text::make(__('Oath secret'), 'oauthSecret')
                ->default(config('services.shops.etsy.client_secret')),
        ];
    }
}
