<?php

namespace App\Nova\Dashboards;

use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Metrics\BiggestCustomersRevenueTableWidget;
use App\Nova\Metrics\BiggestManufacturersRevenueTableWidget;
use App\Nova\Metrics\BiggestMaterialsRevenueTableWidget;
use App\Nova\Metrics\NewOrdersLineChartWidget;
use App\Nova\Metrics\NewOrdersProfitValueWidget;
use App\Nova\Metrics\NewOrdersRevenueValueWidget;
use App\Nova\Metrics\NewOrdersValueWidget;
use App\Nova\Metrics\RevenueCostsProfitLineChartWidget;
use App\Traits\Nova\Metrics\CustomMetricsCharts;
use DigitalCreative\NovaDashboard\Card\NovaDashboard;
use DigitalCreative\NovaDashboard\Card\View;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Dashboards\Main as Dashboard;
use Rpj\Daterangepicker\DateHelper;

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
//        if (auth()->user()->isSuperAdmin()) {
            return [
                NovaDashboard::make()
                    ->addView(__('Dashboard'), function (View $view) {
                        return $view
                            ->icon('window')
                            ->addWidgets([
//                                NewOrdersValueWidget::make()
//                                    ->width(2)
//                                    ->position(x: 0, y: 0),
//                                NewOrdersRevenueValueWidget::make()
//                                    ->width(2)
//                                    ->position(x: 2, y: 0),
//                                NewOrdersProfitValueWidget::make()
//                                    ->width(2)
//                                    ->position(x: 4, y: 0),
//                                RevenueCostsProfitLineChartWidget::make()
//                                    ->width(12)
//                                    ->height(2)
//                                    ->position(x: 0, y: 1),
//                                NewOrdersLineChartWidget::make()
//                                    ->width(12)
//                                    ->height(2)
//                                    ->position(x: 0, y: 4),
//                                BiggestMaterialsRevenueTableWidget::make()
//                                    ->title(__('Biggest materials revenue'))
//                                    ->width(4)
//                                    ->height(3)
//                                    ->position(x: 0, y: 6),
//                                BiggestCustomersRevenueTableWidget::make()
//                                    ->title(__('Biggest customers revenue'))
//                                    ->width(4)
//                                    ->height(3)
//                                    ->position(x: 4, y: 6),
//                                BiggestManufacturersRevenueTableWidget::make()
//                                    ->title(__('Biggest manufacturers revenue'))
//                                    ->width(4)
//                                    ->height(3)
//                                    ->position(x: 8, y: 6),
                            ])
                            ->addFilters([
                                (new OrderDateDaterangepickerFilter(DateHelper::LAST_30_DAYS)),
                            ]);
                    }),
            ];
//        }
//        return [
//            Help::make(),
//        ];
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
