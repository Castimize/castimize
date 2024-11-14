<?php

namespace App\Traits\Nova\Metrics;

use App\Nova\Filters\OrderDateDaterangepickerFilter;
use App\Nova\Filters\RangesFilter;
use Carbon\CarbonPeriod;
use DigitalCreative\NovaDashboard\Filters;
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

    public function defaultValueRanges(): array
    {
        return [
            __('30 Days') => '30',
            __('60 Days') => '60',
            __('365 Days') => '365',
            __('Today') => 'TODAY',
            __('Month To Date') => 'MTD',
            __('Quarter To Date') => 'QTD',
            __('Year To Date') => 'YTD',
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

    public function addRangeValueToQuery(string $column, $range, $query)
    {
        return match ($range) {
            '30 Days' => $query->whereBetween($column, [now()->subDays(30), now()]),
            '60 Days' => $query->whereBetween($column, [now()->subDays(60), now()]),
            '365 Days' => $query->whereBetween($column, [now()->subDays(365), now()]),
            'Today' => $query->where($column, '>=', now()->format('Y-m-d 00:00:00')),
            'Month To Date' => $query->whereBetween($column, [now()->startOfMonth()->format('Y-m-d 00:00:00'), now()]),
            'Quarter To Date' => $query->whereBetween($column, [now()->startOfQuarter()->format('Y-m-d 00:00:00'), now()]),
            'Year To Date' => $query->whereBetween($column, [now()->startOfYear()->format('Y-m-d 00:00:00'), now()]),
        };
    }

    public function addOrderDateToQuery($dateFrom, $dateTo, $query)
    {
        return $query->whereBetween('orders.created_at', [$dateFrom, $dateTo]);
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

    public function getDateRanges(Filters $filters): array
    {
        $dateRanges = [];
        $orderDateFilter = $filters->getFilterValue(OrderDateDaterangepickerFilter::class);
        $dateFrom = now()->subDays(30)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');
        if ($orderDateFilter) {
            [$dateFrom, $dateTo] = explode(' to ', $orderDateFilter);
        }
        $period = CarbonPeriod::create($dateFrom, $dateTo);

        foreach ($period as $date) {
            $dateRanges[] = $date->format('Y-m-d');
        }
        return $dateRanges;
    }

    public function applyFilters($query, Filters $filters)
    {
        $orderDateFilter = $filters->getFilterValue(OrderDateDaterangepickerFilter::class);
        $rangeFilter = $filters->getFilterValue(RangesFilter::class);
        if ($rangeFilter) {
            $query = $this->addRangeToQuery('orders.created_at', $rangeFilter, $query);
        }
        if ($orderDateFilter) {
            [$dateFrom, $dateTo] = explode(' to ', $orderDateFilter);
            $query = $this->addOrderDateToQuery($dateFrom, $dateTo . ' 23:59:59', $query);
        }

        return $query;
    }
}
