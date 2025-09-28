<?php

namespace App\Nova\Actions;

use App\Services\Admin\OrdersService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;

class OrderManualRefundAction extends Action
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
        return __('Manual refund');
    }

    public function __construct(
        public $model = null,
    ) {
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $ordersService = new OrdersService();

        foreach ($models as $model) {
            $ordersService->handleManualRefund($model, $fields->refund_amount);
        }

        return ActionResponse::message(__('Order has been manual refunded.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $max = $this->model->total - ($this->model->total_refund ?? 0);
        return [
            Currency::make(__('Refund amount'), 'refund_amount')
                ->help(__('Max refund amount is :max', [
                    'max' => currencyFormatter((float) $max, $this->model->currency_code ?? config('app.currency')),
                ]))
                ->min(0)
                ->max($max)
                ->currency($this->model->currency_code)
                ->step(0.01)
                ->locale(config('app.format_locale')),
        ];
    }
}
