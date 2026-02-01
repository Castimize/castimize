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

class BiggestManufacturersRevenueTableWidget extends TableWidget
{
    use CustomMetricsQueries;

    public function fields(): array
    {
        return [
            Text::make(__('Manufacturer'), 'name'),
            Number::make(__('# Orders'), 'orders'),
            Text::make(__('Revenue'), 'revenue'),
            Text::make(__('Bruto margin'), 'bruto_margin'),
        ];
    }

    public function value(Filters $filters): Collection
    {
        $currencyService = new CurrencyService;

        $query = DB::table('orders')
            ->join('order_queue', 'orders.id', '=', 'order_queue.order_id')
            ->join('manufacturers', 'manufacturers.id', '=', 'order_queue.manufacturer_id')
            ->selectRaw(
                'order_queue.manufacturer_id as manufacturer_id,
                                   manufacturers.name as manufacturer_name,
                                   COUNT(DISTINCT orders.order_number) as orders,
                                   orders.currency_code,
                                   (
                                      select SUM(total) / 100
                                      from uploads
                                      where uploads.order_id = orders.id
                                   ) as revenue,
                                   (
                                      select SUM(manufacturer_costs) / 100
                                      from order_queue
                                      where order_queue.order_id = orders.id
                                   ) as costs'
            )
            ->whereNotNull('orders.paid_at')
            ->whereNull('orders.deleted_at')
            ->orderByDesc('manufacturer_name')
            ->groupBy('manufacturer_id', 'orders.currency_code');
        $query = $this->removeTestEmailAddresses('orders.email', $query);

        $query = $this->applyFilters($query, $filters);

        $rows = $query->get();

        $data = [];

        foreach ($rows as $row) {
            $rev = $currencyService->convertCurrency($row->currency_code, config('app.currency'), (float) $row->revenue);
            $cost = $currencyService->convertCurrency($row->currency_code, config('app.currency'), (float) $row->costs);
            $prof = $rev - $cost;
            if (! array_key_exists($row->manufacturer_id, $data)) {
                $data[$row->manufacturer_id] = [
                    'name' => $row->manufacturer_name,
                    'orders' => $row->orders,
                    'revenue' => $rev,
                    'bruto_margin' => $prof,
                ];
            } else {
                $data[$row->manufacturer_id]['orders'] += $row->orders;
                $data[$row->manufacturer_id]['revenue'] += $rev;
                $data[$row->manufacturer_id]['bruto_margin'] += $prof;
            }
        }
        usort($data, function ($a, $b) {
            return $a['revenue'] < $b['revenue'];
        });
        for ($i = 0, $iMax = count($data); $iMax > $i; $i++) {
            $data[$i]['revenue'] = currencyFormatter((float) $data[$i]['revenue']);
            $data[$i]['bruto_margin'] = currencyFormatter((float) $data[$i]['bruto_margin']);
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
