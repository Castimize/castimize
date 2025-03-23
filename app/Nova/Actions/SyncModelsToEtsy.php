<?php

namespace App\Nova\Actions;

use App\Jobs\Etsy\SyncListings;
use App\Models\Customer;
use App\Services\Etsy\EtsyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
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

            $shopOauth = $shopOwnerAuth->shop_oauth;
            $shopOauth['default_taxonomy_id'] = $fields->taxonomy_id;
            $shopOwnerAuth->shop_oauth = $shopOauth;
            $shopOwnerAuth->save();

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
        $customer = Customer::find(8);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $taxonomyAsSelect = (new EtsyService())->getTaxonomyAsSelect($shopOwnerAuth);

        $options = [];
        foreach ($taxonomyAsSelect as $id => $taxonomy) {
            $fullPathName = '';
            $fullPath = explode(',', $taxonomy['full_path']);
            if (count($fullPath) > 0) {
                foreach ($fullPath as $pathId) {
                    if (!empty($pathId) && $pathId != $id) {
                        $fullPathName .= $taxonomyAsSelect[$pathId]['name'] . ' > ';
                    }
                }
                $fullPathName = substr($fullPathName, 0, -3);
            } else {
                $fullPathName = $taxonomy['name'];
            }
            $options[$id] = [
                'label' => $taxonomy['name'],
                'group' => $fullPathName,
            ];
        }

        return [
            Select::make(__('Etsy taxonomy'), 'taxonomy_id')
                ->options($options)
                ->help(__('Select an Etsy taxonomy. The listing will show in all categories shown'))
                ->displayUsingLabels()
                ->searchable()
                ->required(),
        ];
    }
}
