<?php

namespace App\Nova\Settings\Shipping;

use App\Enums\Shippo\ShippoBuildingLocationTypesEnum;
use App\Enums\Shippo\ShippoBuildingTypesEnum;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Select;

class PickupSettings extends SystemSettings
{
    public ?string $buildingType;

    public ?string $buildingLocationType;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('Pickup Settings');
    }

    public static function icon(): string
    {
        return 'cursor-click';
    }

    public static function name(): string
    {
        return 'pickup_settings';
    }

    public static function fields(): array
    {
        return [
            Select::make(__('Building type'), 'buildingType')
                ->options(ShippoBuildingTypesEnum::values())
                ->displayUsingLabels(),

            Select::make(__('Building location type'), 'buildingLocationType')
                ->options(ShippoBuildingLocationTypesEnum::values())
                ->displayUsingLabels(),
        ];
    }
}
