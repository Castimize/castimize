<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class RemoveTestCustomerIdsFilter
{
    public function __construct(
        private string $column,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query->whereNotIn($this->column, [
            8,
            9,
            10,
            11,
            12,
            15,
            19,
        ]);
    }
}
