<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewOrders;
use App\Nova\Metrics\NewOrdersProfit;
use App\Nova\Metrics\NewOrdersRevenue;
use App\Traits\Nova\Metrics\CustomMetricsCharts;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

class ManufacturerDashboard extends Dashboard
{
    use CustomMetricsCharts;

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new Help,
        ];
    }

    /**
     * Get the URI key of the dashboard.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'main';
    }
}
