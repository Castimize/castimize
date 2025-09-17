<?php

namespace App\Traits\Nova\Metrics;

use App\Models\Order;
use App\Services\Admin\CurrencyService;
use Carbon\CarbonPeriod;
use Coroowicaksono\ChartJsIntegration\LineChart;
use Illuminate\Support\Facades\DB;

trait CustomMetricsCharts
{
    use CustomMetricsQueries;

    public function getRevenueCostsProfitPerDayMetric(): LineChart
    {
        $dateRanges = [];
        $period = CarbonPeriod::create(now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d'));

        foreach ($period as $date) {
            $dateRanges[] = $date->format('Y-m-d');
        }

        $currencyService = new CurrencyService();
        $xAxis = [];
        $revenue = [];
        $costs = [];
        $profit = [];
        $query = DB::table('orders')
            ->join('uploads', 'orders.id', '=', 'uploads.order_id')
            ->join('order_queue', 'orders.id', '=', 'order_queue.order_id')
            ->selectRaw(
                "DATE_FORMAT(orders.created_at,'%Y-%m-%d') as entry_date,
                                   orders.currency_code,
                                   (SUM(uploads.total) / 100) as revenue,
                                   (SUM(order_queue.manufacturer_costs) / 100) as costs,
                                   (SUM(uploads.total - order_queue.manufacturer_costs) / 100) as profit"
            )
            ->whereNotNull('orders.paid_at')
            ->whereNull('orders.deleted_at')
            ->whereRaw("DATE(orders.created_at) in ('" . implode("','", $dateRanges) . "')")
            ->orderBy('entry_date')
            ->groupBy('entry_date', 'orders.currency_code');

        if (request()->range) {
            //$query = $query->whereBetween('created_at', request()->range);
        }
        $query = $this->removeTestEmailAddresses('email', $query);
        $data = $query->get();

        $converted = [];
        foreach ($data as $row) {
            if (! array_key_exists($row->entry_date, $converted)) {
                $converted[$row->entry_date] = [
                    'revenue' => $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->revenue),
                    'costs' => $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->costs),
                    'profit' => $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->profit),
                ];
            } else {
                $converted[$row->entry_date]['revenue'] += $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->revenue);
                $converted[$row->entry_date]['costs'] += $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->costs);
                $converted[$row->entry_date]['profit'] += $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->profit);
            }
        }

        foreach ($dateRanges as $date) {
            if (array_key_exists($date, $converted)) {
                $xAxis[] = $date;
                $revenue[] = $converted[$date]['revenue'];
                $costs[] = $converted[$date]['costs'];
                $profit[] = $converted[$date]['profit'];
            } else {
                $xAxis[] = $date;
                $revenue[] = 0;
                $costs[] = 0;
                $profit[] = 0;
            }
        }

        return (new LineChart())
            ->title(__('Revenue Costs Profit Per Day'))
            ->animations([
                'enabled' => true,
                'easing' => 'easeinout',
            ])
            ->series([
                [
                    'barPercentage' => 0.5,
                    'label' => __('Revenue'),
                    'borderColor' => 'rgb(14 165 233)',
                    'data' => $revenue,
                ], [
                    'barPercentage' => 0.5,
                    'label' => __('Costs'),
                    'borderColor' => 'rgb(139 92 246)',
                    'data' => $costs,
                ], [
                    'barPercentage' => 0.5,
                    'label' => __('Profit'),
                    'borderColor' => 'rgb(34 197 94)',
                    'data' => $profit,
                ],
            ])
            ->options([
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'btnFilter' => true,
                'btnFilterDefault' => 'MTD',
                'btnFilterList' => $this->defaultRanges(),
                'xaxis' => [
                    'categories' => $xAxis,
                ],
            ])
            ->width('full');
    }

    public function getOrdersPerDayMetric(): LineChart
    {
        return (new LineChart())
            ->title(__('Orders Per Day'))
            ->model(Order::class)
            ->animations([
                'enabled' => true,
                'easing' => 'easeinout',
            ])
            ->options([
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'btnFilter' => true,
                'btnFilterDefault' => '30',
                'btnFilterList' => $this->defaultRanges(),
                'uom' => 'day',
                //'sum' => 'order_number',
                'queryFilter' => [
                    [
                        'key' => 'paid_at',
                        'operator' => 'IS NOT NULL',
                    ],
                    [
                        'key' => 'deleted_at',
                        'operator' => 'IS NULL',
                    ],
                ],
            ])
            ->width('full');
    }
}
