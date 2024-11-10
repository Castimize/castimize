<?php

namespace App\Nova\Metrics;

use App\Models\Order;
use App\Nova\Filters\RangesFilter;
use App\Services\Admin\CurrencyService;
use App\Traits\Nova\Metrics\CustomMetricsQueries;
use Carbon\CarbonPeriod;
use DigitalCreative\ChartJsWidget\Charts\LineChartWidget;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\ValueWidget\ValueWidget;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;

class RevenueCostsProfitLineChartWidget extends LineChartWidget
{
    use CustomMetricsQueries;

    public function configure(NovaRequest $request): void
    {
        /**
         * These set the title and the button on the top-right if there are multiple "tabs" on this view
         */
        $this->title(__('Revenue Costs Profit'));
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
        $dateRanges = [];
        $period = CarbonPeriod::create(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'));

        foreach ($period as $date) {
            $dateRanges[] = $date->format('Y-m-d');
        }

        $currencyService = new CurrencyService();
        $labels = [];
        $revenue = [];
        $costs = [];
        $profit = [];
        $query = DB::table('orders')
            ->join('uploads', 'orders.id', '=', 'uploads.order_id')
            ->join('order_queue', 'orders.id', '=', 'order_queue.order_id')
            ->selectRaw("DATE_FORMAT(orders.created_at,'%Y-%m-%d') as entry_date,
                                   orders.currency_code,
                                   (SUM(uploads.total) / 100) as revenue,
                                   (SUM(order_queue.manufacturer_costs) / 100) as costs"
            )
            ->whereNotNull('orders.paid_at')
            ->whereNull('orders.deleted_at')
            ->whereRaw("DATE(orders.created_at) in ('" . implode("','", $dateRanges) . "')")
            ->orderBy('entry_date')
            ->groupBy('entry_date', 'orders.currency_code');
        $query = $this->removeTestEmailAddresses('email', $query);

        $query = $this->applyFilters($query, $filters);

        $rows = $query->get();

        $converted = [];
        foreach ($rows as $row) {
            $rev = $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->revenue);
            $cost = $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->costs);
            $prof = $rev - $cost;
            if (!array_key_exists($row->entry_date, $converted)) {
                $converted[$row->entry_date] = [
                    'revenue' => $rev,
                    'costs' => $cost,
                    'profit' => $prof,
                ];
            } else {
                $converted[$row->entry_date]['revenue'] += $rev;
                $converted[$row->entry_date]['costs'] += $cost;
                $converted[$row->entry_date]['profit'] += $prof;
            }
        }

        foreach ($dateRanges as $date) {
            $labels[] = $date;
            if (array_key_exists($date, $converted)) {
                $revenue[] = $converted[$date]['revenue'];
                $costs[] = $converted[$date]['costs'];
                $profit[] = $converted[$date]['profit'];
            } else {
                $revenue[] = 0;
                $costs[] = 0;
                $profit[] = 0;
            }
        }

        $datasets = [
            [
                'barPercentage' => 0.5,
                'label' => __('Revenue'),
                'borderColor' => 'rgb(14 165 233)',
                'data' => $revenue,
            ],
            [
                'barPercentage' => 0.5,
                'label' => __('Costs'),
                'borderColor' => 'rgb(139 92 246)',
                'data' => $costs,
            ],
            [
                'barPercentage' => 0.5,
                'label' => __('Profit'),
                'borderColor' => 'rgb(34 197 94)',
                'data' => $profit,
            ],
        ];

        return [
            'labels' => collect($labels),
            'datasets' => collect($datasets),
        ];
    }
}
