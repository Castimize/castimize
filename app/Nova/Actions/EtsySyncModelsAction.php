<?php

namespace App\Nova\Actions;

use App\Jobs\Etsy\SyncListings;
use App\Models\Customer;
use App\Services\Etsy\EtsyService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class EtsySyncModelsAction extends Action
{
    use InteractsWithQueue, Queueable;

    public function name()
    {
        return __('Sync models to Etsy');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $shop) {
            if ($shop->shop !== 'etsy' || ! $shop->active) {
                continue;
            }

            $shopOauth = $shop->shop_oauth;
            $shopOauth['default_taxonomy_id'] = $fields->taxonomy_id;
            $shop->shop_oauth = $shopOauth;
            $shop->save();

            SyncListings::dispatch($shop);
        }

        return ActionResponse::message(__('Selected shop listings are being synced'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $customer = Customer::find(8);
        $shop = $customer->shopOwner->shops->first();
        $taxonomyAsSelect = (new EtsyService)->getTaxonomyAsSelect($shop);

        $options = [];
        foreach ($taxonomyAsSelect as $id => $taxonomy) {
            $fullPathName = '';
            $fullPath = explode(',', $taxonomy['full_path']);
            if (count($fullPath) > 0) {
                foreach ($fullPath as $pathId) {
                    if (! empty($pathId) && $pathId != $id) {
                        $fullPathName .= $taxonomyAsSelect[$pathId]['name'].' > ';
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
