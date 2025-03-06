<?php

namespace App\Nova\Actions;

use App\Jobs\Etsy\SyncListings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class SyncModelsToEtsy extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Sync models to Etsy');
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
        foreach ($models as $shopOwnerAuth) {
            if ($shopOwnerAuth->shop !== 'etsy' || ! $shopOwnerAuth->active) {
                continue;
            }
            SyncListings::dispatch($shopOwnerAuth);
        }

        return ActionResponse::message(__('Selected shop listings are being synced'));
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
