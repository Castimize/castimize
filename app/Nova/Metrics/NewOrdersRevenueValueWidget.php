<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use App\Services\Admin\CurrencyService;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\ValueWidget;
use Laravel\Nova\Http\Requests\NovaRequest;

class NewOrdersRevenueValueWidget extends ValueWidget
{
    public function configure(NovaRequest $request): void
    {
        $this->icon('currency-dollar');
        $this->title(__('Revenue'));
        $this->textColor(dark: 'rgb(132 204 22)', light: 'rgb(77 124 15)');
        $this->backgroundColor(dark: 'rgb(30 41 59)', light: 'rgb(203 213 225)');
    }

    public function value(Filters $filters): mixed
    {
        $currencyService = new CurrencyService();
        $orders = $filters->applyToQueryBuilder(
            Order::with(['uploads'])
                ->whereNotNull('paid_at')
                ->whereNull('deleted_at')
                ->removeTestEmailAddresses('email')
        )->get();

        $total = 0;
        foreach ($orders as $order) {
            $uploadTotal = 0;
            foreach ($order->uploads as $upload) {
                $uploadTotal += $currencyService->convertCurrency($upload->currency_code, config('app.currency'), $upload->total);
            }
            $total += $uploadTotal;
        }

        return currencyFormatter((float)$total);
    }
}
