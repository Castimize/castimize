<?php

namespace App\Nova\Filters;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rpj\Daterangepicker\DateHelper as Helper;
use Rpj\Daterangepicker\Daterangepicker;

class DueDateDaterangepickerFilter extends Daterangepicker
{
    private ?Carbon $minDate = null;

    private ?Carbon $maxDate = null;

    private ?array $ranges = null;

    private string $column = 'due_date';

    public function __construct(
        private string $default = Helper::ALL,
        private string $orderByColumn = 'id',
        private string $orderByDir = 'asc',
    ) {
        parent::__construct($this->column, $default, $this->orderByColumn, $this->orderByDir);
    }

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'daterangepicker';

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return __('Due date');
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(NovaRequest $request, $query, $value): Builder
    {
        [$start, $end] = Helper::getParsedDatesGroupedRanges($value);

        if ($start && $end) {
            return $query->whereBetween($this->column, [$start, $end])
                ->orderBy($this->orderByColumn, $this->orderByDir);
        }

        return $query;
    }

    /**
     * Get the filter's available options.
     */
    public function options(NovaRequest $request): ?array
    {
        if (! $this->ranges) {
            $this->setRanges(Helper::defaultRanges());
        }

        return $this->ranges;
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default(): ?string
    {
        [$start, $end] = Helper::getParsedDatesGroupedRanges($this->default);

        if ($start && $end) {
            return __(':startDate to :endDate', [
                'startDate' => $start->format('Y-m-d'),
                'endDate' => $end->format('Y-m-d'),
            ]);
        }

        return null;
    }

    public function setMinDate(Carbon $minDate): self
    {
        $this->minDate = $minDate;

        if ($this->maxDate && $this->minDate->gt($this->maxDate)) {
            throw new Exception(__('Date range picker: minDate must be lower or equals than maxDate.'));
        }

        return $this;
    }

    public function setMaxDate(Carbon $maxDate): self
    {
        $this->maxDate = $maxDate;

        if ($this->minDate && $this->maxDate->lt($this->minDate)) {
            throw new Exception(__('Date range picker: maxDate must be greater or equals than minDate.'));
        }

        return $this;
    }

    public function setRanges(array $ranges): self
    {
        $result = collect($ranges)->mapWithKeys(function (array $item, string $key) {
            return [
                $key => (collect($item)->map(function (Carbon $date) {
                    return $date->format('Y-m-d');
                })),
            ];
        })->toArray();

        $this->ranges = $result;

        return $this;
    }

    /**
     * Convert the filter to its JSON representation.
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'minDate' => $this?->minDate?->format('Y-m-d'),
            'maxDate' => $this?->maxDate?->format('Y-m-d'),
        ]);
    }
}
