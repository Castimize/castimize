<?php

namespace App\Nova\Actions;

use App\Jobs\CheckOrderAllRejected;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class AcceptRejectionAction extends Action implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Accept rejection');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $orders = [];
        foreach ($models as $model) {
            $model->note_castimize = $fields->note_castimize;
            $model->approved_at = now();
            $model->save();
            if (! array_key_exists($model->order_id, $orders)) {
                $orders[$model->order_id] = $model->order;
            }
        }

        foreach ($orders as $order) {
            $cacheKey = sprintf('create-order-all-rejected-job-%s', $order->id);

            if (! Cache::has($cacheKey)) {
                CheckOrderAllRejected::dispatch($order)->delay(now()->addHours(2));
                Cache::forever($cacheKey, 1);
            }
        }

        return ActionResponse::message(__('Rejection successfully accepted'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Textarea::make(__('Note Castimize'), 'note_castimize')->default(function ($request) {
                return $request->note_manufacturer;
            }),
        ];
    }
}
