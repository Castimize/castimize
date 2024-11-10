<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use App\Services\Admin\CurrencyService;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\ValueWidget;
use Laravel\Nova\Http\Requests\NovaRequest;

class NewOrdersProfitValueWidget extends ValueWidget
{
    public function configure(NovaRequest $request): void
    {
        $this->icon('currency-dollar');
        $this->title(__('Profit'));
        $this->textColor(dark: 'rgb(16 185 129)', light: 'rgb(16 185 129)');
        $this->backgroundColor(dark: 'rgb(30 41 59)', light: 'rgb(203 213 225)');
    }

    public function value(Filters $filters): mixed
    {
        $currencyService = new CurrencyService();
        $orders = $filters->applyToQueryBuilder(
            Order::with(['uploads.orderQueue'])
                ->whereNotNull('paid_at')
                ->whereNull('deleted_at')
                ->removeTestEmailAddresses('email')
        )->get();

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

        return currencyFormatter((float)$total);
    }
}
