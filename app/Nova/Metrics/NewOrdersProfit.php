<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use App\Models\Upload;
use App\Services\Admin\CurrencyService;
use App\Traits\Nova\Metrics\CustomMetricsQueries;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;

class NewOrdersProfit extends Value
{
    use CustomMetricsQueries;

    public $icon = 'currency-dollar';

    /**
     * Get the displayable name of the metric
     *
     * @return string
     */
    public function name()
    {
        return __('Profit');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $currencyService = new CurrencyService();
        $query = Order::with(['uploads.orderQueue'])
            ->whereNotNull('paid_at')
            ->whereNull('deleted_at');
        $query = $this->removeTestEmailAddresses('email', $query);

        if ($request->range) {
            $query = $this->addRangeToQuery('created_at', $request->range, $query);
        }
        $orders = $query->get();
        $total = 0;
        foreach ($orders as $order) {
            $uploadTotal = 0;
            foreach ($order->uploads as $upload) {
                $uploadTotal += $currencyService->convertCurrency($upload->currency_code, config('app.currency'), $upload->total);
                if ($upload->orderQueue) {
                    $uploadTotal -= ($upload->orderQueue->manufacturer_costs ?: 0);
                }
            }
            $total += $uploadTotal;
        }

        return $this->result($total)->currency();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return $this->defaultRanges();
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int|null
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'new-orders-profit';
    }
}
