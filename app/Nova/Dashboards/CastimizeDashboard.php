<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\NewOrders;
use App\Nova\Metrics\NewOrdersProfit;
use App\Nova\Metrics\NewOrdersRevenue;
use App\Traits\Nova\Metrics\CustomMetricsCharts;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;

class CastimizeDashboard extends Dashboard
{
    use CustomMetricsCharts;

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        if (auth()->user()->isSuperAdmin()) {
            return [
            NewOrders::make()->defaultRange('MTD'),
            NewOrdersRevenue::make()->defaultRange('MTD'),
            NewOrdersProfit::make()->defaultRange('MTD'),
            $this->getRevenueCostsProfitPerDayMetric(),
            ];
        }
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