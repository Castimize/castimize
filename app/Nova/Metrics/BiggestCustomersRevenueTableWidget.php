<?php

namespace App\Nova\Metrics;

use App\Services\Admin\CurrencyService;
use App\Traits\Nova\Metrics\CustomMetricsQueries;
use DigitalCreative\NovaDashboard\Filters;
use DigitalCreative\TableWidget\TableWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class BiggestCustomersRevenueTableWidget extends TableWidget
{
    use CustomMetricsQueries;

    public function fields(): array
    {
        return [
            Text::make(__('Customer'), 'name'),
            Number::make(__('# Orders'), 'orders'),
            Text::make(__('Revenue'), 'revenue'),
            Text::make(__('Bruto margin'), 'bruto_margin'),
        ];
    }

    public function value(Filters $filters): Collection
    {
        $currencyService = new CurrencyService();

        $query = DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->join('uploads', 'orders.id', '=', 'uploads.order_id')
            ->join('order_queue', 'orders.id', '=', 'order_queue.order_id')
            ->selectRaw("orders.customer_id,
                                   CONCAT(orders.first_name, ' ', orders.last_name) as name,
                                   COUNT(DISTINCT orders.order_number) as orders,
                                   orders.currency_code,
                                   (SUM(uploads.total) / 100) as revenue,
                                   (SUM(order_queue.manufacturer_costs) / 100) as costs"
            )
            ->whereNotNull('orders.paid_at')
            ->whereNull('orders.deleted_at')
            ->orderByDesc('name')
            ->groupBy('orders.customer_id', 'orders.currency_code');
        $query = $this->removeTestEmailAddresses('orders.email', $query);

        $query = $this->applyFilters($query, $filters);

        $rows = $query->get();

        $data = [];

        foreach ($rows as $row) {
            $rev = $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->revenue);
            $cost = $currencyService->convertCurrency($row->currency_code, config('app.currency'), $row->costs);
            $prof = $rev - $cost;
            if (!array_key_exists($row->customer_id, $data)) {
                $data[$row->customer_id] = [
                    'name' => $row->name,
                    'orders' => $row->orders,
                    'revenue' => $rev,
                    'bruto_margin' => $prof,
                ];
            } else {
                $data[$row->customer_id]['orders'] += $row->orders;
                $data[$row->customer_id]['revenue'] += $rev;
                $data[$row->customer_id]['bruto_margin'] += $prof;
            }
        }
        usort($data, function ($a, $b) {
            return $a['revenue'] < $b['revenue'];
        });
        for ($i = 0, $iMax = count($data); $iMax > $i; $i++) {
            $data[$i]['revenue'] = currencyFormatter($data[$i]['revenue']);
            $data[$i]['bruto_margin'] = currencyFormatter($data[$i]['bruto_margin']);
        }

        if (count($data) === 0) {
            $data[] = [
                'name' => '-',
                'orders' => 0,
                'revenue' => currencyFormatter(0.00),
                'bruto_margin' => currencyFormatter(0.00),
            ];
        }

        return collect($data);
    }
}
