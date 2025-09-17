<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Models\Upload;
use App\Services\Admin\OrderQueuesService;
use App\Services\Admin\OrdersService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoCanceledStatusAction extends Action
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
        return __('Cancel and refund');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $orderQueuesService = new OrderQueuesService();
        $ordersService = new OrdersService();

        foreach ($models as $model) {
            $hasAlreadyCanceled = [];
            /** @var $model OrderQueue */
            if ($model->getLastStatus()->slug === 'canceled') {
                $hasAlreadyCanceled[] = $model->id;
            }

            if (count($hasAlreadyCanceled) > 0) {
                return ActionResponse::danger(
                    __('You selected PO\'s :pos which cannot be changed anymore, because they already are canceled', [
                        'pos' => implode(', ', $hasAlreadyCanceled),
                    ])
                );
            }
        }
        $toRefundOrders = [];
        foreach ($models as $model) {
            $orderQueue = $model;
            if ($model instanceof Upload) {
                $orderQueue = $model->orderQueue;
            }
            $orderQueuesService->setStatus($orderQueue, 'canceled');
            if ($fields->also_refund && ! $orderQueue->order->has_manual_refund) {
                if (! array_key_exists($orderQueue->order_id, $toRefundOrders)) {
                    $toRefundOrders[$orderQueue->order_id] = [
                        'order' => $orderQueue->order,
                        'orderQueues' => [],
                    ];
                }
                $toRefundOrders[$orderQueue->order_id]['orderQueues'][] = $orderQueue;
            }
        }

        foreach ($toRefundOrders as $order) {
            $ordersService->handleRefund($order['order'], $order['orderQueues']);
        }

        return ActionResponse::message(__('Selected PO\'s has been canceled and refunded if possible'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Boolean::make(__('Also refund'), 'also_refund')
                ->default(false),
        ];
    }
}
