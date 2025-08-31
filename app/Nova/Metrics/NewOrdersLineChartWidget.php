<?php

namespace App\Nova\Metrics;

use App\Traits\Nova\Metrics\CustomMetricsQueries;
use DigitalCreative\ChartJsWidget\Charts\LineChartWidget;
use DigitalCreative\NovaDashboard\Filters;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;

class NewOrdersLineChartWidget extends LineChartWidget
{
    use CustomMetricsQueries;

    public function configure(NovaRequest $request): void
    {
        /**
         * These set the title and the button on the top-right if there are multiple "tabs" on this view
         */
        $this->title(__('New orders per day'));
        //        $this->buttonTitle();
        $this->backgroundColor(dark: 'rgb(30 41 59)', light: 'rgb(203 213 225)');

        $this->padding(top: 30, bottom: 5);

        //        $this->tooltip([]); // https://www.chartjs.org/docs/latest/configuration/tooltip.html#tooltip
        //        $this->scales([]);  // https://www.chartjs.org/docs/latest/axes/#axes
        //        $this->legend([]);  // https://www.chartjs.org/docs/latest/configuration/legend.html#legend
        //        $this->elements();  // https://www.chartjs.org/docs/latest/configuration/elements.html#elements
    }

    public function value(Filters $filters): mixed
    {
        $dateRanges = $this->getDateRanges($filters);

        $labels = [];
        $totals = [];
        $query = DB::table('orders')
            ->selectRaw("DATE_FORMAT(orders.created_at,'%Y-%m-%d') as entry_date,
                                   COUNT(order_number) as total"
            )
            ->whereNotNull('orders.paid_at')
            ->whereNull('orders.deleted_at')
            ->whereRaw("DATE(orders.created_at) in ('".implode("','", $dateRanges)."')")
            ->orderBy('entry_date')
            ->groupBy('entry_date');
        $query = $this->removeTestEmailAddresses('email', $query);

        $query = $this->applyFilters($query, $filters);

        $rows = $query->get();

        $converted = [];
        foreach ($rows as $row) {
            if (! array_key_exists($row->entry_date, $converted)) {
                $converted[$row->entry_date] = [
                    'total' => (int) $row->total,
                ];
            }
        }

        foreach ($dateRanges as $date) {
            $labels[] = $date;
            if (array_key_exists($date, $converted)) {
                $totals[] = (int) $converted[$date]['total'];
            } else {
                $totals[] = 0;
            }
        }

        $datasets = [
            [
                'barPercentage' => 0.5,
                'label' => __('Orders'),
                'borderColor' => 'rgb(14 165 233)',
                'data' => $totals,
            ],
        ];

        return [
            'labels' => collect($labels),
            'datasets' => collect($datasets),
        ];
    }
}
