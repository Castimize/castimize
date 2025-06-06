<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class RemoveTestEmailAddressesFilter
{
    public function __construct(
        private string $column,
    ) {
    }

    public function __invoke(Builder $query): void
    {
        $query->whereNotIn($this->column, [
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
