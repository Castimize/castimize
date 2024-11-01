<?php

namespace App\Traits\Nova\Metrics;

use Laravel\Nova\Nova;

trait CustomMetricsQueries
{
    public function defaultRanges(): array
    {
        return [
            '30' => Nova::__('30 Days'),
            '60' => Nova::__('60 Days'),
            '365' => Nova::__('365 Days'),
            'TODAY' => Nova::__('Today'),
            'MTD' => Nova::__('Month To Date'),
            'QTD' => Nova::__('Quarter To Date'),
            'YTD' => Nova::__('Year To Date'),
        ];
    }

    public function addRangeToQuery(string $column, $range, $query)
    {
        return match ($range) {
            '30' => $query->whereBetween($column, [now()->subDays(30), now()]),
            '60' => $query->whereBetween($column, [now()->subDays(60), now()]),
            '365' => $query->whereBetween($column, [now()->subDays(365), now()]),
            'TODAY' => $query->where($column, '>=', now()->format('Y-m-d 00:00:00')),
            'MTD' => $query->whereBetween($column, [now()->startOfMonth()->format('Y-m-d 00:00:00'), now()]),
            'QTD' => $query->whereBetween($column, [now()->startOfQuarter()->format('Y-m-d 00:00:00'), now()]),
            'YTD' => $query->whereBetween($column, [now()->startOfYear()->format('Y-m-d 00:00:00'), now()]),
        };
    }

    public function removeTestEmailAddresses(string $column, $query)
    {
        return $query->whereNotIn($column, [
            'matthbon@hotmail.com',
            'oknoeff@gmail.com',
            'robinkoonen@gmail.com',
            'oscar@castimize.com',
            'robin@castimize.com',
            'koen@castimize.com',
            'info@castimize.com',
        ]);
    }
}
