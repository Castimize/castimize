<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\ValueWidget;
use Laravel\Nova\Http\Requests\NovaRequest;

class NewOrdersValueWidget extends ValueWidget
{
    public function configure(NovaRequest $request): void
    {
        $this->icon('chart-square-bar');
        $this->title(__('New orders'));
        $this->textColor(dark: 'rgb(139 92 246)', light: 'rgb(91 33 182)');
        $this->backgroundColor(dark: 'rgb(30 41 59)', light: 'rgb(203 213 225)');
    }

    public function value(Filters $filters): mixed
    {
        return $filters->applyToQueryBuilder(
            Order::whereNotNull('paid_at')
                ->whereNull('deleted_at')
                ->removeTestEmailAddresses('email')
        )->count('id');
    }
}
