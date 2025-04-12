<?php

namespace App\Nova\Settings\Shipping;

use App\Enums\Shippo\ShippoMassUnitsEnum;
use App\Services\Shippo\ShippoService;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;


class CustomsItemSettings extends SystemSettings
{
    public ?string $massUnit;
    public ?float $bag;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('Customs item Settings');
    }

    public static function icon(): string
    {
        return 'clipboard-list';
    }

    public static function name(): string
    {
        return 'customs_item_settings';
    }

    public static function fields(): array
    {
        return [
            Select::make(__('Mass unit'), 'massUnit')
                ->options(ShippoMassUnitsEnum::values())
                ->displayUsingLabels(),

            Number::make(__('Bag'), 'bag')
                ->help(__('Weight of bag used to calculate net weight')),
        ];
    }
}
